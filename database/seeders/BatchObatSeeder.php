<?php

namespace Database\Seeders;

use App\Models\BatchObat;
use App\Models\Obat;
use Illuminate\Database\Seeder;

/**
 * Memecah stok tiap obat menjadi batch, supaya data contoh sudah menyerupai
 * kondisi nyata: satu obat bisa punya beberapa tanggal kadaluarsa.
 *
 * Total stok seluruh batch selalu sama dengan stok di master obat.
 */
class BatchObatSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Obat::all() as $obat) {
            $obat->batch()->delete();

            if ($obat->stok < 1) {
                continue;
            }

            // Sebagian obat dibuat punya dua batch dengan tanggal berbeda,
            // agar pengambilan dari batch terdekat kadaluarsa bisa diuji.
            $punyaDuaBatch = $obat->id % 3 === 0;
            $stokBatchLama = $punyaDuaBatch ? (int) floor($obat->stok * 0.4) : 0;

            if ($stokBatchLama > 0) {
                BatchObat::create([
                    'obat_id' => $obat->id,
                    'no_batch' => $this->nomorBatch($obat, 'A'),
                    // batch lama: 45 hari lagi kadaluarsa
                    'tanggal_kadaluarsa' => now()->addDays(45)->toDateString(),
                    'stok' => $stokBatchLama,
                    'modal_per_satuan' => $obat->harga_beli,
                ]);
            }

            BatchObat::create([
                'obat_id' => $obat->id,
                'no_batch' => $this->nomorBatch($obat, 'B'),
                'tanggal_kadaluarsa' => $obat->tanggal_kadaluarsa?->toDateString(),
                'stok' => $obat->stok - $stokBatchLama,
                'modal_per_satuan' => $obat->harga_beli,
            ]);
        }
    }

    private function nomorBatch(Obat $obat, string $urutan): string
    {
        return str($obat->kode_obat)->replace('OBT', 'BT')->append('-')->append($urutan)->toString();
    }
}
