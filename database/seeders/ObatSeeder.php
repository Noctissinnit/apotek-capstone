<?php

namespace Database\Seeders;

use App\Models\Obat;
use Illuminate\Database\Seeder;

class ObatSeeder extends Seeder
{
    public function run(): void
    {
        $obat = [
            ['kode_obat' => 'OBT001', 'nama_obat' => 'Paracetamol 500 mg', 'kategori' => 'Obat Bebas', 'satuan' => 'Strip', 'harga_beli' => 3500, 'harga_jual' => 5000, 'stok' => 120, 'stok_minimum' => 20, 'tanggal_kadaluarsa' => '2028-03-31'],
            ['kode_obat' => 'OBT002', 'nama_obat' => 'Amoxicillin 500 mg', 'kategori' => 'Obat Keras', 'satuan' => 'Strip', 'harga_beli' => 6000, 'harga_jual' => 8500, 'stok' => 60, 'stok_minimum' => 15, 'tanggal_kadaluarsa' => '2027-11-30'],
            ['kode_obat' => 'OBT003', 'nama_obat' => 'Obat Batuk Hitam 100 ml', 'kategori' => 'Obat Bebas', 'satuan' => 'Botol', 'harga_beli' => 14000, 'harga_jual' => 18500, 'stok' => 35, 'stok_minimum' => 10, 'tanggal_kadaluarsa' => '2027-08-31'],
            ['kode_obat' => 'OBT004', 'nama_obat' => 'Vitamin C 1000 mg', 'kategori' => 'Vitamin', 'satuan' => 'Tube', 'harga_beli' => 22000, 'harga_jual' => 30000, 'stok' => 8, 'stok_minimum' => 10, 'tanggal_kadaluarsa' => '2027-05-31'],
            ['kode_obat' => 'OBT005', 'nama_obat' => 'Antasida Doen', 'kategori' => 'Obat Bebas', 'satuan' => 'Strip', 'harga_beli' => 2500, 'harga_jual' => 4000, 'stok' => 80, 'stok_minimum' => 20, 'tanggal_kadaluarsa' => '2028-01-31'],
            ['kode_obat' => 'OBT006', 'nama_obat' => 'Cetirizine 10 mg', 'kategori' => 'Obat Bebas Terbatas', 'satuan' => 'Strip', 'harga_beli' => 4000, 'harga_jual' => 6000, 'stok' => 5, 'stok_minimum' => 10, 'tanggal_kadaluarsa' => '2027-09-30'],
        ];

        foreach ($obat as $item) {
            Obat::updateOrCreate(['kode_obat' => $item['kode_obat']], $item);
        }
    }
}
