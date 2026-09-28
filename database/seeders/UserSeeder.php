<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $seedUser = function (string $email, ?string $oldEmail, string $name, string $apotek, string $role): void {
            $user = User::where('email', $email)->first()
                ?? ($oldEmail ? User::where('email', $oldEmail)->first() : null)
                ?? new User;

            $user->fill(compact('email', 'name', 'apotek'));
            $user->password = $user->exists ? $user->password : 'password';
            $user->email_verified_at ??= now();
            $user->save();
            $user->syncRoles($role);
        };

        $seedUser('admin.a@apotek.test', 'admin@apotek.test', 'Administrator Apotek A', 'Apotek A', 'admin_apotek_a');
        $seedUser('kasir.a@apotek.test', 'kasir@apotek.test', 'Kasir Apotek A', 'Apotek A', 'kasir_apotek_a');
        $seedUser('admin.b@apotek.test', null, 'Administrator Apotek B', 'Apotek B', 'admin_apotek_b');
        $seedUser('kasir.b@apotek.test', null, 'Kasir Apotek B', 'Apotek B', 'kasir_apotek_b');
    }
}
