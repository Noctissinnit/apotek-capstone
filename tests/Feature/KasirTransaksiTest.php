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
        return User::factory()->create()->assignRole('kasir_apotek_a');
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

        $this->post(route('kasir.checkout'), ['metode_pembayaran' => 'Tunai'])
            ->assertRedirect(route('kasir.keranjang'));

        $this->assertSame(3, $obat->fresh()->stok);
        $this->assertDatabaseHas('penjualan', [
            'user_id' => $kasir->id,
            'total' => '13000.00',
            'metode_pembayaran' => 'Tunai',
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
            ->post(route('kasir.checkout'), ['metode_pembayaran' => 'Tunai'])
            ->assertSessionHasErrors('keranjang');

        $this->assertSame(1, $obat->fresh()->stok);
        $this->assertSame(0, Penjualan::count());
    }

    public function test_kasir_dapat_memilih_qris_atau_kartu_debit(): void
    {
        foreach (['QRIS', 'Kartu Debit'] as $metode) {
            $kasir = $this->kasir();
            $obat = Obat::create([
                'kode_obat' => 'OBT-'.str($metode)->slug(),
                'nama_obat' => 'Obat '.$metode,
                'satuan' => 'Strip',
                'harga_jual' => 6500,
                'stok' => 5,
                'stok_minimum' => 1,
            ]);

            $this->actingAs($kasir)
                ->withSession(['kasir.cart' => [$obat->id => 1]])
                ->post(route('kasir.checkout'), ['metode_pembayaran' => $metode])
                ->assertSessionHasNoErrors();

            $this->assertDatabaseHas('penjualan', [
                'user_id' => $kasir->id,
                'metode_pembayaran' => $metode,
            ]);
        }
    }

    public function test_notifikasi_pembayaran_berhasil_dikirim_ke_halaman(): void
    {
        $kasir = $this->kasir();
        $obat = $this->obat();

        $response = $this->actingAs($kasir)
            ->withSession(['kasir.cart' => [$obat->id => 2]])
            ->post(route('kasir.checkout'), ['metode_pembayaran' => 'QRIS']);

        $notifikasi = session('transaksi_sukses');

        $this->assertIsArray($notifikasi);
        $this->assertSame('QRIS', $notifikasi['metode_pembayaran']);
        $this->assertSame('Rp 13.000', $notifikasi['total']);
        $this->assertSame(2, $notifikasi['jumlah_item']);
        $this->assertStringContainsString('/pdf', $notifikasi['url_struk']);

        // halaman tujuan memuat data notifikasi untuk ditampilkan SweetAlert
        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('id="transaksi-sukses"', false)
            ->assertSee($notifikasi['no_faktur']);
    }

    public function test_cara_pembayaran_di_luar_daftar_ditolak(): void
    {
        $kasir = $this->kasir();
        $obat = $this->obat();

        $this->actingAs($kasir)
            ->withSession(['kasir.cart' => [$obat->id => 1]])
            ->post(route('kasir.checkout'), ['metode_pembayaran' => 'Bitcoin'])
            ->assertSessionHasErrors('metode_pembayaran');

        $this->assertSame(0, Penjualan::count());
        $this->assertSame(5, $obat->fresh()->stok);
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