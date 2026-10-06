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

    public function test_simpan_dari_modal_tidak_boleh_dijawab_dengan_pengalihan(): void
    {
        $response = $this->actingAs($this->admin())
            ->withHeader('HX-Request', 'true')
            ->post(route('obat.store'), $this->dataObat());

        // pengalihan akan diikuti htmx, lalu seluruh halaman masuk ke dalam jendela
        $response->assertNoContent()->assertHeader('HX-Refresh', 'true');
        $this->assertFalse($response->isRedirect(), 'balasan untuk jendela tidak boleh berupa pengalihan');

        $this->assertDatabaseHas('obat', ['kode_obat' => 'OBT-BARU']);
        $this->assertSame('Data obat berhasil ditambahkan.', session('success'));
    }

    public function test_simpan_tanpa_modal_tetap_mengalihkan_seperti_biasa(): void
    {
        $this->actingAs($this->admin())
            ->post(route('obat.store'), $this->dataObat())
            ->assertRedirect(route('obat.index'))
            ->assertSessionHas('success');
    }

    public function test_isian_salah_dari_modal_dibalas_formnya_lagi_bukan_halaman_penuh(): void
    {
        $response = $this->actingAs($this->admin())
            ->withHeader('HX-Request', 'true')
            ->post(route('obat.store'), $this->dataObat(['nama_obat' => '', 'kode_obat' => 'OBT-DIKETIK']));

        $response->assertStatus(422)
            ->assertDontSee('<!DOCTYPE html>', false)
            ->assertDontSee('id="sidebar"', false)
            ->assertSeeText('Tambah Obat')
            ->assertSee('value="OBT-DIKETIK"', false)    // isian yang sudah diketik tidak hilang
            ->assertSee(trans('validation.required', ['attribute' => 'nama obat']));

        $this->assertSame(0, Obat::count());
    }

    public function test_isian_salah_tanpa_modal_tetap_kembali_ke_halaman_sebelumnya(): void
    {
        $this->actingAs($this->admin())
            ->from(route('obat.create'))
            ->post(route('obat.store'), $this->dataObat(['nama_obat' => '']))
            ->assertRedirect(route('obat.create'))
            ->assertSessionHasErrors('nama_obat');
    }

    public function test_ubah_dari_modal_juga_tidak_mengalihkan(): void
    {
        $obat = $this->obat();

        $this->actingAs($this->admin())
            ->withHeader('HX-Request', 'true')
            ->put(route('obat.update', $obat), $this->dataObat([
                'kode_obat' => $obat->kode_obat,
                'nama_obat' => 'Nama Diperbarui',
            ]))
            ->assertNoContent()
            ->assertHeader('HX-Refresh', 'true');

        $this->assertSame('Nama Diperbarui', $obat->fresh()->nama_obat);
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
