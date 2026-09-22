<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@apotek.test'],
            ['name' => 'Administrator', 'password' => 'password', 'email_verified_at' => now()]
        );
        $admin->syncRoles('admin');

        $kasir = User::firstOrCreate(
            ['email' => 'kasir@apotek.test'],
            ['name' => 'Kasir Apotek', 'password' => 'password', 'email_verified_at' => now()]
        );
        $kasir->syncRoles('kasir');
    }
}
