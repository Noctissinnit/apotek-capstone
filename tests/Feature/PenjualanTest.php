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
            ->assertSee(route('kasir.riwayat.pdf-rentang'))
            ->assertSee('Unduh PDF semua transaksi')
            ->assertSee('tanggal_mulai')
            ->assertSee('tanggal_selesai')
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

    public function test_laporan_pdf_gabungan_mengikuti_rentang_tanggal_yang_dipilih(): void
    {
        $kasir = User::where('email', 'kasir@apotek.test')->firstOrFail();

        $response = $this->actingAs($kasir)->get(route('kasir.riwayat.pdf-rentang', [
            'tanggal_mulai' => '2026-09-27',
            'tanggal_selesai' => '2026-09-28',
        ]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="laporan-penjualan-2026-09-27-sampai-2026-09-28.pdf"');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $html = view('kasir.riwayat.pdf-rentang', [
            'penjualan' => Penjualan::with(['user', 'detail.obat'])
                ->whereBetween('tanggal_penjualan', ['2026-09-27 00:00:00', '2026-09-28 23:59:59'])
                ->orderBy('tanggal_penjualan')
                ->get(),
            'tanggalMulai' => CarbonImmutable::parse('2026-09-27'),
            'tanggalSelesai' => CarbonImmutable::parse('2026-09-28'),
            'totalKeseluruhan' => 95500,
        ])->render();

        $this->assertStringContainsString('TRX-20260927-001', $html);
        $this->assertStringContainsString('TRX-20260928-001', $html);
        $this->assertStringContainsString('Total seluruh transaksi: Rp 95.500', $html);
        $this->assertStringNotContainsString('TRX-20260926', $html);
    }

    public function test_laporan_pdf_gabungan_menolak_rentang_tanggal_yang_tidak_valid(): void
    {
        $kasir = User::where('email', 'kasir@apotek.test')->firstOrFail();

        $this->actingAs($kasir)
            ->get(route('kasir.riwayat.pdf-rentang', [
                'tanggal_mulai' => '2026-09-29',
                'tanggal_selesai' => '2026-09-28',
            ]))
            ->assertSessionHasErrors('tanggal_selesai');
    }

    public function test_riwayat_menampilkan_lima_transaksi_per_halaman(): void
    {
        $kasir = User::where('email', 'kasir@apotek.test')->firstOrFail();

        foreach (range(1, 7) as $nomor) {
            Penjualan::create([
                'no_transaksi' => 'TRX-TAMBAHAN-'.str_pad((string) $nomor, 3, '0', STR_PAD_LEFT),
                'user_id' => $kasir->id,
                'tanggal_penjualan' => CarbonImmutable::today()->setTime(12 + $nomor, 0),
                'total' => 1000 * $nomor,
            ]);
        }

        $halamanPertama = $this->actingAs($kasir)
            ->get(route('kasir.riwayat'))
            ->assertOk()
            ->assertSee('Menampilkan 1-5 dari 10 transaksi')
            ->assertSee('page=2')
            ->assertSee('TRX-TAMBAHAN-007')
            ->assertSee('TRX-TAMBAHAN-003')
            ->assertDontSee('TRX-TAMBAHAN-002');

        $halamanPertama->assertViewHas('riwayat', function ($riwayat): bool {
            return $riwayat->count() === 5
                && $riwayat->currentPage() === 1
                && $riwayat->total() === 10;
        });

        $this->get(route('kasir.riwayat', ['page' => 2]))
            ->assertOk()
            ->assertSee('Menampilkan 6-10 dari 10 transaksi')
            ->assertSee('TRX-TAMBAHAN-002')
            ->assertSee('TRX-TAMBAHAN-001')
            ->assertSee('TRX-20260928-001')
            ->assertDontSee('TRX-TAMBAHAN-007')
            ->assertViewHas('riwayat', function ($riwayat): bool {
                return $riwayat->count() === 5
                    && $riwayat->currentPage() === 2
                    && $riwayat->total() === 10;
            });
    }

    public function test_hanya_kasir_yang_dapat_mengakses_riwayat_penjualan(): void
    {
        $admin = User::where('email', 'admin@apotek.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('kasir.riwayat'))
            ->assertForbidden();
    }
}
