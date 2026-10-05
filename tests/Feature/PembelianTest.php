<?php

namespace Tests\Feature;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CR-01-B: modul barang masuk.
 */
class PembelianTest extends TestCase
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

    private function obat(string $apotek = 'Apotek A', int $isiPerBox = 100, int $stok = 0): Obat
    {
        return Obat::create([
            'kode_obat' => 'OBT-'.fake()->unique()->bothify('??##'),
            'nama_obat' => 'Paracetamol',
            'apotek' => $apotek,
            'satuan' => 'Tablet',
            'isi_per_box' => $isiPerBox,
            'harga_jual' => 600,
            'stok' => $stok,
            'stok_minimum' => 10,
        ]);
    }

    /** @param array<int, array<string, mixed>> $item */
    private function faktur(array $item, array $tambahan = []): array
    {
        return array_merge([
            'no_faktur' => 'FK-UJI-'.fake()->unique()->numerify('###'),
            'supplier_id' => Supplier::create(['kode_supplier' => 'SUP-'.fake()->unique()->numerify('###'), 'nama_supplier' => 'PT Uji'])->id,
            'tanggal_pembelian' => now()->toDateString(),
            'item' => $item,
        ], $tambahan);
    }

    public function test_beli_per_box_menambah_stok_sesuai_konversi(): void
    {
        $admin = $this->admin();
        $obat = $this->obat(isiPerBox: 100, stok: 20);

        $this->actingAs($admin)->post(route('pembelian.store'), $this->faktur([[
            'obat_id' => $obat->id,
            'satuan_input' => 'box',
            'jumlah' => 5,
            'harga_beli' => 45000,
            'diskon_persen' => 10,
            'no_batch' => 'PCT-2609A',
            'tanggal_kadaluarsa' => now()->addYears(2)->toDateString(),
        ]]))->assertSessionHasNoErrors();

        // 5 box x 100 tablet = 500, ditambah stok awal 20
        $this->assertSame(520, $obat->fresh()->stok);

        $batch = BatchObat::where('no_batch', 'PCT-2609A')->firstOrFail();
        $this->assertSame(500, $batch->stok);

        // (45.000 - 10%) / 100 tablet = 405
        $this->assertSame('405.00', $batch->modal_per_satuan);

        // 5 x 45.000 - 10% = 202.500
        $this->assertSame('202500.00', Pembelian::firstOrFail()->total);
    }

    public function test_beli_eceran_dalam_satuan_terkecil(): void
    {
        $admin = $this->admin();
        $obat = $this->obat(isiPerBox: 100, stok: 0);

        $this->actingAs($admin)->post(route('pembelian.store'), $this->faktur([[
            'obat_id' => $obat->id,
            'satuan_input' => 'satuan',
            'jumlah' => 30,
            'harga_beli' => 500,
            'no_batch' => 'PCT-ECER',
            'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
        ]]))->assertSessionHasNoErrors();

        $this->assertSame(30, $obat->fresh()->stok);
        $this->assertSame('500.00', BatchObat::where('no_batch', 'PCT-ECER')->firstOrFail()->modal_per_satuan);
        $this->assertSame('15000.00', Pembelian::firstOrFail()->total);
    }

    public function test_satu_obat_dua_batch_dalam_satu_faktur(): void
    {
        $admin = $this->admin();
        $obat = $this->obat(isiPerBox: 100);

        $this->actingAs($admin)->post(route('pembelian.store'), $this->faktur([
            [
                'obat_id' => $obat->id, 'satuan_input' => 'box', 'jumlah' => 3, 'harga_beli' => 45000,
                'no_batch' => 'PCT-A', 'tanggal_kadaluarsa' => now()->addYears(2)->toDateString(),
            ],
            [
                'obat_id' => $obat->id, 'satuan_input' => 'box', 'jumlah' => 2, 'harga_beli' => 45000,
                'no_batch' => 'PCT-B', 'tanggal_kadaluarsa' => now()->addYears(3)->toDateString(),
            ],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(500, $obat->fresh()->stok);
        $this->assertSame(2, $obat->batch()->count());
        $this->assertSame(300, BatchObat::where('no_batch', 'PCT-A')->firstOrFail()->stok);
        $this->assertSame(200, BatchObat::where('no_batch', 'PCT-B')->firstOrFail()->stok);

        // batch paling dekat kadaluarsa yang diambil duluan nanti saat menjual
        $this->assertSame('PCT-A', $obat->batchTerdekatKadaluarsa()->no_batch);
    }

    public function test_batch_dengan_nomor_sama_menambah_stok_batch_yang_ada(): void
    {
        $admin = $this->admin();
        $obat = $this->obat(isiPerBox: 10);

        foreach ([2, 3] as $jumlah) {
            $this->actingAs($admin)->post(route('pembelian.store'), $this->faktur([[
                'obat_id' => $obat->id, 'satuan_input' => 'box', 'jumlah' => $jumlah, 'harga_beli' => 10000,
                'no_batch' => 'SAMA-01', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            ]]))->assertSessionHasNoErrors();
        }

        $this->assertSame(1, $obat->batch()->count());
        $this->assertSame(50, BatchObat::where('no_batch', 'SAMA-01')->firstOrFail()->stok);
        $this->assertSame(50, $obat->fresh()->stok);
    }

    public function test_tanpa_diskon_tetap_bisa_disimpan(): void
    {
        $admin = $this->admin();
        $obat = $this->obat(isiPerBox: 10);

        $this->actingAs($admin)->post(route('pembelian.store'), $this->faktur([[
            'obat_id' => $obat->id, 'satuan_input' => 'box', 'jumlah' => 1, 'harga_beli' => 20000,
            'diskon_persen' => null, 'no_batch' => 'NODISC', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
        ]]))->assertSessionHasNoErrors();

        $this->assertSame('2000.00', BatchObat::where('no_batch', 'NODISC')->firstOrFail()->modal_per_satuan);
        $this->assertSame('20000.00', Pembelian::firstOrFail()->total);
    }

    public function test_faktur_tanpa_obat_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('pembelian.store'), $this->faktur([]))
            ->assertSessionHasErrors('item');

        $this->assertSame(0, Pembelian::count());
    }

    public function test_tanggal_kadaluarsa_yang_sudah_lewat_ditolak(): void
    {
        $obat = $this->obat();

        $this->actingAs($this->admin())
            ->post(route('pembelian.store'), $this->faktur([[
                'obat_id' => $obat->id, 'satuan_input' => 'box', 'jumlah' => 1, 'harga_beli' => 1000,
                'no_batch' => 'EXP', 'tanggal_kadaluarsa' => now()->subDay()->toDateString(),
            ]]))
            ->assertSessionHasErrors('item.0.tanggal_kadaluarsa');

        $this->assertSame(0, Pembelian::count());
        $this->assertSame(0, $obat->fresh()->stok);
    }

    public function test_obat_apotek_lain_tidak_bisa_dimasukkan(): void
    {
        $obatB = $this->obat('Apotek B');

        $this->actingAs($this->admin('Apotek A'))
            ->post(route('pembelian.store'), $this->faktur([[
                'obat_id' => $obatB->id, 'satuan_input' => 'box', 'jumlah' => 1, 'harga_beli' => 1000,
                'no_batch' => 'LAIN', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            ]]))
            ->assertSessionHasErrors('item');

        $this->assertSame(0, Pembelian::count());
        $this->assertSame(0, $obatB->fresh()->stok);
        $this->assertSame(0, BatchObat::count());
    }

    public function test_faktur_hanya_terlihat_oleh_apoteknya(): void
    {
        $adminA = $this->admin('Apotek A');
        $obat = $this->obat('Apotek A', 10);

        $this->actingAs($adminA)->post(route('pembelian.store'), $this->faktur([[
            'obat_id' => $obat->id, 'satuan_input' => 'box', 'jumlah' => 1, 'harga_beli' => 1000,
            'no_batch' => 'A-01', 'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
        ], ], ['no_faktur' => 'FK-MILIK-A']));

        $faktur = Pembelian::firstOrFail();

        $this->actingAs($adminA)->get(route('pembelian.index'))->assertOk()->assertSee('FK-MILIK-A');
        $this->actingAs($adminA)->get(route('pembelian.show', $faktur))->assertOk();

        $adminB = $this->admin('Apotek B');
        $this->actingAs($adminB)->get(route('pembelian.index'))->assertOk()->assertDontSee('FK-MILIK-A');
        $this->actingAs($adminB)->get(route('pembelian.show', $faktur))->assertNotFound();
    }

    public function test_kasir_tidak_dapat_mengakses_barang_masuk(): void
    {
        $kasir = User::factory()->create(['apotek' => 'Apotek A'])->assignRole('kasir_apotek_a');

        $this->actingAs($kasir)->get(route('pembelian.index'))->assertForbidden();
        $this->actingAs($kasir)->get(route('pembelian.create'))->assertForbidden();
    }
}
