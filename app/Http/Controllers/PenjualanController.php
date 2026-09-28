<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Carbon\CarbonImmutable;
use Dompdf\Dompdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function index(): View
    {
        $hariIni = CarbonImmutable::today();

        return view('kasir.riwayat.index', [
            'riwayat' => Penjualan::with(['user', 'detail.obat'])
                ->orderByDesc('tanggal_penjualan')
                ->get(),
            'totalHariIni' => Penjualan::whereDate('tanggal_penjualan', $hariIni->toDateString())->sum('total'),
            'jumlahHariIni' => Penjualan::whereDate('tanggal_penjualan', $hariIni->toDateString())->count(),
        ]);
    }

    public function exportPdf(Penjualan $penjualan): Response
    {
        $penjualan->load(['user', 'detail.obat']);

        $dompdf = new Dompdf;
        $dompdf->loadHtml(view('kasir.riwayat.pdf', [
            'transaksi' => $penjualan,
        ])->render(), 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-'.$penjualan->id.'-'.$penjualan->tanggal_penjualan->format('Y-m-d').'.pdf"',
        ]);
    }
}
