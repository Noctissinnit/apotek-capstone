<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.lihat',
            'user.kelola',
            'obat.lihat',
            'obat.kelola',
            'supplier.kelola',
            'pembelian.lihat',
            'pembelian.kelola',
            'penjualan.lihat',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::firstOrCreate(['name' => 'admin'])->syncPermissions($permissions);

        Role::firstOrCreate(['name' => 'kasir'])->syncPermissions([
            'dashboard.lihat',
            'obat.lihat',
            'penjualan.lihat',
        ]);
    }
}
