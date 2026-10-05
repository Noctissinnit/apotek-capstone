<?php

namespace Tests\Feature;

use App\Models\BatchObat;
use App\Models\Kategori;
use App\Models\Obat;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CR-01-A: pondasi batch dan isi per box.
 */
class BatchObatTest extends TestCase
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

    private function obat(string $apotek = 'Apotek A', string $kode = 'OBT-X'): Obat
    {
        return Obat::create([
            'kode_obat' => $kode,
            'nama_obat' => 'Obat '.$kode,
            'apotek' => $apotek,
            'satuan' => 'Tablet',
            'isi_per_box' => 100,
            'harga_jual' => 600,
            'stok' => 0,
            'stok_minimum' => 10,
        ]);
    }

    private function batch(Obat $obat, array $isi = []): BatchObat
    {
        return BatchObat::create(array_merge([
            'obat_id' => $obat->id,
            'no_batch' => 'BT-'.fake()->unique()->bothify('??##'),
            'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            'stok' => 50,
            'modal_per_satuan' => 405,
        ], $isi));
    }

    public function test_admin_mengisi_isi_per_box_saat_menambah_obat(): void
    {
        $this->actingAs($this->admin())
            ->post(route('obat.store'), [
                'kode_obat' => 'OBT-BOX',
                'nama_obat' => 'Paracetamol',
                'kategori_id' => Kategori::firstOrCreate(['nama_kategori' => 'Obat Bebas'])->id_kategori,
                'satuan' => 'Tablet',
                'isi_per_box' => 100,
                'harga_beli' => 0,
                'harga_jual' => 600,
                'stok' => 0,
                'stok_minimum' => 10,
            ])
            ->assertRedirect(route('obat.index'));

        $this->assertSame(100, Obat::where('kode_obat', 'OBT-BOX')->firstOrFail()->isi_per_box);
    }

    public function test_isi_per_box_tidak_boleh_nol_atau_kosong(): void
    {
        $admin = $this->admin();
        $dasar = [
            'kode_obat' => 'OBT-NOL',
            'nama_obat' => 'Obat Nol',
            'satuan' => 'Tablet',
            'harga_beli' => 0,
            'harga_jual' => 600,
            'stok' => 0,
            'stok_minimum' => 10,
        ];

        $this->actingAs($admin)->post(route('obat.store'), $dasar)->assertSessionHasErrors('isi_per_box');
        $this->actingAs($admin)->post(route('obat.store'), $dasar + ['isi_per_box' => 0])->assertSessionHasErrors('isi_per_box');

        $this->assertSame(0, Obat::count());
    }

    public function test_satu_obat_dapat_memiliki_beberapa_batch(): void
    {
        $obat = $this->obat();
        $this->batch($obat, ['no_batch' => 'BT-A', 'stok' => 50]);
        $this->batch($obat, ['no_batch' => 'BT-B', 'stok' => 30]);

        $this->assertSame(2, $obat->batch()->count());
        $this->assertSame(80, $obat->stokDariBatch());
    }

    public function test_nomor_batch_tidak_boleh_kembar_pada_obat_yang_sama(): void
    {
        $obat = $this->obat();
        $this->batch($obat, ['no_batch' => 'BT-SAMA']);

        $this->expectException(QueryException::class);
        $this->batch($obat, ['no_batch' => 'BT-SAMA']);
    }

    public function test_nomor_batch_sama_boleh_dipakai_obat_berbeda(): void
    {
        $this->batch($this->obat(kode: 'OBT-1'), ['no_batch' => 'BT-SAMA']);
        $this->batch($this->obat(kode: 'OBT-2'), ['no_batch' => 'BT-SAMA']);

        $this->assertSame(2, BatchObat::where('no_batch', 'BT-SAMA')->count());
    }

    public function test_batch_siap_dijual_mengabaikan_yang_kadaluarsa_ditahan_dan_kosong(): void
    {
        $obat = $this->obat();

        $layak = $this->batch($obat, ['no_batch' => 'BT-OK', 'tanggal_kadaluarsa' => now()->addMonths(6)->toDateString()]);
        $this->batch($obat, ['no_batch' => 'BT-EXP', 'tanggal_kadaluarsa' => now()->subDay()->toDateString()]);
        $this->batch($obat, ['no_batch' => 'BT-TAHAN', 'status' => BatchObat::STATUS_DITAHAN]);
        $this->batch($obat, ['no_batch' => 'BT-HABIS', 'stok' => 0]);

        $siap = BatchObat::siapDijual()->get();

        $this->assertCount(1, $siap);
        $this->assertSame($layak->id, $siap->first()->id);
    }

    public function test_batch_diurutkan_dari_yang_paling_dekat_kadaluarsa(): void
    {
        $obat = $this->obat();
        $this->batch($obat, ['no_batch' => 'BT-JAUH', 'tanggal_kadaluarsa' => now()->addYears(2)->toDateString()]);
        $this->batch($obat, ['no_batch' => 'BT-DEKAT', 'tanggal_kadaluarsa' => now()->addDays(20)->toDateString()]);
        $this->batch($obat, ['no_batch' => 'BT-TENGAH', 'tanggal_kadaluarsa' => now()->addMonths(5)->toDateString()]);

        $this->assertSame(
            ['BT-DEKAT', 'BT-TENGAH', 'BT-JAUH'],
            BatchObat::siapDijual()->pluck('no_batch')->all()
        );

        $this->assertSame('BT-DEKAT', $obat->batchTerdekatKadaluarsa()->no_batch);
    }

    public function test_batch_kadaluarsa_terdata_untuk_ditindaklanjuti(): void
    {
        $obat = $this->obat();
        $lewat = $this->batch($obat, ['no_batch' => 'BT-LEWAT', 'tanggal_kadaluarsa' => now()->subDays(10)->toDateString()]);
        $this->batch($obat, ['no_batch' => 'BT-AMAN', 'tanggal_kadaluarsa' => now()->addMonths(3)->toDateString()]);

        $kadaluarsa = BatchObat::kadaluarsa()->get();

        $this->assertCount(1, $kadaluarsa);
        $this->assertSame($lewat->id, $kadaluarsa->first()->id);
        $this->assertTrue($lewat->sudahKadaluarsa());
        $this->assertFalse($lewat->bolehDijual());
        $this->assertSame(-10, $lewat->sisaHari());
    }

    public function test_batch_ikut_terpisah_antar_apotek(): void
    {
        $this->batch($this->obat('Apotek A', 'OBT-A'), ['no_batch' => 'BT-A']);
        $this->batch($this->obat('Apotek B', 'OBT-B'), ['no_batch' => 'BT-B']);

        $this->assertSame(['BT-A'], BatchObat::forUser($this->admin('Apotek A'))->pluck('no_batch')->all());
        $this->assertSame(['BT-B'], BatchObat::forUser($this->admin('Apotek B'))->pluck('no_batch')->all());
    }
}
