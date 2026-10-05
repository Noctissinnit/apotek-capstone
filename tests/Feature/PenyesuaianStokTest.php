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
 * CR-01-D: retur ke supplier dan pemusnahan stok kadaluarsa.
 */
class PenyesuaianStokTest extends TestCase
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

    private function batchKadaluarsa(string $apotek = 'Apotek A', int $stok = 30, float $modal = 400): BatchObat
    {
        $obat = Obat::create([
            'kode_obat' => 'OBT-'.fake()->unique()->bothify('??##'),
            'nama_obat' => 'Paracetamol',
            'apotek' => $apotek,
            'satuan' => 'Tablet',
            'isi_per_box' => 100,
            'harga_jual' => 600,
            'stok' => $stok,
            'stok_minimum' => 5,
        ]);

        return $obat->batch()->create([
            'no_batch' => 'BT-'.fake()->unique()->bothify('??##'),
            'tanggal_kadaluarsa' => now()->subDays(10)->toDateString(),
            'stok' => $stok,
            'modal_per_satuan' => $modal,
        ]);
    }

    /** @return array<string, mixed> */
    private function dokumen(BatchObat $batch, array $tambahan = [], int $jumlah = null): array
    {
        return array_merge([
            'no_dokumen' => 'DOK-'.fake()->unique()->numerify('####'),
            'jenis' => PenyesuaianStok::JENIS_MUSNAH,
            'tanggal' => now()->toDateString(),
            'item' => [[
                'pilih' => 1,
                'batch_obat_id' => $batch->id,
                'jumlah' => $jumlah ?? $batch->stok,
                'alasan' => 'kadaluarsa',
            ]],
        ], $tambahan);
    }

    public function test_pemusnahan_mengurangi_stok_batch_dan_master(): void
    {
        $batch = $this->batchKadaluarsa(stok: 30, modal: 400);
        $obat = $batch->obat;

        $this->actingAs($this->admin())
            ->post(route('penyesuaian.store'), $this->dokumen($batch, ['keterangan' => 'Dibakar, disaksikan apoteker']))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $batch->fresh()->stok);
        $this->assertSame(0, $obat->fresh()->stok);

        $dokumen = PenyesuaianStok::firstOrFail();
        $this->assertSame(PenyesuaianStok::JENIS_MUSNAH, $dokumen->jenis);
        $this->assertSame('12000.00', $dokumen->total_nilai);   // 30 x 400
        $this->assertSame('Apotek A', $dokumen->apotek);
    }

    public function test_retur_wajib_menyebutkan_supplier(): void
    {
        $batch = $this->batchKadaluarsa();

        $this->actingAs($this->admin())
            ->post(route('penyesuaian.store'), $this->dokumen($batch, ['jenis' => PenyesuaianStok::JENIS_RETUR]))
            ->assertSessionHasErrors('supplier_id');

        $this->assertSame(30, $batch->fresh()->stok, 'stok tidak boleh berkurang saat gagal');
        $this->assertSame(0, PenyesuaianStok::count());
    }

    public function test_retur_tersimpan_beserta_suppliernya(): void
    {
        $batch = $this->batchKadaluarsa(stok: 20, modal: 500);
        $supplier = Supplier::create(['kode_supplier' => 'SUP-RTR', 'nama_supplier' => 'PT Uji Retur']);

        $this->actingAs($this->admin())
            ->post(route('penyesuaian.store'), $this->dokumen($batch, [
                'jenis' => PenyesuaianStok::JENIS_RETUR,
                'supplier_id' => $supplier->id,
            ]))
            ->assertSessionHasNoErrors();

        $dokumen = PenyesuaianStok::firstOrFail();

        $this->assertTrue($dokumen->isRetur());
        $this->assertSame($supplier->id, $dokumen->supplier_id);
        $this->assertSame('10000.00', $dokumen->total_nilai);
        $this->assertSame(0, $batch->fresh()->stok);
    }

    public function test_bisa_mengeluarkan_sebagian_saja(): void
    {
        $batch = $this->batchKadaluarsa(stok: 30, modal: 400);

        $this->actingAs($this->admin())
            ->post(route('penyesuaian.store'), $this->dokumen($batch, jumlah: 10))
            ->assertSessionHasNoErrors();

        $this->assertSame(20, $batch->fresh()->stok);
        $this->assertSame(20, $batch->obat->fresh()->stok);
        $this->assertSame('4000.00', PenyesuaianStok::firstOrFail()->total_nilai);
    }

    public function test_jumlah_melebihi_stok_ditolak(): void
    {
        $batch = $this->batchKadaluarsa(stok: 30);

        $this->actingAs($this->admin())
            ->post(route('penyesuaian.store'), $this->dokumen($batch, jumlah: 50))
            ->assertSessionHasErrors('item');

        $this->assertSame(30, $batch->fresh()->stok);
        $this->assertSame(0, PenyesuaianStok::count());
    }

    public function test_tanpa_batch_terpilih_ditolak(): void
    {
        $batch = $this->batchKadaluarsa();

        $kiriman = $this->dokumen($batch);
        $kiriman['item'][0]['pilih'] = null;

        $this->actingAs($this->admin())
            ->post(route('penyesuaian.store'), $kiriman)
            ->assertSessionHasErrors('item');

        $this->assertSame(0, PenyesuaianStok::count());
    }

    public function test_stok_yang_sudah_dimusnahkan_tidak_bisa_dijual(): void
    {
        $batch = $this->batchKadaluarsa(stok: 30);

        $this->actingAs($this->admin())->post(route('penyesuaian.store'), $this->dokumen($batch));

        $this->assertSame(0, $batch->obat->fresh()->stokSiapDijual());
    }

    public function test_dokumen_dapat_diunduh_sebagai_pdf(): void
    {
        $batch = $this->batchKadaluarsa();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('penyesuaian.store'), $this->dokumen($batch));
        $dokumen = PenyesuaianStok::firstOrFail();

        $response = $this->actingAs($admin)->get(route('penyesuaian.pdf', $dokumen));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('berita-acara-pemusnahan', $response->headers->get('content-disposition'));
    }

    public function test_dokumen_apotek_lain_tidak_bisa_dilihat(): void
    {
        $batch = $this->batchKadaluarsa('Apotek A');
        $adminA = $this->admin('Apotek A');

        $this->actingAs($adminA)->post(route('penyesuaian.store'), $this->dokumen($batch, ['no_dokumen' => 'DOK-MILIK-A']));
        $dokumen = PenyesuaianStok::firstOrFail();

        // pesan sukses berisi nomor dokumen masih menempel di sesi test,
        // di aplikasi nyata tiap admin punya sesi browser sendiri
        $this->flushSession();

        $adminB = $this->admin('Apotek B');
        $this->actingAs($adminB)->get(route('penyesuaian.index'))->assertOk()->assertDontSee('DOK-MILIK-A');
        $this->actingAs($adminB)->get(route('penyesuaian.show', $dokumen))->assertNotFound();
        $this->actingAs($adminB)->get(route('penyesuaian.pdf', $dokumen))->assertNotFound();
    }

    public function test_batch_apotek_lain_tidak_bisa_dikeluarkan(): void
    {
        $batchB = $this->batchKadaluarsa('Apotek B');

        $this->actingAs($this->admin('Apotek A'))
            ->post(route('penyesuaian.store'), $this->dokumen($batchB))
            ->assertSessionHasErrors('item');

        $this->assertSame(30, $batchB->fresh()->stok);
        $this->assertSame(0, PenyesuaianStok::count());
    }

    public function test_nilai_retur_dan_pemusnahan_dilaporkan_terpisah(): void
    {
        $admin = $this->admin();
        $supplier = Supplier::create(['kode_supplier' => 'SUP-X', 'nama_supplier' => 'PT X']);

        $this->actingAs($admin)->post(route('penyesuaian.store'), $this->dokumen($this->batchKadaluarsa(stok: 10, modal: 500)));
        $this->actingAs($admin)->post(route('penyesuaian.store'), $this->dokumen($this->batchKadaluarsa(stok: 20, modal: 300), [
            'jenis' => PenyesuaianStok::JENIS_RETUR,
            'supplier_id' => $supplier->id,
        ]));

        $this->actingAs($admin)
            ->get(route('penyesuaian.index'))
            ->assertOk()
            ->assertSeeText('Rp 5.000')    // nilai pemusnahan
            ->assertSeeText('Rp 6.000');   // nilai retur
    }

    public function test_kasir_tidak_dapat_mengakses_retur_dan_pemusnahan(): void
    {
        $kasir = User::factory()->create(['apotek' => 'Apotek A'])->assignRole('kasir_apotek_a');

        $this->actingAs($kasir)->get(route('penyesuaian.index'))->assertForbidden();
        $this->actingAs($kasir)->get(route('penyesuaian.create'))->assertForbidden();
    }
}
