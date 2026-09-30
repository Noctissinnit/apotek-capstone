<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Carbon\CarbonImmutable;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function index(Request $request): View
    {
        $hariIni = CarbonImmutable::today();
        $milikApotek = fn () => Penjualan::query()->forUser($request->user());

        return view('kasir.riwayat.index', [
            'riwayat' => $milikApotek()
                ->with(['user', 'detail.obat'])
                ->orderByDesc('tanggal_penjualan')
                ->paginate(5)
                ->withQueryString(),
            'totalHariIni' => $milikApotek()->whereDate('tanggal_penjualan', $hariIni->toDateString())->sum('total'),
            'jumlahHariIni' => $milikApotek()->whereDate('tanggal_penjualan', $hariIni->toDateString())->count(),
            'tanggalAwalLaporan' => $hariIni->toDateString(),
            'tanggalAkhirLaporan' => $hariIni->toDateString(),
        ]);
    }

    public function exportRentangPdf(Request $request): Response
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
        ]);
        $tanggalMulai = CarbonImmutable::createFromFormat('Y-m-d', $validated['tanggal_mulai']);
        $tanggalSelesai = CarbonImmutable::createFromFormat('Y-m-d', $validated['tanggal_selesai']);
        $penjualan = Penjualan::query()
            ->forUser($request->user())
            ->with(['user', 'detail.obat'])
            ->whereBetween('tanggal_penjualan', [$tanggalMulai->startOfDay(), $tanggalSelesai->endOfDay()])
            ->orderBy('tanggal_penjualan')
            ->get();

        $dompdf = new Dompdf;
        $dompdf->loadHtml(view('kasir.riwayat.pdf-rentang', [
            'penjualan' => $penjualan,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'totalKeseluruhan' => $penjualan->sum('total'),
        ])->render(), 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-penjualan-'.$tanggalMulai->format('Y-m-d').'-sampai-'.$tanggalSelesai->format('Y-m-d').'.pdf"',
        ]);
    }

    public function exportPdf(Request $request, Penjualan $penjualan): Response
    {
        // Struk transaksi apotek lain tidak boleh dibuka lewat URL
        abort_unless($penjualan->apotek === $request->user()->apotek, 404);

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
