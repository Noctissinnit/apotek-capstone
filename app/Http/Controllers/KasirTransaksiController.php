<?php

namespace App\Http\Controllers;

use App\Actions\ProsesCheckout;
use App\Models\Obat;
use App\Support\Keranjang;
use App\Support\Rupiah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Layar kasir. Isi keranjang diurus App\Support\Keranjang dan pembayarannya
 * oleh App\Actions\ProsesCheckout, keduanya dipakai bersama API.
 */
class KasirTransaksiController extends Controller
{
    public function __construct(private Keranjang $keranjang) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $items = $this->keranjang->rincian($request->user());

        return view('kasir.transaksi.index', [
            'obat' => Obat::query()
                ->forUser($request->user())
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('nama_obat', 'like', "%{$search}%")
                    ->orWhere('kode_obat', 'like', "%{$search}%")))
                ->orderBy('nama_obat')
                ->get(),
            'search' => $search,
            'jumlahKeranjang' => array_sum($this->keranjang->isi()),
            'jumlahPerObat' => $this->keranjang->isi(),
            'items' => $items,
            'subtotal' => $items->sum('subtotal_cents'),
            'qris' => $this->keranjang->qris($request->user(), $items),
        ]);
    }

    public function cart(Request $request): View
    {
        $items = $this->keranjang->rincian($request->user());

        return view('kasir.keranjang.index', [
            'items' => $items,
            'subtotal' => $items->sum('subtotal_cents'),
            'jumlahItem' => $items->sum('jumlah'),
            'qris' => $this->keranjang->qris($request->user(), $items),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'obat_id' => ['required', 'integer', 'exists:obat,id'],
            'jumlah' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        try {
            $obat = $this->keranjang->tambah($request->user(), $data['obat_id'], $data['jumlah'] ?? 1);
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', "{$obat->nama_obat} ditambahkan ke keranjang.");
    }

    public function remove(Obat $obat): RedirectResponse
    {
        $this->keranjang->hapus($obat->id);

        return back()->with('success', 'Obat dihapus dari keranjang.');
    }

    public function checkout(Request $request, ProsesCheckout $proses): RedirectResponse
    {
        $keranjang = $this->keranjang->isi();

        if ($keranjang === []) {
            return redirect()->route('kasir.keranjang')->with('error', 'Keranjang masih kosong.');
        }

        $data = $request->validate(ProsesCheckout::aturan(), ProsesCheckout::pesan());

        $penjualan = $proses($request->user(), $keranjang, $data);

        $this->keranjang->kosongkan();

        $rupiah = fn ($nilai) => $nilai === null ? null : Rupiah::tampil(Rupiah::keSen((string) $nilai));

        // Dipakai layout untuk menampilkan notifikasi besar setelah pembayaran
        return redirect()->route('kasir.keranjang')->with('transaksi_sukses', [
            'no_faktur' => $penjualan->no_faktur,
            'total' => $rupiah($penjualan->total),
            'metode_pembayaran' => $penjualan->metode_pembayaran,
            'uang_diterima' => $rupiah($penjualan->uang_diterima),
            'kembalian' => $rupiah($penjualan->kembalian),
            'jumlah_item' => array_sum($keranjang),
            'url_struk' => route('kasir.riwayat.pdf', $penjualan),
            'url_transaksi_baru' => route('kasir.transaksi'),
        ]);
    }
}
