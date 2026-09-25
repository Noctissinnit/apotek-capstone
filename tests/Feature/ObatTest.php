<?php

namespace Tests\Feature;

use App\Models\Obat;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_dapat_menjalankan_crud_obat(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $data = [
            'kode_obat' => 'OBT999',
            'nama_obat' => 'Obat Test',
            'kategori' => 'Vitamin',
            'satuan' => 'Botol',
            'harga_beli' => 10000,
            'harga_jual' => 15000,
            'stok' => 20,
            'stok_minimum' => 5,
            'tanggal_kadaluarsa' => '2028-12-31',
            'keterangan' => 'Data untuk pengujian',
        ];

        $this->actingAs($admin)->get(route('obat.create'))->assertOk();
        $this->actingAs($admin)->post(route('obat.store'), $data)->assertRedirect(route('obat.index'));

        $obat = Obat::where('kode_obat', 'OBT999')->firstOrFail();
        $this->assertDatabaseHas('obat', ['kode_obat' => 'OBT999', 'nama_obat' => 'Obat Test']);
        $this->actingAs($admin)->get(route('obat.show', $obat))->assertOk()->assertSee('Obat Test');

        $this->actingAs($admin)->put(route('obat.update', $obat), ['nama_obat' => 'Obat Diperbarui'] + $data)
            ->assertRedirect(route('obat.index'));
        $this->assertDatabaseHas('obat', ['id' => $obat->id, 'nama_obat' => 'Obat Diperbarui']);

        $this->actingAs($admin)->delete(route('obat.destroy', $obat))->assertRedirect(route('obat.index'));
        $this->assertSoftDeleted('obat', ['id' => $obat->id]);
    }

    public function test_kasir_dapat_melihat_tetapi_tidak_dapat_mengelola_obat(): void
    {
        $kasir = User::factory()->create()->assignRole('kasir');
        $obat = Obat::create([
            'kode_obat' => 'OBT001',
            'nama_obat' => 'Paracetamol',
            'satuan' => 'Strip',
        ]);

        $this->actingAs($kasir)->get(route('obat.index'))->assertOk();
        $this->actingAs($kasir)->get(route('obat.show', $obat))->assertOk();
        $this->actingAs($kasir)->get(route('obat.create'))->assertForbidden();
        $this->actingAs($kasir)->delete(route('obat.destroy', $obat))->assertForbidden();
    }

    public function test_kode_obat_tidak_boleh_duplikat(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        Obat::create(['kode_obat' => 'OBT001', 'nama_obat' => 'Obat Lama', 'satuan' => 'Strip']);

        $this->actingAs($admin)->from(route('obat.create'))->post(route('obat.store'), [
            'kode_obat' => 'OBT001',
            'nama_obat' => 'Obat Baru',
            'satuan' => 'Strip',
            'harga_beli' => 1000,
            'harga_jual' => 1500,
            'stok' => 1,
            'stok_minimum' => 1,
        ])->assertRedirect(route('obat.create'))->assertSessionHasErrors('kode_obat');
    }

    public function test_data_obat_dapat_diurutkan_naik_dan_turun_berdasarkan_semua_kolom(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        Obat::create(['kode_obat' => 'C03', 'nama_obat' => 'Zinc', 'kategori' => 'Vitamin', 'satuan' => 'Box', 'harga_jual' => 30000, 'stok' => 5]);
        Obat::create(['kode_obat' => 'A01', 'nama_obat' => 'Alpha', 'kategori' => 'Obat Bebas', 'satuan' => 'Botol', 'harga_jual' => 5000, 'stok' => 20]);
        Obat::create(['kode_obat' => 'B02', 'nama_obat' => 'Beta', 'kategori' => 'Obat Keras', 'satuan' => 'Strip', 'harga_jual' => 15000, 'stok' => 10]);

        $sortCases = [
            'kode' => ['A01', 'B02', 'C03'],
            'nama' => ['Alpha', 'Beta', 'Zinc'],
            'stok' => ['Zinc', 'Beta', 'Alpha'],
            'kategori' => ['Alpha', 'Beta', 'Zinc'],
            'satuan' => ['Alpha', 'Zinc', 'Beta'],
            'harga_jual' => ['Alpha', 'Beta', 'Zinc'],
        ];

        foreach ($sortCases as $column => $ascendingOrder) {
            $this->actingAs($admin)
                ->get(route('obat.index', ['sort' => $column, 'direction' => 'asc']))
                ->assertOk()
                ->assertSeeInOrder($ascendingOrder);

            $this->actingAs($admin)
                ->get(route('obat.index', ['sort' => $column, 'direction' => 'desc']))
                ->assertOk()
                ->assertSeeInOrder(array_reverse($ascendingOrder));
        }
    }

    public function test_data_obat_ditampilkan_sepuluh_data_per_halaman(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        foreach (range(1, 11) as $number) {
            Obat::create([
                'kode_obat' => sprintf('OBT%03d', $number),
                'nama_obat' => 'Obat ' . $number,
                'satuan' => 'Strip',
                'stok' => $number,
            ]);
        }

        $pageOne = $this->actingAs($admin)
            ->get(route('obat.index', ['sort' => 'kode', 'direction' => 'asc']))
            ->assertOk()
            ->assertSee('11 jenis obat')
            ->assertSee('OBT010')
            ->assertDontSee('OBT011');

        $pageOne->assertSee('page=2');

        $this->actingAs($admin)
            ->get(route('obat.index', ['sort' => 'kode', 'direction' => 'asc', 'page' => 2]))
            ->assertOk()
            ->assertSee('OBT011')
            ->assertDontSee('OBT001');
    }
}
