<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenjualanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow('2026-09-28 12:00:00');
        $this->seed(DatabaseSeeder::class);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_kasir_melihat_riwayat_dan_rincian_penjualan_dari_database(): void
    {
        $kasir = User::where('email', 'kasir@apotek.test')->firstOrFail();

        $this->actingAs($kasir)
            ->get(route('kasir.riwayat'))
            ->assertOk()
            ->assertSee('TRX-20260928-001')
            ->assertSee('Paracetamol 500 mg')
            ->assertSee('Rp 14.000')
            ->assertSee('Rp 48.500')
            ->assertSee('PDF hari ini')
            ->assertSee('PDF kemarin');
    }

    public function test_kasir_dapat_mengunduh_laporan_pdf_hari_ini_dan_kemarin(): void
    {
        $kasir = User::where('email', 'kasir@apotek.test')->firstOrFail();

        foreach (['2026-09-28', '2026-09-27'] as $tanggal) {
            $response = $this->actingAs($kasir)
                ->get(route('kasir.riwayat.pdf', ['tanggal' => $tanggal]));

            $response->assertOk()
                ->assertHeader('content-type', 'application/pdf')
                ->assertHeader('content-disposition', 'attachment; filename="laporan-penjualan-'.$tanggal.'.pdf"');
            $this->assertStringStartsWith('%PDF', $response->getContent());
        }
    }

    public function test_laporan_pdf_menolak_tanggal_di_luar_hari_ini_dan_kemarin(): void
    {
        $kasir = User::where('email', 'kasir@apotek.test')->firstOrFail();

        $this->actingAs($kasir)
            ->get(route('kasir.riwayat.pdf', ['tanggal' => '2026-09-26']))
            ->assertSessionHasErrors('tanggal');
    }

    public function test_hanya_kasir_yang_dapat_mengakses_riwayat_penjualan(): void
    {
        $admin = User::where('email', 'admin@apotek.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('kasir.riwayat'))
            ->assertForbidden();
    }
}
