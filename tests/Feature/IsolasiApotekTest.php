<?php

namespace Tests\Feature;

use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menutup skenario TC-PHARM-05 dan TC-PHARM-06: data satu apotek
 * tidak boleh terlihat, terjual, atau terhitung oleh apotek lain.
 */
class IsolasiApotekTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function kasir(string $apotek): User
    {
        $role = $apotek === 'Apotek A' ? 'kasir_apotek_a' : 'kasir_apotek_b';

        return User::factory()->create(['apotek' => $apotek])->assignRole($role);
    }

    private function obat(string $apotek, string $kode, int $stok = 10): Obat
    {
        return Obat::create([
            'kode_obat' => $kode,
            'nama_obat' => 'Obat '.$apotek.' '.$kode,
            'apotek' => $apotek,
            'satuan' => 'Strip',
            'harga_jual' => 5000,
            'stok' => $stok,
            'stok_minimum' => 1,
        ]);
    }

    public function test_layar_kasir_hanya_menampilkan_obat_apoteknya(): void
    {
        $this->obat('Apotek A', 'A01');
        $this->obat('Apotek B', 'B01');

        $this->actingAs($this->kasir('Apotek A'))
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSee('Obat Apotek A A01')
            ->assertDontSee('Obat Apotek B B01');

        $this->actingAs($this->kasir('Apotek B'))
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSee('Obat Apotek B B01')
            ->assertDontSee('Obat Apotek A A01');
    }

    public function test_obat_apotek_lain_tidak_bisa_dimasukkan_ke_keranjang(): void
    {
        $obatB = $this->obat('Apotek B', 'B01');

        $this->actingAs($this->kasir('Apotek A'))
            ->post(route('kasir.keranjang.add'), ['obat_id' => $obatB->id])
            ->assertNotFound();

        $this->assertEmpty(session('kasir.cart', []));
    }

    public function test_checkout_menolak_obat_apotek_lain_dan_stoknya_tidak_berubah(): void
    {
        $obatB = $this->obat('Apotek B', 'B01');

        // keranjang sengaja diisi langsung, meniru sisa sesi atau data yang dipaksakan
        $this->actingAs($this->kasir('Apotek A'))
            ->withSession(['kasir.cart' => [$obatB->id => 2]])
            ->post(route('kasir.checkout'), ['metode_pembayaran' => 'Tunai'])
            ->assertSessionHasErrors('keranjang');

        $this->assertSame(10, $obatB->fresh()->stok);
        $this->assertSame(0, Penjualan::count());
    }

    public function test_riwayat_hanya_menampilkan_transaksi_apoteknya(): void
    {
        $kasirA = $this->kasir('Apotek A');
        $kasirB = $this->kasir('Apotek B');

        $this->belanja($kasirA, $this->obat('Apotek A', 'A01'));
        $this->belanja($kasirB, $this->obat('Apotek B', 'B01'));

        $fakturA = Penjualan::where('apotek', 'Apotek A')->firstOrFail()->no_faktur;
        $fakturB = Penjualan::where('apotek', 'Apotek B')->firstOrFail()->no_faktur;

        $this->actingAs($kasirA)
            ->get(route('kasir.riwayat'))
            ->assertOk()
            ->assertSee($fakturA)
            ->assertDontSee($fakturB);

        $this->actingAs($kasirB)
            ->get(route('kasir.riwayat'))
            ->assertOk()
            ->assertSee($fakturB)
            ->assertDontSee($fakturA);
    }

    public function test_struk_transaksi_apotek_lain_tidak_bisa_dibuka(): void
    {
        $kasirB = $this->kasir('Apotek B');
        $this->belanja($kasirB, $this->obat('Apotek B', 'B01'));
        $penjualanB = Penjualan::where('apotek', 'Apotek B')->firstOrFail();

        $this->actingAs($this->kasir('Apotek A'))
            ->get(route('kasir.riwayat.pdf', $penjualanB))
            ->assertNotFound();
    }

    public function test_total_penjualan_hari_ini_dihitung_per_apotek(): void
    {
        $kasirA = $this->kasir('Apotek A');
        $kasirB = $this->kasir('Apotek B');

        $this->belanja($kasirA, $this->obat('Apotek A', 'A01'), 2);   // 2 x 5.000
        $this->belanja($kasirB, $this->obat('Apotek B', 'B01'), 3);   // 3 x 5.000

        $this->actingAs($kasirA)
            ->get(route('kasir.riwayat'))
            ->assertSee('Rp 10.000')
            ->assertDontSee('Rp 15.000');
    }

    public function test_transaksi_mencatat_apotek_pembuatnya(): void
    {
        $kasirB = $this->kasir('Apotek B');
        $this->belanja($kasirB, $this->obat('Apotek B', 'B01'));

        $this->assertDatabaseHas('penjualan', [
            'user_id' => $kasirB->id,
            'apotek' => 'Apotek B',
        ]);
    }

    private function belanja(User $kasir, Obat $obat, int $jumlah = 1): void
    {
        $this->actingAs($kasir)
            ->withSession(['kasir.cart' => [$obat->id => $jumlah]])
            ->post(route('kasir.checkout'), ['metode_pembayaran' => 'Tunai'])
            ->assertSessionHasNoErrors();

        // Di aplikasi nyata tiap kasir punya sesi browser sendiri. Di test sesinya
        // dipakai bersama, jadi notifikasi transaksi sebelumnya perlu dibersihkan
        // agar tidak terbaca sebagai kebocoran data.
        $this->flushSession();
    }
}
