<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        return User::factory()->create()->assignRole($role);
    }

    public function test_halaman_login_bisa_dibuka(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/kasir/dashboard')->assertRedirect('/login');
    }

    public function test_admin_login_diarahkan_ke_dashboard_admin(): void
    {
        $admin = $this->userWithRole('admin');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Dashboard Admin');
    }

    public function test_kasir_login_diarahkan_ke_dashboard_kasir(): void
    {
        $kasir = $this->userWithRole('kasir');

        $this->post('/login', ['email' => $kasir->email, 'password' => 'password'])
            ->assertRedirect(route('kasir.dashboard'));

        $this->get(route('kasir.dashboard'))->assertOk()->assertSee('Dashboard Kasir');
    }

    public function test_password_salah_ditolak(): void
    {
        $admin = $this->userWithRole('admin');

        $this->from('/login')
            ->post('/login', ['email' => $admin->email, 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_tanpa_role_ditolak(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_kasir_tidak_bisa_akses_halaman_admin(): void
    {
        $this->actingAs($this->userWithRole('kasir'))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_tidak_bisa_akses_halaman_kasir(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('kasir.dashboard'))
            ->assertForbidden();
    }

    public function test_user_login_yang_membuka_login_diarahkan_ke_dashboardnya(): void
    {
        $this->actingAs($this->userWithRole('kasir'))
            ->get('/login')
            ->assertRedirect(route('kasir.dashboard'));
    }

    public function test_route_dashboard_mengarah_sesuai_role(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_logout(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
