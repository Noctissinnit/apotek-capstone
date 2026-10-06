<?php

namespace App\Http\Controllers;

use App\Models\BatchObat;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Penelusuran batch obat: dari faktur mana barang masuk, dan ke mana saja
 * keluarnya (terjual, diretur, atau dimusnahkan).
 */
class BatchObatController extends Controller
{
    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari', ''));
        $status = (string) $request->query('status', '');

        $batch = BatchObat::query()
            ->forUser($request->user())
            ->with('obat')
            ->saring($cari, $status)
            ->paginate(10)
            ->withQueryString();

        return view('admin.batch.index', compact('batch', 'cari', 'status'));
    }

    public function show(Request $request, BatchObat $batch): View
    {
        abort_unless($batch->obat?->apotek === $request->user()->apotek, 404);

        $ringkasan = $batch->ringkasanRiwayat();

        return view('admin.batch.show', [
            'batch' => $batch->load('obat'),
            'riwayat' => $ringkasan['riwayat'],
            'stokAwal' => $ringkasan['stok_awal'],
            'totalMasuk' => $ringkasan['total_masuk'],
            'totalKeluar' => $ringkasan['total_keluar'],
        ]);
    }
}
