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
            'obatHampirKadaluarsa' => Obat::whereNotNull('tanggal_kadaluarsa')
                ->whereDate('tanggal_kadaluarsa', '<=', now()->addMonths(3))
                ->orderBy('tanggal_kadaluarsa')
                ->get(),
            'daftarObat' => Obat::orderBy('stok')->limit(8)->get(),
        ]);
    }

    public function transaksi(): View
    {
        return view('kasir.transaksi.index', [
            'obat' => Obat::orderBy('nama_obat')->get(),
        ]);
    }

    public function keranjang(): View
    {
        return view('kasir.keranjang.index', [
            'items' => [
                ['nama' => 'Paracetamol 500 mg', 'jumlah' => 1, 'harga' => 5000],
                ['nama' => 'Vitamin C 1000 mg', 'jumlah' => 2, 'harga' => 30000],
            ],
        ]);
    }

    public function riwayat(): View
    {
        return view('kasir.riwayat.index', [
            'riwayat' => [
                ['waktu' => '08:45', 'kasir' => 'Kasir Apotek', 'total' => 28000],
                ['waktu' => '09:15', 'kasir' => 'Kasir Apotek', 'total' => 72500],
                ['waktu' => '10:20', 'kasir' => 'Kasir Apotek', 'total' => 43000],
            ],
        ]);
    }

    public function monitoring(): View
    {
        return view('kasir.monitoring.index', [
            'obatMenipis' => Obat::stokMenipis()->orderBy('stok')->get(),
        ]);
    }
}
