<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class KasirTransaksiController extends Controller
{
    /**
     * Cara pembayaran yang dilayani di meja kasir. Semuanya dibayar langsung di tempat:
     * QRIS lewat stiker QR milik apotek, kartu debit lewat mesin EDC bank.
     * Sistem hanya mencatat, tidak memproses pembayaran.
     */
    public const METODE_PEMBAYARAN = ['Tunai', 'QRIS', 'Kartu Debit'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $cart = $this->currentCart();
        $items = $this->cartItems($request->user());

        return view('kasir.transaksi.index', [
            'obat' => Obat::query()
                ->forUser($request->user())
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('nama_obat', 'like', "%{$search}%")
                    ->orWhere('kode_obat', 'like', "%{$search}%")))
                ->orderBy('nama_obat')
                ->get(),
            'search' => $search,
            'jumlahKeranjang' => array_sum($cart),
            'jumlahPerObat' => $cart,
            'items' => $items,
            'subtotal' => $items->sum(fn (array $item) => $item['subtotal_cents']),
        ]);
    }

    public function cart(Request $request): View
    {
        $items = $this->cartItems($request->user());

        return view('kasir.keranjang.index', [
            'items' => $items,
            'subtotal' => $items->sum(fn (array $item) => $item['subtotal_cents']),
            'jumlahItem' => $items->sum('jumlah'),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'obat_id' => ['required', 'integer', 'exists:obat,id'],
            'jumlah' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        // Obat milik apotek lain tidak boleh masuk keranjang
        $obat = Obat::query()->forUser($request->user())->findOrFail($data['obat_id']);
        $cart = $this->currentCart();
        $quantity = ($cart[$obat->id] ?? 0) + ($data['jumlah'] ?? 1);

        if ($obat->stok < $quantity) {
            return back()->with('error', "Stok {$obat->nama_obat} tidak mencukupi. Stok tersedia: {$obat->stok}.");
        }

        $cart[$obat->id] = $quantity;
        $request->session()->put('kasir.cart', $cart);

        return back()->with('success', "{$obat->nama_obat} ditambahkan ke keranjang.");
    }

    public function remove(Request $request, Obat $obat): RedirectResponse
    {
        $cart = $this->currentCart();
        unset($cart[$obat->id]);
        $request->session()->put('kasir.cart', $cart);

        return back()->with('success', 'Obat dihapus dari keranjang.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $cart = $this->currentCart();

        if ($cart === []) {
            return redirect()->route('kasir.keranjang')->with('error', 'Keranjang masih kosong.');
        }

        $data = $request->validate([
            'metode_pembayaran' => ['required', Rule::in(self::METODE_PEMBAYARAN)],
        ]);

        $penjualan = DB::transaction(function () use ($cart, $request, $data): Penjualan {
            // Penyaringan apotek diulang di sini, bukan hanya saat menampilkan daftar,
            // supaya keranjang yang sudah telanjur berisi obat apotek lain tetap ditolak
            $obatList = Obat::query()
                ->forUser($request->user())
                ->whereIn('id', array_keys($cart))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalCents = 0;
            foreach ($cart as $obatId => $jumlah) {
                $obat = $obatList->get($obatId);
                if (! $obat) {
                    throw ValidationException::withMessages([
                        'keranjang' => 'Ada obat di keranjang yang bukan milik apotek Anda. Kosongkan keranjang lalu ulangi.',
                    ]);
                }

                if ($obat->stok < $jumlah) {
                    throw ValidationException::withMessages([
                        'keranjang' => 'Stok salah satu obat berubah atau tidak mencukupi. Periksa kembali keranjang.',
                    ]);
                }

                $totalCents += $this->toCents((string) $obat->harga_jual) * $jumlah;
            }

            $penjualan = Penjualan::create([
                'no_faktur' => 'TRX-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(5)),
                'user_id' => $request->user()->id,
                'apotek' => $request->user()->apotek,
                'tanggal_penjualan' => now(),
                'total' => $this->fromCents($totalCents),
                'metode_pembayaran' => $data['metode_pembayaran'],
            ]);

            foreach ($cart as $obatId => $jumlah) {
                $obat = $obatList->get($obatId);
                $unitCents = $this->toCents((string) $obat->harga_jual);

                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'obat_id' => $obat->id,
                    'jumlah' => $jumlah,
                    'harga_jual' => $obat->harga_jual,
                    'subtotal' => $this->fromCents($unitCents * $jumlah),
                ]);

                $obat->decrement('stok', $jumlah);
            }

            return $penjualan;
        });

        $request->session()->forget('kasir.cart');

        // Dipakai layout untuk menampilkan notifikasi besar setelah pembayaran
        return redirect()->route('kasir.keranjang')->with('transaksi_sukses', [
            'no_faktur' => $penjualan->no_faktur,
            'total' => 'Rp '.number_format((float) $penjualan->total, 0, ',', '.'),
            'metode_pembayaran' => $penjualan->metode_pembayaran,
            'jumlah_item' => array_sum($cart),
            'url_struk' => route('kasir.riwayat.pdf', $penjualan),
            'url_transaksi_baru' => route('kasir.transaksi'),
        ]);
    }

    /** @return array<int, int> */
    private function currentCart(): array
    {
        return collect(session('kasir.cart', []))
            ->mapWithKeys(fn ($quantity, $id) => [(int) $id => (int) $quantity])
            ->all();
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function cartItems(User $user)
    {
        $cart = $this->currentCart();
        $obatList = Obat::query()
            ->forUser($user)
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        return collect($cart)->map(function (int $quantity, int $id) use ($obatList): array {
            $obat = $obatList->get($id);
            if (! $obat) {
                return null;
            }

            $unitCents = $this->toCents((string) $obat->harga_jual);

            return [
                'obat' => $obat,
                'jumlah' => $quantity,
                'harga_cents' => $unitCents,
                'subtotal_cents' => $unitCents * $quantity,
            ];
        })->filter()->values();
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}