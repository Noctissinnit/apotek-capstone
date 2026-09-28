<?php

namespace Tests\Feature;

use App\Models\Penjualan;
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
            ->assertSee(route('kasir.riwayat.pdf', Penjualan::where('no_transaksi', 'TRX-20260928-001')->firstOrFail()))
            ->assertSee(route('kasir.riwayat.pdf', Penjualan::where('no_transaksi', 'TRX-20260927-001')->firstOrFail()))
            ->assertDontSee('PDF hari ini')
            ->assertDontSee('PDF kemarin');
    }

    public function test_setiap_transaksi_mengunduh_pdf_masing_masing_dengan_nama_file_unik(): void
    {
        $kasir = User::where('email', 'kasir@apotek.test')->firstOrFail();
        $transaksiHariIni = Penjualan::where('no_transaksi', 'TRX-20260928-001')->firstOrFail();
        $transaksiKemarin = Penjualan::where('no_transaksi', 'TRX-20260927-001')->firstOrFail();

        $pdfHariIni = $this->actingAs($kasir)
            ->get(route('kasir.riwayat.pdf', $transaksiHariIni));
        $pdfKemarin = $this->actingAs($kasir)
            ->get(route('kasir.riwayat.pdf', $transaksiKemarin));

        $pdfHariIni->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="laporan-'.$transaksiHariIni->id.'-2026-09-28.pdf"');
        $pdfKemarin->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="laporan-'.$transaksiKemarin->id.'-2026-09-27.pdf"');
        $this->assertStringStartsWith('%PDF', $pdfHariIni->getContent());
        $this->assertStringStartsWith('%PDF', $pdfKemarin->getContent());
        $this->assertNotSame($pdfHariIni->getContent(), $pdfKemarin->getContent());
    }

    public function test_pdf_hanya_dapat_dibuat_untuk_transaksi_yang_tersimpan(): void
    {
        $kasir = User::where('email', 'kasir@apotek.test')->firstOrFail();

        $this->actingAs($kasir)
            ->get(route('kasir.riwayat.pdf', 999999))
            ->assertNotFound();
    }

    public function test_hanya_kasir_yang_dapat_mengakses_riwayat_penjualan(): void
    {
        $admin = User::where('email', 'admin@apotek.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('kasir.riwayat'))
            ->assertForbidden();
    }
}
