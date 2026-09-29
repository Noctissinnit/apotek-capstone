<?php

namespace Database\Seeders;

use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanSeeder extends Seeder
{
    public function run(): void
    {
        $kasir = User::where('email', 'kasir@apotek.test')->firstOrFail();
        $transaksi = [
            [
                'tanggal' => CarbonImmutable::today()->setTime(8, 45),
                'nomor' => 1,
                'items' => [['OBT001', 2], ['OBT005', 1]],
            ],
            [
                'tanggal' => CarbonImmutable::today()->setTime(10, 20),
                'nomor' => 2,
                'items' => [['OBT003', 1], ['OBT008', 1]],
            ],
            [
                'tanggal' => CarbonImmutable::yesterday()->setTime(9, 15),
                'nomor' => 1,
                'items' => [['OBT002', 2], ['OBT004', 1]],
            ],
        ];

        DB::transaction(function () use ($transaksi, $kasir): void {
            foreach ($transaksi as $data) {
                $tanggal = $data['tanggal'];
                $penjualan = Penjualan::updateOrCreate(
                    ['no_faktur' => 'TRX-'.$tanggal->format('Ymd').'-'.str_pad((string) $data['nomor'], 3, '0', STR_PAD_LEFT)],
                    [
                        'user_id' => $kasir->id,
                        'tanggal_penjualan' => $tanggal,
                        'total' => 0,
                    ]
                );
                $penjualan->detail()->delete();

                $total = 0;
                foreach ($data['items'] as [$kodeObat, $jumlah]) {
                    $obat = Obat::where('kode_obat', $kodeObat)->firstOrFail();
                    $subtotal = $obat->harga_jual * $jumlah;
                    $total += $subtotal;

                    $penjualan->detail()->create([
                        'obat_id' => $obat->id,
                        'jumlah' => $jumlah,
                        'harga_jual' => $obat->harga_jual,
                        'subtotal' => $subtotal,
                    ]);
                }

                $penjualan->update(['total' => $total]);
            }
        });
    }
}
