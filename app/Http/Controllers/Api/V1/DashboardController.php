<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BatchObatResource;
use App\Http\Resources\ObatResource;
use App\Http\Resources\PembelianResource;
use App\Http\Resources\PenjualanResource;
use App\Queries\RingkasanDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Isi dashboard dan monitoring stok. Angkanya sama persis dengan halaman web
 * karena keduanya memakai RingkasanDashboard.
 */
class DashboardController extends Controller
{
    public function __construct(private RingkasanDashboard $ringkasan) {}

    public function admin(Request $request): JsonResponse
    {
        $data = $this->ringkasan->admin($request->user());

        return response()->json(['data' => [
            'total_obat' => $data['totalObat'],
            'stok_menipis' => $data['stokMenipis'],
            'total_supplier' => $data['totalSupplier'],
            'total_pembelian_bulan_ini' => $data['totalPembelianBulanIni'],
            'pembelian_terbaru' => PembelianResource::collection($data['pembelianTerbaru']),
        ]]);
    }

    public function kasir(Request $request): JsonResponse
    {
        $data = $this->ringkasan->kasir($request->user());

        return response()->json(['data' => [
            'total_obat' => $data['totalObat'],
            'obat_menipis' => ObatResource::collection($data['obatMenipis']),
            'batch_perlu_perhatian' => BatchObatResource::collection($data['batchPerluPerhatian']),
            'riwayat_terakhir' => PenjualanResource::collection($data['riwayatTerakhir']),
        ]]);
    }

    public function monitoring(Request $request): JsonResponse
    {
        $data = $this->ringkasan->monitoring($request->user());

        return response()->json(['data' => [
            'total_obat' => $data['totalObat'],
            'obat_menipis' => ObatResource::collection($data['obatMenipis']),
            'batch' => [
                'kadaluarsa' => BatchObatResource::collection($data['batchKadaluarsa']),
                'segera_habis' => BatchObatResource::collection($data['batchSegeraHabis']),
                'siapkan_retur' => BatchObatResource::collection($data['batchSiapkanRetur']),
            ],
            'ambang_hari' => $data['ambang'],
        ]]);
    }
}
