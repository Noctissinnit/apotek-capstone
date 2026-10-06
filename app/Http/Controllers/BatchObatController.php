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
            ->when($cari !== '', fn ($query) => $query->where(function ($query) use ($cari) {
                $query->where('no_batch', 'like', "%{$cari}%")
                    ->orWhereHas('obat', fn ($obat) => $obat
                        ->where('nama_obat', 'like', "%{$cari}%")
                        ->orWhere('kode_obat', 'like', "%{$cari}%"));
            }))
            ->when($status === 'kadaluarsa', fn ($query) => $query->kadaluarsa())
            ->when($status === 'ditahan', fn ($query) => $query->where('status', BatchObat::STATUS_DITAHAN))
            ->when($status === 'habis', fn ($query) => $query->where('stok', 0))
            ->when($status === 'siap', fn ($query) => $query->siapDijual())
            ->orderByRaw('tanggal_kadaluarsa is null')
            ->orderBy('tanggal_kadaluarsa')
            ->paginate(10)
            ->withQueryString();

        return view('admin.batch.index', compact('batch', 'cari', 'status'));
    }

    public function show(Request $request, BatchObat $batch): View
    {
        abort_unless($batch->obat?->apotek === $request->user()->apotek, 404);

        $riwayat = $batch->riwayat();

        // Batch hasil migrasi data lama bisa tidak punya catatan barang masuk.
        // Selisihnya ditampilkan sebagai stok awal supaya angkanya tetap jujur.
        $totalMasuk = $riwayat->where('jumlah', '>', 0)->sum('jumlah');
        $totalKeluar = abs($riwayat->where('jumlah', '<', 0)->sum('jumlah'));
        $stokAwal = $batch->stok + $totalKeluar - $totalMasuk;

        return view('admin.batch.show', [
            'batch' => $batch->load('obat'),
            'riwayat' => $riwayat,
            'stokAwal' => $stokAwal,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
        ]);
    }
}
