<?php

namespace App\Http\Controllers;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Supplier;
use App\Models\User;
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
            'batchPerluPerhatian' => $this->batchPerluPerhatian($request->user()),
            'riwayatTerakhir' => Penjualan::query()
                ->forUser($request->user())
                ->with('detail.obat')
                ->latest('tanggal_penjualan')
                ->take(3)
                ->get(),
        ]);
    }

    // Halaman transaksi kini ditangani KasirTransaksiController,
    // dan riwayat penjualan oleh PenjualanController.

    public function monitoring(Request $request): View
    {
        $obat = Obat::query()->forUser($request->user());

        $ambang = config('apotek.peringatan_kadaluarsa');

        // batch yang perlu diperhatikan, dikelompokkan per tingkat peringatan
        $batch = BatchObat::query()
            ->forUser($request->user())
            ->with('obat')
            ->where('stok', '>', 0)
            ->whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', now()->addDays($ambang['siapkan_retur']))
            ->orderBy('tanggal_kadaluarsa')
            ->get()
            ->groupBy(fn (BatchObat $item) => $item->tingkatKadaluarsa());

        return view('kasir.monitoring.index', [
            'totalObat' => (clone $obat)->count(),
            'obatMenipis' => (clone $obat)->stokMenipis()->orderBy('stok')->get(),
            'batchKadaluarsa' => $batch->get('kadaluarsa', collect()),
            'batchSegeraHabis' => $batch->get('segera_habis', collect()),
            'batchSiapkanRetur' => $batch->get('siapkan_retur', collect()),
            'ambang' => $ambang,
        ]);
    }

    /**
     * Batch yang stoknya masih ada dan tanggalnya sudah dekat atau lewat.
     *
     * @return Collection<int, BatchObat>
     */
    private function batchPerluPerhatian(User $user)
    {
        return BatchObat::query()
            ->forUser($user)
            ->with('obat')
            ->where('stok', '>', 0)
            ->whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', now()->addDays(config('apotek.peringatan_kadaluarsa.siapkan_retur')))
            ->orderBy('tanggal_kadaluarsa')
            ->get();
    }
}
