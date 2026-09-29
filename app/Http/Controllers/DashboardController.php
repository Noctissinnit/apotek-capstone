<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
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

    public function admin(Request $request): View
    {
        $obat = Obat::query()->forUser($request->user());

        return view('admin.dashboard', [
            'totalObat' => (clone $obat)->count(),
            'stokMenipis' => (clone $obat)->stokMenipis()->count(),
            'totalSupplier' => Supplier::count(),
            'totalPembelianBulanIni' => Pembelian::whereBetween('tanggal_pembelian', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
            'pembelianTerbaru' => Pembelian::with('supplier')->latest('tanggal_pembelian')->take(5)->get(),
        ]);
    }

    public function kasir(Request $request): View
    {
        $obat = Obat::query()->forUser($request->user());

        return view('kasir.dashboard', [
            'totalObat' => (clone $obat)->count(),
            'obatMenipis' => (clone $obat)->stokMenipis()->orderBy('stok')->get(),
            'obatHampirKadaluarsa' => $this->obatHampirKadaluarsa(clone $obat),
            'riwayatTerakhir' => Penjualan::with('detail.obat')->latest('tanggal_penjualan')->take(3)->get(),
        ]);
    }

    // Halaman transaksi kini ditangani KasirTransaksiController,
    // dan riwayat penjualan oleh PenjualanController.

    public function monitoring(Request $request): View
    {
        $obat = Obat::query()->forUser($request->user());

        return view('kasir.monitoring.index', [
            'totalObat' => (clone $obat)->count(),
            'obatMenipis' => (clone $obat)->stokMenipis()->orderBy('stok')->get(),
            'obatHampirKadaluarsa' => $this->obatHampirKadaluarsa(clone $obat),
        ]);
    }

    /**
     * @param  Builder<Obat>  $obat
     * @return Collection<int, Obat>
     */
    private function obatHampirKadaluarsa(Builder $obat)
    {
        return $obat->whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', now()->addMonths(3))
            ->orderBy('tanggal_kadaluarsa')
            ->get();
    }
}
