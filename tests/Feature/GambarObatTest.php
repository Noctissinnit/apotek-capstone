<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Obat;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GambarObatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('unggahan');
    }

    private function admin(): User
    {
        return User::factory()->create(['apotek' => 'Apotek A'])->assignRole('admin_apotek_a');
    }

    /** @return array<string, mixed> */
    private function dataObat(array $tambahan = []): array
    {
        return array_merge([
            'kode_obat' => 'OBT-GBR',
            'nama_obat' => 'Obat Bergambar',
            'kategori_id' => Kategori::firstOrCreate(['nama_kategori' => 'Obat Bebas'])->id_kategori,
            'satuan' => 'Strip',
            'harga_beli' => 1000,
            'harga_jual' => 2000,
            'stok' => 10,
            'stok_minimum' => 2,
        ], $tambahan);
    }

    public function test_admin_dapat_mengunggah_gambar_saat_menambah_obat(): void
    {
        $this->actingAs($this->admin())
            ->post(route('obat.store'), $this->dataObat([
                'gambar' => UploadedFile::fake()->image('paracetamol.jpg'),
            ]))
            ->assertRedirect(route('obat.index'));

        $obat = Obat::where('kode_obat', 'OBT-GBR')->firstOrFail();

        $this->assertNotNull($obat->gambar);
        Storage::disk('unggahan')->assertExists($obat->gambar);
        $this->assertStringContainsString('uploads/'.$obat->gambar, $obat->urlGambar());
    }

    public function test_obat_tanpa_gambar_tetap_bisa_disimpan(): void
    {
        $this->actingAs($this->admin())
            ->post(route('obat.store'), $this->dataObat())
            ->assertRedirect(route('obat.index'));

        $this->assertNull(Obat::where('kode_obat', 'OBT-GBR')->firstOrFail()->gambar);
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('obat.store'), $this->dataObat([
                'gambar' => UploadedFile::fake()->create('daftar-harga.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('gambar');

        $this->assertSame(0, Obat::count());
    }

    public function test_gambar_lebih_dari_dua_mega_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('obat.store'), $this->dataObat([
                'gambar' => UploadedFile::fake()->image('besar.jpg')->size(2500),
            ]))
            ->assertSessionHasErrors('gambar');
    }

    public function test_mengganti_gambar_menghapus_berkas_lama(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('obat.store'), $this->dataObat([
            'gambar' => UploadedFile::fake()->image('lama.jpg'),
        ]));

        $obat = Obat::where('kode_obat', 'OBT-GBR')->firstOrFail();
        $gambarLama = $obat->gambar;

        $this->actingAs($admin)->put(route('obat.update', $obat), $this->dataObat([
            'gambar' => UploadedFile::fake()->image('baru.jpg'),
        ]));

        $obat->refresh();

        $this->assertNotSame($gambarLama, $obat->gambar);
        Storage::disk('unggahan')->assertExists($obat->gambar);
        Storage::disk('unggahan')->assertMissing($gambarLama);
    }

    public function test_gambar_dapat_dihapus_tanpa_mengganti(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('obat.store'), $this->dataObat([
            'gambar' => UploadedFile::fake()->image('lama.jpg'),
        ]));

        $obat = Obat::where('kode_obat', 'OBT-GBR')->firstOrFail();
        $gambarLama = $obat->gambar;

        $this->actingAs($admin)->put(route('obat.update', $obat), $this->dataObat([
            'hapus_gambar' => 1,
        ]));

        $this->assertNull($obat->refresh()->gambar);
        Storage::disk('unggahan')->assertMissing($gambarLama);
    }

    public function test_mengubah_data_lain_tidak_menghilangkan_gambar(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('obat.store'), $this->dataObat([
            'gambar' => UploadedFile::fake()->image('tetap.jpg'),
        ]));

        $obat = Obat::where('kode_obat', 'OBT-GBR')->firstOrFail();
        $gambar = $obat->gambar;

        $this->actingAs($admin)->put(route('obat.update', $obat), $this->dataObat([
            'nama_obat' => 'Nama Diperbarui',
        ]));

        $obat->refresh();

        $this->assertSame('Nama Diperbarui', $obat->nama_obat);
        $this->assertSame($gambar, $obat->gambar);
        Storage::disk('unggahan')->assertExists($gambar);
    }

    public function test_gambar_tampil_di_daftar_dan_detail_obat(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('obat.store'), $this->dataObat([
            'gambar' => UploadedFile::fake()->image('tampil.jpg'),
        ]));

        $obat = Obat::where('kode_obat', 'OBT-GBR')->firstOrFail();

        $this->actingAs($admin)->get(route('obat.index'))->assertOk()->assertSee($obat->gambar);
        $this->actingAs($admin)->get(route('obat.show', $obat))->assertOk()->assertSee($obat->gambar);
    }
}
