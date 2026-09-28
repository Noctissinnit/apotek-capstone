<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Carbon\CarbonImmutable;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function index(): View
    {
        $hariIni = CarbonImmutable::today();

        return view('kasir.riwayat.index', [
            'riwayat' => Penjualan::with(['user', 'detail'])
                ->orderByDesc('tanggal_penjualan')
                ->get(),
            'totalHariIni' => Penjualan::whereDate('tanggal_penjualan', $hariIni->toDateString())->sum('total'),
            'jumlahHariIni' => Penjualan::whereDate('tanggal_penjualan', $hariIni->toDateString())->count(),
            'tanggalHariIni' => $hariIni,
            'tanggalKemarin' => $hariIni->subDay(),
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $hariIni = CarbonImmutable::today();
        $validated = $request->validate([
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
                Rule::in([$hariIni->toDateString(), $hariIni->subDay()->toDateString()]),
            ],
        ]);
        $tanggal = CarbonImmutable::parse($validated['tanggal']);
        $penjualan = Penjualan::with(['user', 'detail'])
            ->whereDate('tanggal_penjualan', $tanggal->toDateString())
            ->orderBy('tanggal_penjualan')
            ->get();

        $dompdf = new Dompdf;
        $dompdf->loadHtml(view('kasir.riwayat.pdf', [
            'penjualan' => $penjualan,
            'tanggal' => $tanggal,
            'totalPenjualan' => $penjualan->sum('total'),
        ])->render(), 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-penjualan-'.$tanggal->format('Y-m-d').'.pdf"',
        ]);
    }
}
