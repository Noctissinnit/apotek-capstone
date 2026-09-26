<?php

namespace Database\Seeders;

use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanSeeder extends Seeder
{
    public function run(): void
    {
        $kasir = User::where('email', 'kasir@apotek.test')->firstOrFail();

        $transaksi = [
            [
                'no_transaksi' => 'PJ-SAMPLE-001',
                'hari' => 1,
                'pelanggan' => 'Dewi Anggraini',
                'pembayaran' => 'Tunai',
                'items' => [['kode_obat' => 'OBT001', 'jumlah' => 2], ['kode_obat' => 'OBT005', 'jumlah' => 1]],
            ],
            [
                'no_transaksi' => 'PJ-SAMPLE-002',
                'hari' => 2,
                'pelanggan' => 'Rizky Pratama',
                'pembayaran' => 'QRIS',
                'items' => [['kode_obat' => 'OBT002', 'jumlah' => 1], ['kode_obat' => 'OBT003', 'jumlah' => 1]],
            ],
            [
                'no_transaksi' => 'PJ-SAMPLE-003',
                'hari' => 4,
                'pelanggan' => 'Pelanggan Umum',
                'pembayaran' => 'Tunai',
                'items' => [['kode_obat' => 'OBT004', 'jumlah' => 1], ['kode_obat' => 'OBT001', 'jumlah' => 1]],
            ],
            [
                'no_transaksi' => 'PJ-SAMPLE-004',
                'hari' => 7,
                'pelanggan' => 'Siti Nurhaliza',
                'pembayaran' => 'Debit',
                'items' => [['kode_obat' => 'OBT005', 'jumlah' => 3]],
            ],
            [
                'no_transaksi' => 'PJ-SAMPLE-005',
                'hari' => 10,
                'pelanggan' => 'Bambang Setiawan',
                'pembayaran' => 'QRIS',
                'items' => [['kode_obat' => 'OBT003', 'jumlah' => 2], ['kode_obat' => 'OBT001', 'jumlah' => 1]],
            ],
        ];

        DB::transaction(function () use ($transaksi, $kasir): void {
            foreach ($transaksi as $index => $data) {
                $penjualan = Penjualan::updateOrCreate(
                    ['no_transaksi' => $data['no_transaksi']],
                    [
                        'user_id' => $kasir->id,
                        'nama_pelanggan' => $data['pelanggan'],
                        'tanggal_penjualan' => now()->subDays($data['hari'])->setTime(9 + $index, 15),
                        'total' => 0,
                        'metode_pembayaran' => $data['pembayaran'],
                    ]
                );

                $penjualan->detail()->delete();
                $total = 0;

                foreach ($data['items'] as $item) {
                    $obat = Obat::where('kode_obat', $item['kode_obat'])->firstOrFail();
                    $subtotal = (float) $obat->harga_jual * $item['jumlah'];
                    $total += $subtotal;

                    $penjualan->detail()->create([
                        'obat_id' => $obat->id,
                        'jumlah' => $item['jumlah'],
                        'harga_jual' => $obat->harga_jual,
                        'subtotal' => $subtotal,
                    ]);
                }

                $penjualan->update(['total' => $total]);
            }
        });
    }
}
