<?php

namespace Tests\Feature;

use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uang diterima dan kembalian untuk pembayaran tunai.
 */
class UangKembalianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function kasir(): User
    {
        return User::factory()->create(['apotek' => 'Apotek A'])->assignRole('kasir_apotek_a');
    }

    /** Obat seharga Rp 6.500 dengan stok siap jual. */
    private function obat(int $stok = 10): Obat
    {
        $obat = Obat::create([
            'kode_obat' => 'OBT-'.fake()->unique()->bothify('??##'),
            'nama_obat' => 'Obat Uji',
            'apotek' => 'Apotek A',
            'satuan' => 'Strip',
            'isi_per_box' => 10,
            'harga_jual' => 6500,
            'stok' => $stok,
            'stok_minimum' => 1,
        ]);

        $obat->batch()->create([
            'no_batch' => 'BT-'.fake()->unique()->bothify('??##'),
            'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            'stok' => $stok,
            'modal_per_satuan' => 4000,
        ]);

        return $obat;
    }

    private function bayar(User $kasir, Obat $obat, array $kiriman, int $jumlah = 2)
    {
        return $this->actingAs($kasir)
            ->withSession(['kasir.cart' => [$obat->id => $jumlah]])
            ->post(route('kasir.checkout'), $kiriman);
    }

    public function test_kembalian_dihitung_dari_uang_yang_diterima(): void
    {
        $obat = $this->obat();

        // 2 x 6.500 = 13.000, dibayar 20.000
        $this->bayar($this->kasir(), $obat, [
            'metode_pembayaran' => 'Tunai',
            'uang_diterima' => 20000,
        ])->assertSessionHasNoErrors();

        $penjualan = Penjualan::firstOrFail();

        $this->assertSame('13000.00', $penjualan->total);
        $this->assertSame('20000.00', $penjualan->uang_diterima);
        $this->assertSame('7000.00', $penjualan->kembalian);
    }

    public function test_uang_pas_menghasilkan_kembalian_nol(): void
    {
        $obat = $this->obat();

        $this->bayar($this->kasir(), $obat, [
            'metode_pembayaran' => 'Tunai',
            'uang_diterima' => 13000,
        ])->assertSessionHasNoErrors();

        $this->assertSame('0.00', Penjualan::firstOrFail()->kembalian);
    }

    public function test_tunai_tanpa_uang_diterima_ditolak(): void
    {
        $obat = $this->obat();

        $this->bayar($this->kasir(), $obat, ['metode_pembayaran' => 'Tunai'])
            ->assertSessionHasErrors('uang_diterima');

        $this->assertSame(0, Penjualan::count());
        $this->assertSame(10, $obat->fresh()->stok, 'stok tidak boleh berkurang saat gagal');
    }

    public function test_uang_kurang_dari_total_ditolak(): void
    {
        $obat = $this->obat();

        $this->bayar($this->kasir(), $obat, [
            'metode_pembayaran' => 'Tunai',
            'uang_diterima' => 10000,
        ])->assertSessionHasErrors('uang_diterima');

        $this->assertSame(0, Penjualan::count());
        $this->assertSame(10, $obat->fresh()->stok);
    }

    public function test_qris_dan_transfer_tidak_perlu_uang_diterima(): void
    {
        foreach (['QRIS', 'Transfer/Debit'] as $metode) {
            $obat = $this->obat();

            $this->bayar($this->kasir(), $obat, ['metode_pembayaran' => $metode])
                ->assertSessionHasNoErrors();

            $penjualan = Penjualan::where('metode_pembayaran', $metode)->firstOrFail();

            $this->assertNull($penjualan->uang_diterima);
            $this->assertNull($penjualan->kembalian);
        }
    }

    public function test_kolom_uang_diterima_tersedia_di_layar_kasir(): void
    {
        $obat = $this->obat();

        $this->actingAs($this->kasir())
            ->withSession(['kasir.cart' => [$obat->id => 2]])
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSee('data-bagian-tunai', false)
            ->assertSee('data-total="13000"', false)
            ->assertSeeText('Uang pas')
            ->assertSeeText('Kembalian');
    }

    public function test_kembalian_tercetak_di_struk(): void
    {
        $kasir = $this->kasir();
        $obat = $this->obat();

        $this->bayar($kasir, $obat, ['metode_pembayaran' => 'Tunai', 'uang_diterima' => 50000]);

        $response = $this->actingAs($kasir)->get(route('kasir.riwayat.pdf', Penjualan::firstOrFail()));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertGreaterThan(1000, strlen($response->getContent()));
    }
}
