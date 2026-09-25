<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarTest extends TestCase
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

    public function test_admin_tidak_melihat_menu_khusus_kasir(): void
    {
        $response = $this->actingAs($this->userWithRole('admin'))->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertDontSee(route('kasir.transaksi'))
            ->assertDontSee(route('kasir.riwayat'))
            ->assertDontSee(route('kasir.monitoring'));
    }

    public function test_kasir_tidak_melihat_menu_khusus_admin(): void
    {
        $response = $this->actingAs($this->userWithRole('kasir'))->get(route('kasir.dashboard'));

        $response->assertOk()
            ->assertDontSee(route('admin.user.index'))
            ->assertDontSee(route('kategori.index'));
    }

    public function test_menu_yang_boleh_diakses_tetap_tampil(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('admin.dashboard'))
            ->assertSee(route('admin.user.index'))
            ->assertSee(route('obat.index'));

        $this->actingAs($this->userWithRole('kasir'))
            ->get(route('kasir.dashboard'))
            ->assertSee(route('kasir.transaksi'))
            ->assertSee(route('obat.index'));
    }

    public function test_hanya_satu_menu_yang_ditandai_aktif(): void
    {
        $halaman = [
            'kasir' => ['kasir.dashboard', 'kasir.transaksi', 'kasir.riwayat', 'kasir.monitoring'],
            'admin' => ['admin.dashboard', 'obat.index', 'kategori.index', 'admin.user.index'],
        ];

        foreach ($halaman as $role => $routes) {
            $user = $this->userWithRole($role);

            foreach ($routes as $route) {
                $html = $this->actingAs($user)->get(route($route))->getContent();
                $nav = str($html)->after('<nav')->before('</nav>')->toString();

                $this->assertSame(
                    1,
                    substr_count($nav, 'nav-link active'),
                    "Halaman {$route} seharusnya menandai tepat satu menu sebagai aktif."
                );
            }
        }
    }

    public function test_menu_resource_tetap_aktif_di_halaman_tambah_dan_ubah(): void
    {
        $admin = $this->userWithRole('admin');

        foreach (['obat.create', 'kategori.create', 'admin.user.create'] as $route) {
            $html = $this->actingAs($admin)->get(route($route))->getContent();
            $nav = str($html)->after('<nav')->before('</nav>')->toString();

            $this->assertSame(1, substr_count($nav, 'nav-link active'), "Halaman {$route} harus menandai menu induknya.");
        }
    }

    public function test_setiap_menu_yang_tampil_benar_benar_dapat_dibuka(): void
    {
        foreach (['admin' => 'admin.dashboard', 'kasir' => 'kasir.dashboard'] as $role => $dashboard) {
            $user = $this->userWithRole($role);

            $html = $this->actingAs($user)->get(route($dashboard))->getContent();
            $nav = str($html)->after('<nav')->before('</nav>')->toString();

            preg_match_all('/href="([^"]+)"/', $nav, $cocok);

            $this->assertNotEmpty($cocok[1], "Sidebar {$role} tidak punya menu sama sekali.");

            foreach (array_unique($cocok[1]) as $url) {
                $this->actingAs($user)->get($url)->assertOk();
            }
        }
    }
}
