<?php

namespace Database\Seeders;

use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembelianSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@apotek.test')->firstOrFail();

        $transaksi = [
            [
                'no_faktur' => 'PB-20260901-001',
                'supplier' => 'SUP001',
                'tanggal_pembelian' => '2026-09-01',
                'items' => [
                    ['kode_obat' => 'OBT001', 'jumlah' => 50, 'no_batch' => 'PCT2609A', 'tanggal_kadaluarsa' => '2028-03-31'],
                    ['kode_obat' => 'OBT005', 'jumlah' => 40, 'no_batch' => 'ATS2609A', 'tanggal_kadaluarsa' => '2028-01-31'],
                ],
            ],
            [
                'no_faktur' => 'PB-20260910-001',
                'supplier' => 'SUP002',
                'tanggal_pembelian' => '2026-09-10',
                'items' => [
                    ['kode_obat' => 'OBT002', 'jumlah' => 30, 'no_batch' => 'AMX2609B', 'tanggal_kadaluarsa' => '2027-11-30'],
                    ['kode_obat' => 'OBT003', 'jumlah' => 20, 'no_batch' => 'OBH2609B', 'tanggal_kadaluarsa' => '2027-08-31'],
                ],
            ],
        ];

        DB::transaction(function () use ($transaksi, $admin) {
            foreach ($transaksi as $data) {
                $pembelian = Pembelian::updateOrCreate(
                    ['no_faktur' => $data['no_faktur']],
                    [
                        'supplier_id' => Supplier::where('kode_supplier', $data['supplier'])->value('id'),
                        'user_id' => $admin->id,
                        'tanggal_pembelian' => $data['tanggal_pembelian'],
                        'total' => 0,
                    ]
                );

                $pembelian->detail()->delete();

                $total = 0;
                foreach ($data['items'] as $item) {
                    $obat = Obat::where('kode_obat', $item['kode_obat'])->firstOrFail();
                    $subtotal = $obat->harga_beli * $item['jumlah'];
                    $total += $subtotal;

                    $pembelian->detail()->create([
                        'obat_id' => $obat->id,
                        'jumlah' => $item['jumlah'],
                        'harga_beli' => $obat->harga_beli,
                        'subtotal' => $subtotal,
                        'no_batch' => $item['no_batch'],
                        'tanggal_kadaluarsa' => $item['tanggal_kadaluarsa'],
                    ]);
                }

                $pembelian->update(['total' => $total]);
            }
        });
    }
}
