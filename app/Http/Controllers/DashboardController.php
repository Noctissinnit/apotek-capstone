<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Arahkan ke dashboard sesuai role user yang login.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->dashboardRoute());
    }

    public function admin(): View
    {
        return view('admin.dashboard', [
            'totalObat' => Obat::count(),
            'stokMenipis' => Obat::stokMenipis()->count(),
            'totalSupplier' => Supplier::count(),
            'totalPembelianBulanIni' => Pembelian::whereBetween('tanggal_pembelian', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
            'pembelianTerbaru' => Pembelian::with('supplier')->latest('tanggal_pembelian')->take(5)->get(),
        ]);
    }

    public function kasir(): View
    {
        return view('kasir.dashboard', [
            'totalObat' => Obat::count(),
            'obatMenipis' => Obat::stokMenipis()->orderBy('stok')->get(),
            'obatHampirKadaluarsa' => $this->obatHampirKadaluarsa(),
            'riwayatTerakhir' => Penjualan::with('detail.obat')->latest('tanggal_penjualan')->take(3)->get(),
        ]);
    }

    public function transaksi(): View
    {
        return view('kasir.transaksi.index', [
            'obat' => Obat::orderBy('nama_obat')->get(),
            'keranjang' => $this->keranjangDummy(),
        ]);
    }

    public function monitoring(): View
    {
        return view('kasir.monitoring.index', [
            'totalObat' => Obat::count(),
            'obatMenipis' => Obat::stokMenipis()->orderBy('stok')->get(),
            'obatHampirKadaluarsa' => $this->obatHampirKadaluarsa(),
        ]);
    }

    /**
     * @return Collection<int, Obat>
     */
    private function obatHampirKadaluarsa()
    {
        return Obat::whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', now()->addMonths(3))
            ->orderBy('tanggal_kadaluarsa')
            ->get();
    }

    /**
     * Data contoh keranjang. Diganti data asli pada W6 (Cashier & Sales Transaction).
     *
     * @return array<int, array<string, mixed>>
     */
    private function keranjangDummy(): array
    {
        return [
            ['nama' => 'Paracetamol 500 mg', 'satuan' => 'Strip', 'jumlah' => 1, 'harga' => 5000],
            ['nama' => 'Vitamin C 1000 mg', 'satuan' => 'Tube', 'jumlah' => 2, 'harga' => 30000],
        ];
    }
}
