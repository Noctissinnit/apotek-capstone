<?php

namespace Tests\Feature\Api;

use App\Models\BatchObat;
use App\Models\Kategori;
use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Endpoint baca-saja: obat, kategori, batch, referensi, dan dashboard.
 */
class DataApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function user(string $role): User
    {
        $apotek = str_ends_with($role, '_a') ? 'Apotek A' : 'Apotek B';

        return User::factory()->create(['apotek' => $apotek])->assignRole($role);
    }

    private function obat(string $apotek, string $nama, array $tambahan = []): Obat
    {
        return Obat::create([
            'kode_obat' => 'OBT-'.fake()->unique()->bothify('??##'),
            'nama_obat' => $nama,
            'apotek' => $apotek,
            'satuan' => 'Tablet',
            'isi_per_box' => 10,
            'harga_beli' => 400,
            'harga_jual' => 600,
            'stok' => 50,
            'stok_minimum' => 10,
            ...$tambahan,
        ]);
    }

    public function test_belum_login_ditolak_401(): void
    {
        $this->getJson(route('api.v1.obat.index'))->assertUnauthorized();
        $this->getJson(route('api.v1.batch.index'))->assertUnauthorized();
    }

    public function test_daftar_obat_hanya_milik_apotek_sendiri_dan_dibagi_per_halaman(): void
    {
        foreach (range(1, 12) as $nomor) {
            $this->obat('Apotek A', sprintf('Obat A%02d', $nomor));
        }
        $this->obat('Apotek B', 'Obat Milik B');

        $respon = $this->actingAs($this->user('kasir_apotek_a'))
            ->getJson(route('api.v1.obat.index', ['sort' => 'nama']))
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('data.0.nama_obat', 'Obat A01');

        $this->assertNotContains('Obat Milik B', array_column($respon->json('data'), 'nama_obat'));
    }

    public function test_pencarian_dan_urutan_obat_sama_seperti_halaman_web(): void
    {
        $this->obat('Apotek A', 'Amoxicillin');
        $this->obat('Apotek A', 'Paracetamol');
        $this->obat('Apotek A', 'Paracetamol Sirup');

        $this->actingAs($this->user('admin_apotek_a'))
            ->getJson(route('api.v1.obat.index', ['search' => 'para', 'sort' => 'nama', 'direction' => 'desc']))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nama_obat', 'Paracetamol Sirup')
            ->assertJsonPath('data.1.nama_obat', 'Paracetamol');
    }

    public function test_saringan_tidak_dikenal_dibalas_422(): void
    {
        $admin = $this->user('admin_apotek_a');

        $this->actingAs($admin)->getJson(route('api.v1.obat.index', ['sort' => 'harga_beli']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');

        $this->actingAs($admin)->getJson(route('api.v1.batch.index', ['status' => 'entah']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_harga_beli_dan_modal_hanya_terlihat_oleh_pengelola(): void
    {
        $obat = $this->obat('Apotek A', 'Paracetamol');
        $obat->batch()->create(['no_batch' => 'PCT-1', 'stok' => 50, 'modal_per_satuan' => 40]);

        $this->actingAs($this->user('kasir_apotek_a'))
            ->getJson(route('api.v1.obat.show', $obat))
            ->assertOk()
            ->assertJsonMissingPath('data.harga_beli')
            ->assertJsonMissingPath('data.batch.0.modal_per_satuan')
            ->assertJsonPath('data.harga_jual', 600);

        $this->actingAs($this->user('admin_apotek_a'))
            ->getJson(route('api.v1.obat.show', $obat))
            ->assertOk()
            ->assertJsonPath('data.harga_beli', 400)
            ->assertJsonPath('data.batch.0.modal_per_satuan', 40);
    }

    public function test_detail_obat_berisi_batch_urut_kadaluarsa_dan_stok_siap_dijual(): void
    {
        $obat = $this->obat('Apotek A', 'Paracetamol');
        $obat->batch()->create(['no_batch' => 'JAUH', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(), 'stok' => 30]);
        $obat->batch()->create(['no_batch' => 'DEKAT', 'tanggal_kadaluarsa' => now()->addDays(20)->toDateString(), 'stok' => 15]);
        $obat->batch()->create(['no_batch' => 'LEWAT', 'tanggal_kadaluarsa' => now()->subDay()->toDateString(), 'stok' => 5]);

        $this->actingAs($this->user('kasir_apotek_a'))
            ->getJson(route('api.v1.obat.show', $obat))
            ->assertOk()
            ->assertJsonPath('data.batch.0.no_batch', 'LEWAT')
            ->assertJsonPath('data.batch.0.boleh_dijual', false)
            ->assertJsonPath('data.batch.1.no_batch', 'DEKAT')
            ->assertJsonPath('data.batch.1.tingkat_kadaluarsa', 'segera_habis')
            ->assertJsonPath('meta.stok_siap_dijual', 45);
    }

    public function test_obat_dan_batch_apotek_lain_dibalas_404(): void
    {
        $obatB = $this->obat('Apotek B', 'Obat Milik B');
        $batchB = $obatB->batch()->create(['no_batch' => 'B-1', 'stok' => 5]);
        $kasirA = $this->user('kasir_apotek_a');

        $this->actingAs($kasirA)->getJson(route('api.v1.obat.show', $obatB))
            ->assertNotFound()
            ->assertExactJson(['message' => 'Data tidak ditemukan.']);

        $this->actingAs($kasirA)->getJson(route('api.v1.batch.show', $batchB))->assertNotFound();
    }

    public function test_jumlah_obat_per_kategori_hanya_menghitung_apotek_sendiri(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Analgesik']);
        $this->obat('Apotek A', 'Paracetamol', ['kategori_id' => $kategori->id_kategori]);
        $this->obat('Apotek B', 'Ibuprofen', ['kategori_id' => $kategori->id_kategori]);
        $this->obat('Apotek B', 'Asam Mefenamat', ['kategori_id' => $kategori->id_kategori]);

        $this->actingAs($this->user('kasir_apotek_a'))
            ->getJson(route('api.v1.kategori.index'))
            ->assertOk()
            ->assertJsonPath('data.0.nama', 'Analgesik')
            ->assertJsonPath('data.0.jumlah_obat', 1);

        // halaman web Data Kategori memakai aturan yang sama
        $this->actingAs($this->user('admin_apotek_b'))
            ->get(route('kategori.index'))
            ->assertOk()
            ->assertViewHas('kategori', fn ($daftar) => $daftar->first()->obat_count === 2);
    }

    public function test_daftar_batch_bisa_disaring_dan_detailnya_berisi_riwayat(): void
    {
        $obat = $this->obat('Apotek A', 'Paracetamol');
        $siap = $obat->batch()->create(['no_batch' => 'SIAP-1', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(), 'stok' => 20]);
        $obat->batch()->create(['no_batch' => 'LEWAT-1', 'tanggal_kadaluarsa' => now()->subDays(3)->toDateString(), 'stok' => 4]);

        $kasir = $this->user('kasir_apotek_a');

        $this->actingAs($kasir)->getJson(route('api.v1.batch.index', ['status' => 'kadaluarsa']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.no_batch', 'LEWAT-1')
            ->assertJsonPath('data.0.obat.nama_obat', 'Paracetamol');

        // batch tanpa catatan barang masuk: seluruh stoknya tercatat sebagai stok awal
        $this->actingAs($kasir)->getJson(route('api.v1.batch.show', $siap))
            ->assertOk()
            ->assertJsonPath('data.no_batch', 'SIAP-1')
            ->assertJsonPath('riwayat', [])
            ->assertJsonPath('meta.stok_awal', 20)
            ->assertJsonPath('meta.total_masuk', 0);
    }

    public function test_referensi_berisi_pilihan_tetap(): void
    {
        $this->actingAs($this->user('kasir_apotek_a'))
            ->getJson(route('api.v1.referensi'))
            ->assertOk()
            ->assertJsonPath('data.satuan', Obat::SATUAN)
            ->assertJsonPath('data.status_batch', BatchObat::SARINGAN_STATUS);
    }

    public function test_dashboard_dijaga_sesuai_role(): void
    {
        $kasir = $this->user('kasir_apotek_a');
        $admin = $this->user('admin_apotek_a');

        $this->actingAs($kasir)->getJson(route('api.v1.dashboard.admin'))
            ->assertForbidden()
            ->assertExactJson(['message' => 'Anda tidak memiliki akses ke data ini.']);
        $this->actingAs($admin)->getJson(route('api.v1.dashboard.kasir'))->assertForbidden();
        $this->actingAs($admin)->getJson(route('api.v1.monitoring'))->assertForbidden();

        $this->actingAs($kasir)->getJson(route('api.v1.dashboard.kasir'))->assertOk();
        $this->actingAs($kasir)->getJson(route('api.v1.monitoring'))->assertOk();
    }

    public function test_dashboard_admin_hanya_menghitung_pembelian_apotek_sendiri(): void
    {
        $supplier = Supplier::create(['kode_supplier' => 'SUP-001', 'nama_supplier' => 'PT Uji']);
        $adminA = $this->user('admin_apotek_a');
        $adminB = $this->user('admin_apotek_b');

        foreach ([[$adminA, 'FK-A', 100000], [$adminB, 'FK-B', 999000]] as [$admin, $faktur, $total]) {
            Pembelian::create([
                'no_faktur' => $faktur,
                'supplier_id' => $supplier->id,
                'user_id' => $admin->id,
                'apotek' => $admin->apotek,
                'tanggal_pembelian' => now()->toDateString(),
                'total' => $total,
            ]);
        }

        $this->actingAs($adminA)->getJson(route('api.v1.dashboard.admin'))
            ->assertOk()
            ->assertJsonPath('data.total_pembelian_bulan_ini', 100000)
            ->assertJsonCount(1, 'data.pembelian_terbaru')
            ->assertJsonPath('data.pembelian_terbaru.0.no_faktur', 'FK-A');

        // sebelumnya halaman web ikut menjumlahkan faktur apotek lain
        $this->actingAs($adminA)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('FK-A')
            ->assertDontSee('FK-B');
    }

    public function test_monitoring_mengelompokkan_batch_per_tingkat_peringatan(): void
    {
        $obat = $this->obat('Apotek A', 'Paracetamol', ['stok' => 3]);
        $obat->batch()->create(['no_batch' => 'LEWAT', 'tanggal_kadaluarsa' => now()->subDay()->toDateString(), 'stok' => 1]);
        $obat->batch()->create(['no_batch' => 'DEKAT', 'tanggal_kadaluarsa' => now()->addDays(10)->toDateString(), 'stok' => 1]);
        $obat->batch()->create(['no_batch' => 'RETUR', 'tanggal_kadaluarsa' => now()->addDays(60)->toDateString(), 'stok' => 1]);
        $obat->batch()->create(['no_batch' => 'AMAN', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(), 'stok' => 1]);

        $this->actingAs($this->user('kasir_apotek_a'))
            ->getJson(route('api.v1.monitoring'))
            ->assertOk()
            ->assertJsonPath('data.batch.kadaluarsa.0.no_batch', 'LEWAT')
            ->assertJsonPath('data.batch.segera_habis.0.no_batch', 'DEKAT')
            ->assertJsonPath('data.batch.siapkan_retur.0.no_batch', 'RETUR')
            ->assertJsonPath('data.obat_menipis.0.nama_obat', 'Paracetamol');
    }
}
