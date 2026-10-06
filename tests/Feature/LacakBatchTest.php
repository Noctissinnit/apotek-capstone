<?php

namespace Tests\Feature;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\PenyesuaianStok;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman penelusuran batch: asal barang, ke mana keluarnya, dan sisanya.
 */
class LacakBatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(string $apotek = 'Apotek A'): User
    {
        $role = $apotek === 'Apotek A' ? 'admin_apotek_a' : 'admin_apotek_b';

        return User::factory()->create(['apotek' => $apotek])->assignRole($role);
    }

    private function obat(string $apotek = 'Apotek A', string $nama = 'Paracetamol'): Obat
    {
        return Obat::create([
            'kode_obat' => 'OBT-'.fake()->unique()->bothify('??##'),
            'nama_obat' => $nama,
            'apotek' => $apotek,
            'satuan' => 'Tablet',
            'isi_per_box' => 100,
            'harga_jual' => 600,
            'stok' => 0,
            'stok_minimum' => 10,
        ]);
    }

    public function test_daftar_batch_bisa_dicari_dan_disaring(): void
    {
        $admin = $this->admin();

        $this->obat(nama: 'Paracetamol')->batch()->create([
            'no_batch' => 'PCT-2609A', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(), 'stok' => 50,
        ]);
        $this->obat(nama: 'Amoxicillin')->batch()->create([
            'no_batch' => 'AMX-2610B', 'tanggal_kadaluarsa' => now()->subDays(5)->toDateString(), 'stok' => 20,
        ]);

        // cari berdasarkan nomor batch
        $this->actingAs($admin)->get(route('batch.index', ['cari' => 'PCT-26']))
            ->assertOk()->assertSeeText('PCT-2609A')->assertDontSeeText('AMX-2610B');

        // cari berdasarkan nama obat
        $this->actingAs($admin)->get(route('batch.index', ['cari' => 'Amoxi']))
            ->assertOk()->assertSeeText('AMX-2610B')->assertDontSeeText('PCT-2609A');

        // saring batch kadaluarsa
        $this->actingAs($admin)->get(route('batch.index', ['status' => 'kadaluarsa']))
            ->assertOk()->assertSeeText('AMX-2610B')->assertDontSeeText('PCT-2609A');
    }

    public function test_riwayat_menampilkan_barang_masuk_penjualan_dan_pemusnahan(): void
    {
        $admin = $this->admin();
        $obat = $this->obat();
        $supplier = Supplier::create(['kode_supplier' => 'SUP-01', 'nama_supplier' => 'PT Sumber Sehat']);

        // 1. barang masuk 5 box
        $this->actingAs($admin)->post(route('pembelian.store'), [
            'no_faktur' => 'FK-LACAK-01',
            'supplier_id' => $supplier->id,
            'tanggal_pembelian' => now()->subDays(3)->toDateString(),
            'item' => [[
                'obat_id' => $obat->id, 'satuan_input' => 'box', 'jumlah' => 5, 'harga_beli' => 45000,
                'no_batch' => 'PCT-LACAK', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            ]],
        ])->assertSessionHasNoErrors();

        $batch = BatchObat::where('no_batch', 'PCT-LACAK')->firstOrFail();
        $this->assertSame(500, $batch->stok);

        // 2. terjual 20 tablet
        $kasir = User::factory()->create(['apotek' => 'Apotek A'])->assignRole('kasir_apotek_a');
        $this->actingAs($kasir)
            ->withSession(['kasir.cart' => [$obat->id => 20]])
            ->post(route('kasir.checkout'), ['metode_pembayaran' => 'Tunai', 'uang_diterima' => 100000])
            ->assertSessionHasNoErrors();

        // 3. 30 tablet dimusnahkan
        $this->flushSession();
        $this->actingAs($admin)->post(route('penyesuaian.store'), [
            'no_dokumen' => 'BAP-LACAK-01',
            'jenis' => PenyesuaianStok::JENIS_MUSNAH,
            'tanggal' => now()->toDateString(),
            'item' => [[
                'pilih' => 1, 'batch_obat_id' => $batch->id, 'jumlah' => 30, 'alasan' => 'rusak',
            ]],
        ])->assertSessionHasNoErrors();

        $this->flushSession();

        $halaman = $this->actingAs($admin)->get(route('batch.show', $batch))->assertOk();

        $halaman->assertSeeText('FK-LACAK-01')        // dokumen barang masuk
            ->assertSeeText('PT Sumber Sehat')        // asal barang
            ->assertSeeText('Terjual di kasir')
            ->assertSeeText('BAP-LACAK-01')           // dokumen pemusnahan
            ->assertSeeText('Dimusnahkan');

        // 500 masuk, 20 terjual, 30 musnah, sisa 450
        $this->assertSame(450, $batch->fresh()->stok);
        $halaman->assertSeeText('Sisa menurut riwayat');
    }

    public function test_batch_tanpa_catatan_masuk_ditampilkan_sebagai_stok_awal(): void
    {
        $obat = $this->obat();
        $batch = $obat->batch()->create([
            'no_batch' => 'LAMA-01',
            'tanggal_kadaluarsa' => now()->addMonths(6)->toDateString(),
            'stok' => 40,
        ]);

        $this->actingAs($this->admin())
            ->get(route('batch.show', $batch))
            ->assertOk()
            ->assertSeeText('Stok awal, sebelum pencatatan batch dimulai');
    }

    public function test_kasir_boleh_menelusuri_tetapi_batch_apotek_lain_tidak_bisa_dibuka(): void
    {
        $batchA = $this->obat('Apotek A')->batch()->create([
            'no_batch' => 'A-01', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(), 'stok' => 10,
        ]);
        $batchB = $this->obat('Apotek B')->batch()->create([
            'no_batch' => 'B-01', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(), 'stok' => 10,
        ]);

        $kasir = User::factory()->create(['apotek' => 'Apotek A'])->assignRole('kasir_apotek_a');

        $this->actingAs($kasir)->get(route('batch.index'))
            ->assertOk()->assertSeeText('A-01')->assertDontSeeText('B-01');

        $this->actingAs($kasir)->get(route('batch.show', $batchA))->assertOk();
        $this->actingAs($kasir)->get(route('batch.show', $batchB))->assertNotFound();
    }
}
