<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['kode_supplier' => 'SUP001', 'nama_supplier' => 'PT Sehat Farma Distribusi', 'kontak_person' => 'Budi Santoso', 'telepon' => '021-4212345', 'email' => 'order@sehatfarma.test', 'alamat' => 'Jl. Budi Utomo No. 1, Jakarta Pusat'],
            ['kode_supplier' => 'SUP002', 'nama_supplier' => 'PT Medika Utama', 'kontak_person' => 'Siti Rahma', 'telepon' => '021-5301234', 'email' => 'sales@medikautama.test', 'alamat' => 'Jl. Pemuda No. 10, Jakarta Timur'],
            ['kode_supplier' => 'SUP003', 'nama_supplier' => 'CV Nusantara Pharma', 'kontak_person' => 'Andi Wijaya', 'telepon' => '022-4608888', 'email' => 'cs@nusantarapharma.test', 'alamat' => 'Jl. Asia Afrika No. 25, Bandung'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(['kode_supplier' => $supplier['kode_supplier']], $supplier);
        }
    }
}
