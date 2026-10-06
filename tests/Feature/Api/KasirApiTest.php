<?php

namespace Tests\Feature\Api;

use App\Models\Obat;
use App\Models\PemakaianBatch;
use App\Models\Penjualan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Meja kasir lewat API: keranjang, pembayaran FEFO, riwayat, dan struk.
 */
class KasirApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        // keranjang disimpan di session, yang hanya diberikan kepada frontend terdaftar
        $this->withHeader('Origin', 'http://localhost');
    }

    private function kasir(string $apotek = 'Apotek A'): User
    {
        $role = $apotek === 'Apotek A' ? 'kasir_apotek_a' : 'kasir_apotek_b';

        return User::factory()->create(['apotek' => $apotek])->assignRole($role);
    }

    /**
     * Obat dengan satu batch per pasangan [stok, hari menuju kadaluarsa].
     */
    private function obat(array $batch = [[50, 365]], string $apotek = 'Apotek A', float $harga = 6500): Obat
    {
        $obat = Obat::create([
            'kode_obat' => 'OBT-'.fake()->unique()->bothify('??##'),
            'nama_obat' => 'Paracetamol',
            'apotek' => $apotek,
            'satuan' => 'Tablet',
            'isi_per_box' => 100,
            'harga_jual' => $harga,
            'stok' => array_sum(array_column($batch, 0)),
            'stok_minimum' => 5,
        ]);

        foreach ($batch as $i => [$stok, $hari]) {
            $obat->batch()->create([
                'no_batch' => "B{$i}",
                'tanggal_kadaluarsa' => now()->addDays($hari)->toDateString(),
                'stok' => $stok,
                'modal_per_satuan' => 4000,
            ]);
        }

        return $obat;
    }

    public function test_hanya_kasir_yang_bisa_memakai_meja_kasir(): void
    {
        $admin = User::factory()->create(['apotek' => 'Apotek A'])->assignRole('admin_apotek_a');

        $this->actingAs($admin)->getJson(route('api.v1.kasir.keranjang'))->assertForbidden();
        $this->actingAs($admin)->postJson(route('api.v1.kasir.checkout'), ['metode_pembayaran' => 'QRIS'])->assertForbidden();
    }

    public function test_daftar_obat_berisi_stok_siap_dijual(): void
    {
        // 30 tablet sudah kadaluarsa, hanya 20 yang boleh dijual
        $this->obat([[20, 100], [30, -1]]);

        $this->actingAs($this->kasir())
            ->getJson(route('api.v1.obat.index', ['sort' => 'kategori']))
            ->assertOk()
            ->assertJsonPath('data.0.stok', 50)
            ->assertJsonPath('data.0.stok_siap_dijual', 20);
    }

    public function test_isi_keranjang_tambah_ubah_hapus(): void
    {
        $kasir = $this->kasir();
        $obat = $this->obat();

        $this->actingAs($kasir)->postJson(route('api.v1.kasir.keranjang.tambah'), ['obat_id' => $obat->id, 'jumlah' => 2])
            ->assertOk()
            ->assertJsonPath('data.item.0.jumlah', 2)
            ->assertJsonPath('data.total', 13000);

        // obat yang sama ditambah lagi: jumlahnya digabung
        $this->postJson(route('api.v1.kasir.keranjang.tambah'), ['obat_id' => $obat->id])
            ->assertJsonPath('data.item.0.jumlah', 3)
            ->assertJsonPath('data.jumlah_item', 3);

        $this->putJson(route('api.v1.kasir.keranjang.atur', $obat->id), ['jumlah' => 5])
            ->assertJsonPath('data.item.0.jumlah', 5)
            ->assertJsonPath('data.total', 32500)
            ->assertJsonPath('data.qris.nominal', 32500);

        $this->deleteJson(route('api.v1.kasir.keranjang.hapus', $obat->id))
            ->assertJsonPath('data.item', [])
            ->assertJsonPath('data.qris', null);
    }

    public function test_jumlah_melebihi_stok_siap_jual_ditolak(): void
    {
        $obat = $this->obat([[3, 100], [10, -1]]);

        $this->actingAs($this->kasir())
            ->postJson(route('api.v1.kasir.keranjang.tambah'), ['obat_id' => $obat->id, 'jumlah' => 4])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['jumlah' => 'Siap dijual: 3 Tablet.']);

        $this->getJson(route('api.v1.kasir.keranjang'))->assertJsonPath('data.item', []);
    }

    public function test_obat_apotek_lain_tidak_bisa_masuk_keranjang(): void
    {
        $obatB = $this->obat(apotek: 'Apotek B');

        $this->actingAs($this->kasir())
            ->postJson(route('api.v1.kasir.keranjang.tambah'), ['obat_id' => $obatB->id])
            ->assertNotFound();
    }

    public function test_checkout_tunai_mengambil_stok_fefo_dan_menghitung_kembalian(): void
    {
        $kasir = $this->kasir();
        // batch B1 lebih dekat kadaluarsa daripada B0, jadi diambil lebih dulu
        $obat = $this->obat([[50, 300], [4, 40]]);

        $this->actingAs($kasir)->postJson(route('api.v1.kasir.keranjang.tambah'), ['obat_id' => $obat->id, 'jumlah' => 6]);

        $respon = $this->postJson(route('api.v1.kasir.checkout'), [
            'metode_pembayaran' => 'Tunai',
            'uang_diterima' => 50000,
        ])->assertCreated();

        $respon->assertJsonPath('data.total', 39000)
            ->assertJsonPath('data.uang_diterima', 50000)
            ->assertJsonPath('data.kembalian', 11000)
            ->assertJsonPath('data.item.0.jumlah', 6)
            ->assertJsonPath('data.struk_url', route('api.v1.kasir.riwayat.pdf', $respon->json('data.id')));

        $this->assertSame(48, $obat->fresh()->stok);
        $this->assertSame(0, $obat->batch()->where('no_batch', 'B1')->value('stok'));
        $this->assertSame(48, $obat->batch()->where('no_batch', 'B0')->value('stok'));
        $this->assertSame(2, PemakaianBatch::count());

        // keranjang kosong lagi setelah dibayar
        $this->getJson(route('api.v1.kasir.keranjang'))->assertJsonPath('data.item', []);
    }

    public function test_checkout_menolak_keranjang_kosong_dan_uang_kurang(): void
    {
        $kasir = $this->kasir();

        $this->actingAs($kasir)->postJson(route('api.v1.kasir.checkout'), ['metode_pembayaran' => 'QRIS'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['keranjang' => 'Keranjang masih kosong.']);

        $obat = $this->obat();
        $this->postJson(route('api.v1.kasir.keranjang.tambah'), ['obat_id' => $obat->id, 'jumlah' => 2]);

        $this->postJson(route('api.v1.kasir.checkout'), ['metode_pembayaran' => 'Tunai'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['uang_diterima' => 'Isi dulu uang yang diterima dari pembeli.']);

        $this->postJson(route('api.v1.kasir.checkout'), ['metode_pembayaran' => 'Tunai', 'uang_diterima' => 10000])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['uang_diterima' => 'Uang yang diterima kurang dari total belanja Rp 13.000.']);

        $this->assertSame(0, Penjualan::count());
        // keranjang tetap utuh supaya kasir tinggal membetulkan isiannya
        $this->getJson(route('api.v1.kasir.keranjang'))->assertJsonPath('data.jumlah_item', 2);
    }

    public function test_checkout_non_tunai_mencatat_nomor_referensi(): void
    {
        $obat = $this->obat();

        $this->actingAs($this->kasir())->postJson(route('api.v1.kasir.keranjang.tambah'), ['obat_id' => $obat->id]);

        $this->postJson(route('api.v1.kasir.checkout'), ['metode_pembayaran' => 'QRIS', 'no_referensi' => 'QR-123'])
            ->assertCreated()
            ->assertJsonPath('data.metode_pembayaran', 'QRIS')
            ->assertJsonPath('data.no_referensi', 'QR-123')
            ->assertJsonPath('data.uang_diterima', null);
    }

    public function test_riwayat_detail_struk_dan_laporan_hanya_apotek_sendiri(): void
    {
        $kasirA = $this->kasir();
        $obat = $this->obat();

        $this->actingAs($kasirA)->postJson(route('api.v1.kasir.keranjang.tambah'), ['obat_id' => $obat->id, 'jumlah' => 2]);
        $id = $this->postJson(route('api.v1.kasir.checkout'), ['metode_pembayaran' => 'QRIS'])->json('data.id');

        $this->getJson(route('api.v1.kasir.riwayat.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $id)
            ->assertJsonPath('data.0.kasir', $kasirA->name)
            ->assertJsonPath('ringkasan.hari_ini.jumlah', 1)
            ->assertJsonPath('ringkasan.hari_ini.total', 13000);

        $this->getJson(route('api.v1.kasir.riwayat.show', $id))
            ->assertOk()
            ->assertJsonPath('data.item.0.nama_obat', 'Paracetamol');

        $this->get(route('api.v1.kasir.riwayat.pdf', $id))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $hariIni = now()->toDateString();
        $this->get(route('api.v1.kasir.laporan.pdf', ['tanggal_mulai' => $hariIni, 'tanggal_selesai' => $hariIni]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        // laporan tanpa tanggal dibalas JSON 422, bukan dialihkan
        $this->get(route('api.v1.kasir.laporan.pdf'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tanggal_mulai', 'tanggal_selesai']);

        $kasirB = $this->kasir('Apotek B');
        $this->actingAs($kasirB)->getJson(route('api.v1.kasir.riwayat.show', $id))->assertNotFound();
        $this->actingAs($kasirB)->get(route('api.v1.kasir.riwayat.pdf', $id))->assertNotFound();
        $this->actingAs($kasirB)->getJson(route('api.v1.kasir.riwayat.index'))->assertJsonCount(0, 'data');
    }
}
