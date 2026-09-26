<?php

namespace Tests\Feature;

use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\PenjualanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_kasir_dapat_melihat_riwayat_penjualan(): void
    {
        [$kasir] = $this->createSale();

        $this->actingAs($kasir)
            ->get(route('kasir.penjualan.index'))
            ->assertOk()
            ->assertSee('Riwayat Penjualan')
            ->assertSee('PJ-TEST-001')
            ->assertSee('Paracetamol 500 mg')
            ->assertSee('Rp 10.000');
    }

    public function test_riwayat_dapat_difilter_dan_total_mengikuti_filter(): void
    {
        [$kasir] = $this->createSale();

        Penjualan::create([
            'no_transaksi' => 'PJ-TEST-002',
            'user_id' => $kasir->id,
            'nama_pelanggan' => 'Pelanggan Lain',
            'tanggal_penjualan' => now()->subDays(20),
            'total' => 4500,
            'metode_pembayaran' => 'Tunai',
        ]);

        $this->actingAs($kasir)
            ->get(route('kasir.penjualan.index', ['tanggal_mulai' => now()->subDay()->toDateString(), 'q' => 'Dewi']))
            ->assertOk()
            ->assertSee('PJ-TEST-001')
            ->assertDontSee('PJ-TEST-002')
            ->assertSee('Rp 10.000');
    }

    public function test_kasir_dapat_mengunduh_laporan_pdf_dengan_filter(): void
    {
        [$kasir] = $this->createSale();

        $response = $this->actingAs($kasir)
            ->get(route('kasir.penjualan.pdf', ['q' => 'PJ-TEST-001']));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('attachment; filename=laporan-penjualan-', $response->headers->get('content-disposition'));
    }

    public function test_penjualan_tidak_dapat_diakses_admin(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('kasir.penjualan.index'))
            ->assertForbidden();
    }

    public function test_seeder_membuat_lima_transaksi_dummy_secara_idempoten(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('penjualan', 5);
        $this->seed(PenjualanSeeder::class);
        $this->assertDatabaseCount('penjualan', 5);
        $this->assertDatabaseCount('detail_penjualan', 9);
    }

    /**
     * @return array{0: User, 1: Penjualan}
     */
    private function createSale(): array
    {
        $kasir = User::factory()->create()->assignRole('kasir');
        $obat = Obat::create([
            'kode_obat' => 'OBT-TEST',
            'nama_obat' => 'Paracetamol 500 mg',
            'satuan' => 'Strip',
            'harga_beli' => 3000,
            'harga_jual' => 5000,
            'stok' => 20,
            'stok_minimum' => 5,
        ]);

        $penjualan = Penjualan::create([
            'no_transaksi' => 'PJ-TEST-001',
            'user_id' => $kasir->id,
            'nama_pelanggan' => 'Dewi Anggraini',
            'tanggal_penjualan' => now(),
            'total' => 10000,
            'metode_pembayaran' => 'Tunai',
        ]);

        $penjualan->detail()->create([
            'obat_id' => $obat->id,
            'jumlah' => 2,
            'harga_jual' => 5000,
            'subtotal' => 10000,
        ]);

        return [$kasir, $penjualan];
    }
}
