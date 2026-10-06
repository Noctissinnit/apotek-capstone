<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BatchObatResource;
use App\Models\BatchObat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class BatchObatController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $saringan = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(BatchObat::SARINGAN_STATUS)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $batch = BatchObat::query()
            ->forUser($request->user())
            ->with('obat')
            ->saring($saringan['cari'] ?? '', $saringan['status'] ?? '')
            ->paginate($saringan['per_page'] ?? 10)
            ->withQueryString();

        return BatchObatResource::collection($batch);
    }

    /**
     * Satu batch beserta riwayat keluar masuknya, dari yang paling lama.
     */
    public function show(Request $request, BatchObat $batch): BatchObatResource
    {
        abort_unless($batch->obat?->apotek === $request->user()->apotek, 404);

        $ringkasan = $batch->ringkasanRiwayat();

        return (new BatchObatResource($batch->load('obat')))->additional([
            'riwayat' => $ringkasan['riwayat']->map(fn (array $baris) => [
                ...$baris,
                'tanggal' => $baris['tanggal']?->toIso8601String(),
            ]),
            'meta' => [
                'stok_awal' => $ringkasan['stok_awal'],
                'total_masuk' => $ringkasan['total_masuk'],
                'total_keluar' => $ringkasan['total_keluar'],
            ],
        ]);
    }
}
