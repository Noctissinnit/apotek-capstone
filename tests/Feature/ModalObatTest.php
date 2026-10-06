<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Obat;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tambah, ubah, dan lihat obat lewat jendela tumpang.
 * Halaman penuh tetap dipertahankan sebagai cadangan.
 */
class ModalObatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['apotek' => 'Apotek A'])->assignRole('admin_apotek_a');
    }

    private function obat(): Obat
    {
        return Obat::create([
            'kode_obat' => 'OBT-MODAL',
            'nama_obat' => 'Obat Modal',
            'apotek' => 'Apotek A',
            'satuan' => 'Strip',
            'isi_per_box' => 10,
            'harga_jual' => 5000,
            'stok' => 0,
            'stok_minimum' => 1,
        ]);
    }

    /** @return array<string, mixed> */
    private function dataObat(array $tambahan = []): array
    {
        return array_merge([
            'kode_obat' => 'OBT-BARU',
            'nama_obat' => 'Obat Baru',
            'kategori_id' => Kategori::firstOrCreate(['nama_kategori' => 'Obat Bebas'])->id_kategori,
            'satuan' => 'Strip',
            'isi_per_box' => 10,
            'harga_beli' => 1000,
            'harga_jual' => 2000,
            'stok' => 5,
            'stok_minimum' => 1,
        ], $tambahan);
    }

    public function test_permintaan_modal_membalas_potongan_bukan_halaman_penuh(): void
    {
        $response = $this->actingAs($this->admin())
            ->withHeader('HX-Request', 'true')
            ->get(route('obat.create'));

        $response->assertOk()
            ->assertSee('Tambah Obat')
            ->assertDontSee('<!DOCTYPE html>', false)
            ->assertDontSee('id="sidebar"', false);
    }

    public function test_halaman_penuh_tetap_bisa_dibuka_tanpa_modal(): void
    {
        $admin = $this->admin();
        $obat = $this->obat();

        foreach ([route('obat.create'), route('obat.show', $obat), route('obat.edit', $obat)] as $alamat) {
            $this->actingAs($admin)
                ->get($alamat)
                ->assertOk()
                ->assertSee('<!DOCTYPE html>', false)
                ->assertSee('id="sidebar"', false);
        }
    }

    public function test_detail_obat_dalam_modal_menampilkan_batch(): void
    {
        $obat = $this->obat();
        $obat->batch()->create([
            'no_batch' => 'BT-MODAL-01',
            'tanggal_kadaluarsa' => now()->addDays(20)->toDateString(),
            'stok' => 15,
        ]);

        $this->actingAs($this->admin())
            ->withHeader('HX-Request', 'true')
            ->get(route('obat.show', $obat))
            ->assertOk()
            ->assertSeeText('BT-MODAL-01')
            ->assertSeeText('Sisa 20 hari')
            ->assertSeeText('Siap Dijual');
    }

    public function test_simpan_dari_modal_memberi_tanda_agar_modal_ditutup(): void
    {
        $this->actingAs($this->admin())
            ->withHeader('HX-Request', 'true')
            ->post(route('obat.store'), $this->dataObat())
            ->assertRedirect(route('obat.index'))
            ->assertHeader('X-Tutup-Modal', '1');

        $this->assertDatabaseHas('obat', ['kode_obat' => 'OBT-BARU']);
    }

    public function test_simpan_tanpa_modal_tidak_memberi_tanda_itu(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('obat.store'), $this->dataObat());

        $response->assertRedirect(route('obat.index'));
        $this->assertNull($response->headers->get('X-Tutup-Modal'));
    }

    public function test_isian_salah_dari_modal_tidak_menutup_modal(): void
    {
        $response = $this->actingAs($this->admin())
            ->withHeader('HX-Request', 'true')
            ->post(route('obat.store'), $this->dataObat(['nama_obat' => '']));

        $response->assertSessionHasErrors('nama_obat');
        $this->assertNull($response->headers->get('X-Tutup-Modal'), 'modal harus tetap terbuka agar pesan error terlihat');
        $this->assertSame(0, Obat::count());
    }

    public function test_obat_apotek_lain_tidak_bisa_dibuka_lewat_modal(): void
    {
        $obatB = Obat::create([
            'kode_obat' => 'OBT-B',
            'nama_obat' => 'Obat Apotek B',
            'apotek' => 'Apotek B',
            'satuan' => 'Strip',
            'isi_per_box' => 10,
            'harga_jual' => 5000,
            'stok' => 0,
            'stok_minimum' => 1,
        ]);

        $this->actingAs($this->admin())
            ->withHeader('HX-Request', 'true')
            ->get(route('obat.show', $obatB))
            ->assertNotFound();
    }
}
