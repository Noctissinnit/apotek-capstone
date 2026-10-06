<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PenjualanResource;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Riwayat transaksi penjualan. Struk dan laporan PDF-nya dilayani langsung
 * oleh controller web, lewat route api.v1.kasir.riwayat.pdf dan
 * api.v1.kasir.laporan.pdf.
 */
class RiwayatPenjualanController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $saringan = $request->validate([
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $user = $request->user();

        $riwayat = Penjualan::query()
            ->forUser($user)
            ->with(['user', 'detail.obat'])
            ->when($saringan['tanggal_mulai'] ?? null, fn ($query, $tanggal) => $query->whereDate('tanggal_penjualan', '>=', $tanggal))
            ->when($saringan['tanggal_selesai'] ?? null, fn ($query, $tanggal) => $query->whereDate('tanggal_penjualan', '<=', $tanggal))
            ->orderByDesc('tanggal_penjualan')
            ->orderByDesc('id')
            ->paginate($saringan['per_page'] ?? 10)
            ->withQueryString();

        return PenjualanResource::collection($riwayat)->additional([
            'ringkasan' => ['hari_ini' => Penjualan::ringkasanHariIni($user)],
        ]);
    }

    public function show(Request $request, Penjualan $penjualan): PenjualanResource
    {
        abort_unless($penjualan->apotek === $request->user()->apotek, 404);

        return new PenjualanResource($penjualan->load(['user', 'detail.obat']));
    }
}
