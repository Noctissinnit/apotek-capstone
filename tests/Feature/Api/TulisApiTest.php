<?php

namespace Tests\Feature\Api;

use App\Models\BatchObat;
use App\Models\Kategori;
use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\PenyesuaianStok;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Endpoint yang mengubah data: obat, kategori, barang masuk, retur dan pemusnahan.
 */
class TulisApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('unggahan');
    }

    private function user(string $role): User
    {
        $apotek = str_ends_with($role, '_a') ? 'Apotek A' : 'Apotek B';

        return User::factory()->create(['apotek' => $apotek])->assignRole($role);
    }

    private function obat(string $apotek = 'Apotek A', array $tambahan = []): Obat
    {
        return Obat::create([
            'kode_obat' => 'OBT-'.fake()->unique()->bothify('??##'),
            'nama_obat' => 'Paracetamol',
            'apotek' => $apotek,
            'satuan' => 'Tablet',
            'isi_per_box' => 100,
            'harga_beli' => 400,
            'harga_jual' => 600,
            'stok' => 0,
            'stok_minimum' => 10,
            ...$tambahan,
        ]);
    }

    private function isianObat(array $tambahan = []): array
    {
        return [
            'kode_obat' => 'OBT-BARU',
            'nama_obat' => 'Amoxicillin',
            'satuan' => 'Kapsul',
            'isi_per_box' => 10,
            'harga_beli' => 1500,
            'harga_jual' => 2000,
            'stok' => 40,
            'stok_minimum' => 5,
            ...$tambahan,
        ];
    }

    private function supplier(): Supplier
    {
        return Supplier::create(['kode_supplier' => 'SUP-'.fake()->unique()->numerify('###'), 'nama_supplier' => 'PT Uji']);
    }

    // ------------------------------------------------------------------
    // Obat
    // ------------------------------------------------------------------

    public function test_admin_menambah_obat_dan_stoknya_jadi_batch_stok_awal(): void
    {
        $admin = $this->user('admin_apotek_a');

        $id = $this->actingAs($admin)
            ->postJson(route('api.v1.obat.store'), $this->isianObat())
            ->assertCreated()
            ->assertJsonPath('data.kode_obat', 'OBT-BARU')
            ->assertJsonPath('data.apotek', 'Apotek A')
            ->json('data.id');

        $this->assertDatabaseHas('batch_obat', ['obat_id' => $id, 'no_batch' => 'STOK-AWAL', 'stok' => 40]);
    }

    public function test_kasir_tidak_boleh_mengubah_data_obat(): void
    {
        $kasir = $this->user('kasir_apotek_a');
        $obat = $this->obat();

        $this->actingAs($kasir)->postJson(route('api.v1.obat.store'), $this->isianObat())->assertForbidden();
        $this->actingAs($kasir)->putJson(route('api.v1.obat.update', $obat), $this->isianObat())->assertForbidden();
        $this->actingAs($kasir)->deleteJson(route('api.v1.obat.destroy', $obat))->assertForbidden();
    }

    public function test_isian_obat_salah_dibalas_422_per_kolom(): void
    {
        $this->actingAs($this->user('admin_apotek_a'))
            ->postJson(route('api.v1.obat.store'), $this->isianObat(['satuan' => 'Karung', 'harga_jual' => -1]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['satuan', 'harga_jual']);
    }

    public function test_ubah_obat_dengan_gambar_lewat_post_method_put(): void
    {
        $admin = $this->user('admin_apotek_a');
        $obat = $this->obat('Apotek A', ['stok' => 30]);
        $obat->batch()->create(['no_batch' => 'B-1', 'stok' => 30]);

        $this->actingAs($admin)
            ->post(route('api.v1.obat.update', $obat), [
                ...$this->isianObat(['kode_obat' => $obat->kode_obat, 'nama_obat' => 'Paracetamol 500', 'stok' => 999]),
                '_method' => 'PUT',
                'gambar' => UploadedFile::fake()->image('kemasan.jpg'),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.nama_obat', 'Paracetamol 500')
            // stok yang sudah terpecah jadi batch tidak berubah lewat form obat
            ->assertJsonPath('data.stok', 30);

        $obat->refresh();
        $this->assertNotNull($obat->gambar);
        Storage::disk('unggahan')->assertExists($obat->gambar);
    }

    public function test_obat_apotek_lain_tidak_bisa_diubah_atau_dihapus(): void
    {
        $admin = $this->user('admin_apotek_a');
        $obatB = $this->obat('Apotek B');

        $this->actingAs($admin)->putJson(route('api.v1.obat.update', $obatB), $this->isianObat())->assertNotFound();
        $this->actingAs($admin)->deleteJson(route('api.v1.obat.destroy', $obatB))->assertNotFound();

        $this->assertNotSoftDeleted($obatB);
    }

    public function test_hapus_obat(): void
    {
        $obat = $this->obat();

        $this->actingAs($this->user('admin_apotek_a'))
            ->deleteJson(route('api.v1.obat.destroy', $obat))
            ->assertNoContent();

        $this->assertSoftDeleted($obat);
    }

    // ------------------------------------------------------------------
    // Kategori
    // ------------------------------------------------------------------

    public function test_kelola_kategori(): void
    {
        $admin = $this->user('admin_apotek_a');

        $id = $this->actingAs($admin)->postJson(route('api.v1.kategori.store'), ['nama_kategori' => 'Vitamin'])
            ->assertCreated()
            ->assertJsonPath('data.nama', 'Vitamin')
            ->json('data.id');

        $this->actingAs($admin)->postJson(route('api.v1.kategori.store'), ['nama_kategori' => 'Vitamin'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nama_kategori');

        $this->actingAs($admin)->putJson(route('api.v1.kategori.update', $id), ['nama_kategori' => 'Vitamin & Suplemen'])
            ->assertOk()
            ->assertJsonPath('data.nama', 'Vitamin & Suplemen');

        $this->actingAs($admin)->deleteJson(route('api.v1.kategori.destroy', $id))->assertNoContent();
        $this->assertDatabaseMissing('kategori', ['id_kategori' => $id]);
    }

    public function test_kategori_yang_dipakai_obat_dibalas_409(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Analgesik']);
        // dipakai obat apotek lain pun tetap tidak boleh dihapus
        $this->obat('Apotek B', ['kategori_id' => $kategori->id_kategori]);

        $this->actingAs($this->user('admin_apotek_a'))
            ->deleteJson(route('api.v1.kategori.destroy', $kategori))
            ->assertStatus(409)
            ->assertJsonPath('message', 'Kategori yang masih digunakan obat tidak dapat dihapus.');

        $this->assertDatabaseHas('kategori', ['id_kategori' => $kategori->id_kategori]);
    }

    public function test_kasir_tidak_boleh_mengelola_kategori_dan_melihat_supplier(): void
    {
        $kasir = $this->user('kasir_apotek_a');

        $this->actingAs($kasir)->postJson(route('api.v1.kategori.store'), ['nama_kategori' => 'Vitamin'])->assertForbidden();
        $this->actingAs($kasir)->getJson(route('api.v1.supplier.index'))->assertForbidden();
        $this->actingAs($kasir)->getJson(route('api.v1.pembelian.index'))->assertForbidden();
        $this->actingAs($kasir)->getJson(route('api.v1.penyesuaian.index'))->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Barang masuk
    // ------------------------------------------------------------------

    public function test_barang_masuk_menambah_stok_membentuk_batch_dan_nomor_faktur_otomatis(): void
    {
        $admin = $this->user('admin_apotek_a');
        $obat = $this->obat();

        $respon = $this->actingAs($admin)->postJson(route('api.v1.pembelian.store'), [
            'supplier_id' => $this->supplier()->id,
            'tanggal_pembelian' => now()->toDateString(),
            'item' => [[
                'obat_id' => $obat->id,
                'satuan_input' => 'box',
                'jumlah' => 2,
                'harga_beli' => 50000,
                'diskon_persen' => 10,
                'no_batch' => 'PCT-2610',
                'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            ]],
        ])->assertCreated();

        $respon->assertJsonPath('data.no_faktur', 'FK-'.now()->format('Ymd').'-001')
            ->assertJsonPath('data.total', 90000)
            ->assertJsonPath('data.detail.0.jumlah_satuan', 200)
            ->assertJsonPath('data.detail.0.modal_per_satuan', 450);

        $this->assertSame(200, $obat->fresh()->stok);
        $this->assertDatabaseHas('batch_obat', ['obat_id' => $obat->id, 'no_batch' => 'PCT-2610', 'stok' => 200]);
    }

    public function test_barang_masuk_untuk_obat_apotek_lain_dibatalkan_seluruhnya(): void
    {
        $admin = $this->user('admin_apotek_a');
        $obatA = $this->obat();
        $obatB = $this->obat('Apotek B');

        $baris = fn (Obat $obat) => [
            'obat_id' => $obat->id, 'satuan_input' => 'satuan', 'jumlah' => 5,
            'harga_beli' => 500, 'no_batch' => 'X-'.$obat->id,
        ];

        $this->actingAs($admin)->postJson(route('api.v1.pembelian.store'), [
            'supplier_id' => $this->supplier()->id,
            'tanggal_pembelian' => now()->toDateString(),
            'item' => [$baris($obatA), $baris($obatB)],
        ])->assertUnprocessable()->assertJsonValidationErrors('item');

        $this->assertSame(0, Pembelian::count());
        $this->assertSame(0, $obatA->fresh()->stok);
    }

    public function test_daftar_dan_detail_pembelian_hanya_apotek_sendiri(): void
    {
        $supplier = $this->supplier();
        $adminA = $this->user('admin_apotek_a');
        $adminB = $this->user('admin_apotek_b');

        $fakturB = Pembelian::create([
            'no_faktur' => 'FK-B', 'supplier_id' => $supplier->id, 'user_id' => $adminB->id,
            'apotek' => 'Apotek B', 'tanggal_pembelian' => now()->toDateString(), 'total' => 1000,
        ]);

        $this->actingAs($adminA)->getJson(route('api.v1.pembelian.index'))
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->actingAs($adminA)->getJson(route('api.v1.pembelian.show', $fakturB))->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Retur dan pemusnahan
    // ------------------------------------------------------------------

    private function batch(Obat $obat, int $stok = 20): BatchObat
    {
        $obat->increment('stok', $stok);

        return $obat->batch()->create([
            'no_batch' => 'B-'.fake()->unique()->numerify('###'),
            'tanggal_kadaluarsa' => now()->subDays(2)->toDateString(),
            'stok' => $stok,
            'modal_per_satuan' => 500,
        ]);
    }

    public function test_pemusnahan_mengurangi_stok_batch_dan_obat(): void
    {
        $admin = $this->user('admin_apotek_a');
        $obat = $this->obat();
        $batch = $this->batch($obat);

        $this->actingAs($admin)->postJson(route('api.v1.penyesuaian.store'), [
            'jenis' => 'musnah',
            'tanggal' => now()->toDateString(),
            'item' => [['batch_obat_id' => $batch->id, 'jumlah' => 8, 'alasan' => 'kadaluarsa']],
        ])
            ->assertCreated()
            ->assertJsonPath('data.no_dokumen', 'BAP-'.now()->format('Ymd').'-001')
            ->assertJsonPath('data.total_nilai', 4000)
            ->assertJsonPath('data.detail.0.no_batch', $batch->no_batch);

        $this->assertSame(12, $batch->fresh()->stok);
        $this->assertSame(12, $obat->fresh()->stok);
    }

    public function test_retur_wajib_menyebut_supplier_dan_jumlah_tidak_boleh_melebihi_stok(): void
    {
        $admin = $this->user('admin_apotek_a');
        $batch = $this->batch($this->obat(), 5);

        $this->actingAs($admin)->postJson(route('api.v1.penyesuaian.store'), [
            'jenis' => 'retur',
            'tanggal' => now()->toDateString(),
            'item' => [['batch_obat_id' => $batch->id, 'jumlah' => 1, 'alasan' => 'rusak']],
        ])->assertUnprocessable()->assertJsonValidationErrors(['supplier_id' => 'Retur harus menyebutkan supplier tujuan.']);

        $this->actingAs($admin)->postJson(route('api.v1.penyesuaian.store'), [
            'jenis' => 'retur',
            'tanggal' => now()->toDateString(),
            'supplier_id' => $this->supplier()->id,
            'item' => [['batch_obat_id' => $batch->id, 'jumlah' => 6, 'alasan' => 'rusak']],
        ])->assertUnprocessable()->assertJsonValidationErrors('item');

        $this->assertSame(0, PenyesuaianStok::count());
        $this->assertSame(5, $batch->fresh()->stok);
    }

    public function test_batch_apotek_lain_tidak_bisa_dimusnahkan(): void
    {
        $batchB = $this->batch($this->obat('Apotek B'));

        $this->actingAs($this->user('admin_apotek_a'))->postJson(route('api.v1.penyesuaian.store'), [
            'jenis' => 'musnah',
            'tanggal' => now()->toDateString(),
            'item' => [['batch_obat_id' => $batchB->id, 'jumlah' => 1, 'alasan' => 'rusak']],
        ])->assertUnprocessable();

        $this->assertSame(20, $batchB->fresh()->stok);
    }

    public function test_daftar_detail_dan_pdf_penyesuaian(): void
    {
        $admin = $this->user('admin_apotek_a');
        $batch = $this->batch($this->obat());

        $id = $this->actingAs($admin)->postJson(route('api.v1.penyesuaian.store'), [
            'jenis' => 'musnah',
            'tanggal' => now()->toDateString(),
            'item' => [['batch_obat_id' => $batch->id, 'jumlah' => 2, 'alasan' => 'rusak']],
        ])->json('data.id');

        $this->actingAs($admin)->getJson(route('api.v1.penyesuaian.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $id)
            ->assertJsonPath('ringkasan.nilai_bulan_ini.musnah', 1000)
            ->assertJsonPath('ringkasan.jumlah_batch_kadaluarsa', 1);

        $this->actingAs($admin)->getJson(route('api.v1.penyesuaian.show', $id))
            ->assertOk()
            ->assertJsonPath('data.pdf_url', route('api.v1.penyesuaian.pdf', $id));

        $this->actingAs($admin)->get(route('api.v1.penyesuaian.pdf', $id))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($this->user('admin_apotek_b'))->getJson(route('api.v1.penyesuaian.show', $id))->assertNotFound();
    }
}
