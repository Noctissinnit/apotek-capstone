<?php

namespace App\Http\Controllers;

use App\Queries\RingkasanDashboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private RingkasanDashboard $ringkasan) {}

    /**
     * Arahkan ke dashboard sesuai role user yang login.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->dashboardRoute());
    }

    public function admin(Request $request): View
    {
        return view('admin.dashboard', $this->ringkasan->admin($request->user()));
    }

    public function kasir(Request $request): View
    {
        return view('kasir.dashboard', $this->ringkasan->kasir($request->user()));
    }

    // Halaman transaksi kini ditangani KasirTransaksiController,
    // dan riwayat penjualan oleh PenjualanController.

    public function monitoring(Request $request): View
    {
        return view('kasir.monitoring.index', $this->ringkasan->monitoring($request->user()));
    }
}
