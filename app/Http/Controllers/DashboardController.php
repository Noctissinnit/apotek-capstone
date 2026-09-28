<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Supplier;
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
            'obatHampirKadaluarsa' => (clone $obat)->whereNotNull('tanggal_kadaluarsa')
                ->whereDate('tanggal_kadaluarsa', '<=', now()->addMonths(3))
                ->orderBy('tanggal_kadaluarsa')
                ->get(),
        ]);
    }
}
