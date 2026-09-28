<?php

namespace Tests\Feature;

use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirTransaksiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function kasir(): User
    {
        return User::factory()->create()->assignRole('kasir');
    }

    private function obat(int $stok = 5): Obat
    {
        return Obat::create([
            'kode_obat' => 'OBT-TEST',
            'nama_obat' => 'Obat Uji',
            'satuan' => 'Strip',
            'harga_beli' => 4000,
            'harga_jual' => 6500,
            'stok' => $stok,
            'stok_minimum' => 1,
        ]);
    }

    public function test_checkout_mencatat_penjualan_dan_mengurangi_stok(): void
    {
        $kasir = $this->kasir();
        $obat = $this->obat();

        $this->actingAs($kasir)
            ->post(route('kasir.keranjang.add'), ['obat_id' => $obat->id, 'jumlah' => 2])
            ->assertSessionHasNoErrors();

        $this->post(route('kasir.checkout'))->assertRedirect(route('kasir.keranjang'));

        $this->assertSame(3, $obat->fresh()->stok);
        $this->assertDatabaseHas('penjualan', [
            'user_id' => $kasir->id,
            'total' => '13000.00',
        ]);
        $this->assertDatabaseHas('detail_penjualan', [
            'obat_id' => $obat->id,
            'jumlah' => 2,
            'subtotal' => '13000.00',
        ]);
        $this->assertSame(1, Penjualan::count());
        $this->assertFalse(session()->has('kasir.cart'));
    }

    public function test_checkout_ditolak_jika_stok_tidak_mencukupi_dan_tidak_mengubah_stok(): void
    {
        $kasir = $this->kasir();
        $obat = $this->obat(1);

        $this->actingAs($kasir)
            ->withSession(['kasir.cart' => [$obat->id => 2]])
            ->post(route('kasir.checkout'))
            ->assertSessionHasErrors('keranjang');

        $this->assertSame(1, $obat->fresh()->stok);
        $this->assertSame(0, Penjualan::count());
    }

    public function test_keranjang_bisa_dibuka_dan_item_ditambahkan(): void
    {
        $obat = $this->obat();

        $this->actingAs($this->kasir())
            ->get(route('kasir.keranjang'))
            ->assertOk()
            ->assertSee('Keranjang masih kosong');

        $this->post(route('kasir.keranjang.add'), ['obat_id' => $obat->id])
            ->assertRedirect();

        $this->get(route('kasir.keranjang'))
            ->assertOk()
            ->assertSee('Obat Uji')
            ->assertSee('Rp 6.500');
    }

    public function test_sisa_stok_di_daftar_obat_dikurangi_jumlah_yang_sudah_di_keranjang(): void
    {
        $obat = $this->obat(5);
        $kasir = $this->kasir();

        $this->actingAs($kasir)
            ->post(route('kasir.keranjang.add'), ['obat_id' => $obat->id, 'jumlah' => 2])
            ->assertRedirect();

        $this->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSeeText('Di keranjang 2')
            ->assertSeeText('Tersedia 3 Strip');

        $this->assertSame(5, $obat->fresh()->stok);
    }
}