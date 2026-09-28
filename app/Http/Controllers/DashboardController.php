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
            'riwayatTerakhir' => array_slice($this->riwayatDummy(), -3),
        ]);
    }

    public function riwayat(): View
    {
        return view('kasir.riwayat.index', [
            'riwayat' => $this->riwayatDummy(),
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
     * @return \Illuminate\Database\Eloquent\Collection<int, Obat>
     */
    private function obatHampirKadaluarsa()
    {
        return Obat::whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', now()->addMonths(3))
            ->orderBy('tanggal_kadaluarsa')
            ->get();
    }

    /**
     * Data contoh riwayat transaksi. Diganti data asli pada W8 (History & Reporting).
     *
     * @return array<int, array<string, mixed>>
     */
    private function riwayatDummy(): array
    {
        return [
            ['no_faktur' => 'TRX-20260925-001', 'waktu' => '08:45', 'kasir' => 'Kasir Apotek', 'item' => 3, 'total' => 28000],
            ['no_faktur' => 'TRX-20260925-002', 'waktu' => '09:15', 'kasir' => 'Kasir Apotek', 'item' => 5, 'total' => 72500],
            ['no_faktur' => 'TRX-20260925-003', 'waktu' => '10:20', 'kasir' => 'Kasir Apotek', 'item' => 2, 'total' => 43000],
        ];
    }
}
