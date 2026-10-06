<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ProsesCheckout;
use App\Http\Controllers\Controller;
use App\Models\BatchObat;
use App\Models\Obat;
use Illuminate\Http\JsonResponse;

/**
 * Daftar pilihan tetap untuk isian dan saringan di frontend, supaya
 * Next.js tidak perlu menyalin daftar yang sama ke kodenya sendiri.
 */
class ReferensiController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => [
            'satuan' => Obat::SATUAN,
            'urutan_obat' => array_keys(Obat::KOLOM_URUT),
            'status_batch' => BatchObat::SARINGAN_STATUS,
            'metode_pembayaran' => ProsesCheckout::METODE_PEMBAYARAN,
            'ambang_kadaluarsa_hari' => config('apotek.peringatan_kadaluarsa'),
        ]]);
    }
}
