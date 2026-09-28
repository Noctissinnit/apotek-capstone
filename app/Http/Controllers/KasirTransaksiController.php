<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\Obat;
use App\Models\Penjualan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class KasirTransaksiController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $cart = $this->currentCart();

        return view('kasir.transaksi.index', [
            'obat' => Obat::query()
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('nama_obat', 'like', "%{$search}%")
                    ->orWhere('kode_obat', 'like', "%{$search}%")))
                ->orderBy('nama_obat')
                ->get(),
            'search' => $search,
            'jumlahKeranjang' => array_sum($cart),
            'jumlahPerObat' => $cart,
        ]);
    }

    public function cart(): View
    {
        $items = $this->cartItems();

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

        $obat = Obat::findOrFail($data['obat_id']);
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

        $penjualan = DB::transaction(function () use ($cart, $request): Penjualan {
            $obatList = Obat::query()
                ->whereIn('id', array_keys($cart))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalCents = 0;
            foreach ($cart as $obatId => $jumlah) {
                $obat = $obatList->get($obatId);
                if (! $obat || $obat->stok < $jumlah) {
                    throw ValidationException::withMessages([
                        'keranjang' => 'Stok salah satu obat berubah atau tidak mencukupi. Periksa kembali keranjang.',
                    ]);
                }

                $totalCents += $this->toCents((string) $obat->harga_jual) * $jumlah;
            }

            $penjualan = Penjualan::create([
                'no_faktur' => 'TRX-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(5)),
                'user_id' => $request->user()->id,
                'tanggal_penjualan' => now(),
                'total' => $this->fromCents($totalCents),
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

        return redirect()->route('kasir.keranjang')
            ->with('success', "Transaksi {$penjualan->no_faktur} berhasil. Total Rp ".number_format((float) $penjualan->total, 0, ',', '.'));
    }

    /** @return array<int, int> */
    private function currentCart(): array
    {
        return collect(session('kasir.cart', []))
            ->mapWithKeys(fn ($quantity, $id) => [(int) $id => (int) $quantity])
            ->all();
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function cartItems()
    {
        $cart = $this->currentCart();
        $obatList = Obat::whereIn('id', array_keys($cart))->get()->keyBy('id');

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