<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Masuk dan keluar lewat API untuk frontend Next.js (cookie session Sanctum).
 */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        // Sanctum hanya memberi session pada request dari domain frontend yang terdaftar
        $this->withHeader('Origin', 'http://localhost');
    }

    private function kasir(): User
    {
        return User::factory()->create([
            'apotek' => 'Apotek A',
            'email' => 'kasir@apotek.test',
            'password' => 'rahasia123',
        ])->assignRole('kasir_apotek_a');
    }

    public function test_user_dapat_masuk_melihat_dirinya_lalu_keluar(): void
    {
        $this->kasir();

        $this->postJson(route('api.v1.login'), ['email' => 'kasir@apotek.test', 'password' => 'rahasia123'])
            ->assertOk()
            ->assertJsonPath('data.email', 'kasir@apotek.test')
            ->assertJsonPath('data.apotek', 'Apotek A')
            ->assertJsonPath('data.jenis', 'kasir')
            ->assertJsonPath('data.peran', ['kasir_apotek_a']);

        $this->getJson(route('api.v1.me'))
            ->assertOk()
            ->assertJsonPath('data.email', 'kasir@apotek.test');

        $this->postJson(route('api.v1.logout'))->assertOk();

        // guard di-reset agar request berikutnya benar-benar membaca session yang sudah dihapus
        $this->app['auth']->forgetGuards();

        $this->getJson(route('api.v1.me'))->assertUnauthorized();
    }

    public function test_daftar_izin_ikut_dikirim_untuk_menyusun_menu(): void
    {
        $admin = User::factory()->create(['apotek' => 'Apotek B'])->assignRole('admin_apotek_b');

        $izin = $this->actingAs($admin)->getJson(route('api.v1.me'))
            ->assertOk()
            ->assertJsonPath('data.jenis', 'admin')
            ->json('data.izin');

        $this->assertContains('obat.kelola', $izin);
        $this->assertContains('user.kelola', $izin);
    }

    public function test_password_salah_dibalas_422_dengan_pesan_per_kolom(): void
    {
        $this->kasir();

        $this->postJson(route('api.v1.login'), ['email' => 'kasir@apotek.test', 'password' => 'salah'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => 'Email atau password salah.']);

        $this->getJson(route('api.v1.me'))->assertUnauthorized();
    }

    public function test_user_tanpa_role_tidak_bisa_masuk(): void
    {
        User::factory()->create(['email' => 'tanpa-role@apotek.test', 'password' => 'rahasia123']);

        $this->postJson(route('api.v1.login'), ['email' => 'tanpa-role@apotek.test', 'password' => 'rahasia123'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->getJson(route('api.v1.me'))->assertUnauthorized();
    }

    public function test_percobaan_masuk_dibatasi(): void
    {
        $this->kasir();

        foreach (range(1, 5) as $_) {
            $this->postJson(route('api.v1.login'), ['email' => 'kasir@apotek.test', 'password' => 'salah']);
        }

        // password benar pun ditolak sampai batas waktunya lewat
        $this->postJson(route('api.v1.login'), ['email' => 'kasir@apotek.test', 'password' => 'rahasia123'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', fn (string $pesan) => str_starts_with($pesan, 'Terlalu banyak percobaan login'));
    }

    public function test_error_api_selalu_json_berbahasa_indonesia(): void
    {
        // tanpa header Accept: application/json pun tetap dibalas JSON, bukan dialihkan ke /login
        $this->get('/api/v1/me')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Silakan masuk terlebih dahulu.']);

        $this->get('/api/v1/tidak-ada')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Data tidak ditemukan.']);
    }

    public function test_frontend_boleh_memanggil_api_dengan_cookie(): void
    {
        config(['cors.allowed_origins' => ['http://localhost:3000']]);

        $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'POST',
        ])->options('/api/v1/login')
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }
}
