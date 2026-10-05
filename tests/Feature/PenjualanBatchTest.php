<?php

namespace Tests\Feature;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\PemakaianBatch;
use App\Models\Penjualan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CR-01-C: stok berkurang otomatis dari batch yang paling dekat kadaluarsa.
 */
class PenjualanBatchTest extends TestCase
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

    private function obat(int $stok = 0): Obat
    {
        return Obat::create([
            'kode_obat' => 'OBT-'.fake()->unique()->bothify('??##'),
            'nama_obat' => 'Paracetamol',
            'apotek' => 'Apotek A',
            'satuan' => 'Tablet',
            'isi_per_box' => 100,
            'harga_jual' => 600,
            'stok' => $stok,
            'stok_minimum' => 5,
        ]);
    }

    private function batch(Obat $obat, string $nomor, int $stok, ?string $kadaluarsa, array $tambahan = []): BatchObat
    {
        return BatchObat::create(array_merge([
            'obat_id' => $obat->id,
            'no_batch' => $nomor,
            'tanggal_kadaluarsa' => $kadaluarsa,
            'stok' => $stok,
            'modal_per_satuan' => 400,
        ], $tambahan));
    }

    private function bayar(User $kasir, array $keranjang)
    {
        return $this->actingAs($kasir)
            ->withSession(['kasir.cart' => $keranjang])
            ->post(route('kasir.checkout'), ['metode_pembayaran' => 'Tunai']);
    }

    public function test_stok_diambil_dari_batch_paling_dekat_kadaluarsa(): void
    {
        $obat = $this->obat(stok: 250);
        $dekat = $this->batch($obat, 'BT-DEKAT', 50, now()->addMonths(2)->toDateString());
        $jauh = $this->batch($obat, 'BT-JAUH', 200, now()->addYears(2)->toDateString());

        $this->bayar($this->kasir(), [$obat->id => 20])->assertSessionHasNoErrors();

        $this->assertSame(30, $dekat->fresh()->stok, 'batch terdekat kadaluarsa harus berkurang duluan');
        $this->assertSame(200, $jauh->fresh()->stok, 'batch jauh belum tersentuh');
        $this->assertSame(230, $obat->fresh()->stok);
    }

    public function test_pengambilan_lanjut_ke_batch_berikutnya_bila_kurang(): void
    {
        $obat = $this->obat(stok: 250);
        $dekat = $this->batch($obat, 'BT-DEKAT', 50, now()->addMonths(2)->toDateString());
        $jauh = $this->batch($obat, 'BT-JAUH', 200, now()->addYears(2)->toDateString());

        // minta 60: 50 dari batch dekat sampai habis, 10 dari batch berikutnya
        $this->bayar($this->kasir(), [$obat->id => 60])->assertSessionHasNoErrors();

        $this->assertSame(0, $dekat->fresh()->stok);
        $this->assertSame(190, $jauh->fresh()->stok);
        $this->assertSame(190, $obat->fresh()->stok);

        $pemakaian = PemakaianBatch::with('batch')->get();
        $this->assertCount(2, $pemakaian, 'pemakaian dicatat per batch');
        $this->assertSame(
            ['BT-DEKAT' => 50, 'BT-JAUH' => 10],
            $pemakaian->mapWithKeys(fn ($p) => [$p->batch->no_batch => $p->jumlah])->all()
        );
    }

    public function test_batch_kadaluarsa_tidak_ikut_dijual(): void
    {
        $obat = $this->obat(stok: 80);
        $lewat = $this->batch($obat, 'BT-LEWAT', 30, now()->subDay()->toDateString());
        $aman = $this->batch($obat, 'BT-AMAN', 50, now()->addYear()->toDateString());

        $this->bayar($this->kasir(), [$obat->id => 10])->assertSessionHasNoErrors();

        $this->assertSame(30, $lewat->fresh()->stok, 'batch kadaluarsa tidak boleh berkurang');
        $this->assertSame(40, $aman->fresh()->stok);
    }

    public function test_batch_yang_ditahan_tidak_ikut_dijual(): void
    {
        $obat = $this->obat(stok: 80);
        $ditahan = $this->batch($obat, 'BT-TAHAN', 30, now()->addYear()->toDateString(), [
            'status' => BatchObat::STATUS_DITAHAN,
            'catatan_status' => 'Kemasan penyok, menunggu retur',
        ]);
        $aman = $this->batch($obat, 'BT-AMAN', 50, now()->addYears(2)->toDateString());

        $this->bayar($this->kasir(), [$obat->id => 10])->assertSessionHasNoErrors();

        $this->assertSame(30, $ditahan->fresh()->stok);
        $this->assertSame(40, $aman->fresh()->stok);
    }

    public function test_penjualan_ditolak_bila_stok_siap_jual_kurang(): void
    {
        $obat = $this->obat(stok: 100);
        $this->batch($obat, 'BT-LEWAT', 70, now()->subDay()->toDateString());
        $siap = $this->batch($obat, 'BT-SIAP', 30, now()->addYear()->toDateString());

        // stok tertulis 100, tetapi yang boleh dijual cuma 30
        $this->bayar($this->kasir(), [$obat->id => 50])->assertSessionHasErrors('keranjang');

        $this->assertSame(30, $siap->fresh()->stok, 'tidak ada stok yang berkurang saat gagal');
        $this->assertSame(100, $obat->fresh()->stok);
        $this->assertSame(0, Penjualan::count());
        $this->assertSame(0, PemakaianBatch::count());
    }

    public function test_keranjang_menolak_jumlah_melebihi_stok_siap_jual(): void
    {
        $obat = $this->obat(stok: 100);
        $this->batch($obat, 'BT-LEWAT', 70, now()->subDay()->toDateString());
        $this->batch($obat, 'BT-SIAP', 30, now()->addYear()->toDateString());

        $this->actingAs($this->kasir())
            ->post(route('kasir.keranjang.add'), ['obat_id' => $obat->id, 'jumlah' => 40])
            ->assertSessionHas('error');

        $this->assertEmpty(session('kasir.cart', []));
    }

    public function test_layar_kasir_menampilkan_stok_siap_jual_dan_peringatan_kadaluarsa(): void
    {
        $obat = $this->obat(stok: 100);
        $this->batch($obat, 'BT-LEWAT', 70, now()->subDay()->toDateString());
        $this->batch($obat, 'BT-DEKAT', 30, now()->addDays(18)->toDateString());

        $this->actingAs($this->kasir())
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSeeText('Tersedia 30 Tablet')   // bukan 100
            ->assertSeeText('Exp 18 hari lagi');
    }

    public function test_modal_batch_ikut_tercatat_saat_barang_keluar(): void
    {
        $obat = $this->obat(stok: 60);
        $this->batch($obat, 'BT-MURAH', 20, now()->addMonths(3)->toDateString(), ['modal_per_satuan' => 380]);
        $this->batch($obat, 'BT-MAHAL', 40, now()->addYear()->toDateString(), ['modal_per_satuan' => 450]);

        $this->bayar($this->kasir(), [$obat->id => 25])->assertSessionHasNoErrors();

        $this->assertSame(
            ['380.00' => 20, '450.00' => 5],
            PemakaianBatch::get()->mapWithKeys(fn ($p) => [$p->modal_per_satuan => $p->jumlah])->all()
        );
    }
}
