<?php

namespace Tests\Feature;

use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Nomor referensi pembayaran non-tunai, untuk mencocokkan penjualan
 * dengan mutasi rekening.
 */
class NoReferensiTest extends TestCase
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

    private function obat(): Obat
    {
        $obat = Obat::create([
            'kode_obat' => 'OBT-'.fake()->unique()->bothify('??##'),
            'nama_obat' => 'Obat Uji',
            'apotek' => 'Apotek A',
            'satuan' => 'Strip',
            'isi_per_box' => 10,
            'harga_jual' => 10000,
            'stok' => 10,
            'stok_minimum' => 1,
        ]);

        $obat->batch()->create([
            'no_batch' => 'BT-'.fake()->unique()->bothify('??##'),
            'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            'stok' => 10,
        ]);

        return $obat;
    }

    private function bayar(array $kiriman)
    {
        return $this->actingAs($this->kasir())
            ->withSession(['kasir.cart' => [$this->obat()->id => 1]])
            ->post(route('kasir.checkout'), $kiriman);
    }

    public function test_nomor_referensi_tersimpan_untuk_qris(): void
    {
        $this->bayar([
            'metode_pembayaran' => 'QRIS',
            'no_referensi' => '2610051245XXXX',
        ])->assertSessionHasNoErrors();

        $this->assertSame('2610051245XXXX', Penjualan::firstOrFail()->no_referensi);
    }

    public function test_nomor_referensi_tersimpan_untuk_transfer_debit(): void
    {
        $this->bayar([
            'metode_pembayaran' => 'Transfer/Debit',
            'no_referensi' => 'APPROVAL-884213',
        ])->assertSessionHasNoErrors();

        $this->assertSame('APPROVAL-884213', Penjualan::firstOrFail()->no_referensi);
    }

    public function test_boleh_dikosongkan_agar_kasir_tidak_terhambat(): void
    {
        $this->bayar(['metode_pembayaran' => 'QRIS'])->assertSessionHasNoErrors();

        $this->assertNull(Penjualan::firstOrFail()->no_referensi);
    }

    public function test_tunai_tidak_menyimpan_nomor_referensi(): void
    {
        $this->bayar([
            'metode_pembayaran' => 'Tunai',
            'uang_diterima' => 50000,
            'no_referensi' => 'TIDAK-RELEVAN',
        ])->assertSessionHasNoErrors();

        $this->assertNull(Penjualan::firstOrFail()->no_referensi, 'tunai tidak punya nomor bukti');
    }

    public function test_kolom_referensi_tersedia_di_layar_kasir(): void
    {
        $obat = $this->obat();

        $this->actingAs($this->kasir())
            ->withSession(['kasir.cart' => [$obat->id => 1]])
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSee('data-bagian-referensi', false)
            ->assertSeeText('No. Referensi');
    }

    public function test_nomor_referensi_tampil_di_riwayat(): void
    {
        $kasir = $this->kasir();
        $obat = $this->obat();

        $this->actingAs($kasir)
            ->withSession(['kasir.cart' => [$obat->id => 1]])
            ->post(route('kasir.checkout'), ['metode_pembayaran' => 'QRIS', 'no_referensi' => 'REF-TAMPIL-01']);

        $this->flushSession();

        $this->actingAs($kasir)
            ->get(route('kasir.riwayat'))
            ->assertOk()
            ->assertSeeText('REF-TAMPIL-01');
    }
}
