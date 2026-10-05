<?php

namespace Tests\Feature;

use App\Models\Obat;
use App\Models\User;
use App\Support\QrisDinamis;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Kode QR berisi nominal, dibuat dari kode QRIS statis milik apotek.
 */
class QrisDinamisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * Susun kode QRIS statis contoh beserta kode pemeriksanya,
     * meniru bentuk teks pada stiker QRIS sungguhan.
     */
    private function qrisStatisContoh(): string
    {
        $isi = '000201'                 // format EMVCo
            .'010211'                   // jenis: statis
            .'52045912'                 // kategori merchant
            .'5303360'                  // mata uang rupiah
            .'5802ID'                   // negara
            .'5908APOTEK A'             // nama merchant, 8 huruf
            .'6008SURABAYA'             // kota, 8 huruf
            .'6304';                    // penanda kode pemeriksa

        return $isi.$this->hitungPemeriksa($isi);
    }

    /** Perhitungan CRC-16/CCITT-FALSE, ditulis ulang agar tidak sekadar mencocokkan hasil sendiri. */
    private function hitungPemeriksa(string $teks): string
    {
        $crc = 0xFFFF;

        foreach (str_split($teks) as $huruf) {
            $crc ^= ord($huruf) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) & 0xFFFF : ($crc << 1) & 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /** @return array<string, string> */
    private function uraikan(string $teks): array
    {
        $bagian = [];
        $posisi = 0;

        while ($posisi + 4 <= strlen($teks)) {
            $penanda = substr($teks, $posisi, 2);
            $panjang = (int) substr($teks, $posisi + 2, 2);
            $bagian[$penanda] = substr($teks, $posisi + 4, $panjang);
            $posisi += 4 + $panjang;
        }

        return $bagian;
    }

    public function test_nominal_disisipkan_dan_jenis_berubah_jadi_sekali_pakai(): void
    {
        $hasil = (new QrisDinamis)->dariStatis($this->qrisStatisContoh(), 20000);
        $bagian = $this->uraikan($hasil);

        $this->assertSame('20000', $bagian['54'], 'nominal masuk ke penanda 54');
        $this->assertSame('12', $bagian['01'], 'jenis berubah dari statis ke sekali pakai');
        $this->assertSame('APOTEK A', $bagian['59'], 'data merchant tidak berubah');
        $this->assertSame('360', $bagian['53']);
    }

    public function test_kode_pemeriksa_dihitung_ulang_dengan_benar(): void
    {
        $hasil = (new QrisDinamis)->dariStatis($this->qrisStatisContoh(), 20000);

        $tanpaPemeriksa = substr($hasil, 0, -4);
        $pemeriksa = substr($hasil, -4);

        $this->assertStringEndsWith('6304', $tanpaPemeriksa, 'penanda kode pemeriksa ada di akhir');
        $this->assertSame($this->hitungPemeriksa($tanpaPemeriksa), $pemeriksa);
    }

    public function test_nominal_berbeda_menghasilkan_kode_berbeda(): void
    {
        $qris = new QrisDinamis;
        $statis = $this->qrisStatisContoh();

        $this->assertNotSame(
            $qris->dariStatis($statis, 20000),
            $qris->dariStatis($statis, 35000)
        );
    }

    public function test_kode_qris_rusak_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new QrisDinamis)->dariStatis('teks-ngawur-bukan-qris', 20000);
    }

    public function test_gambar_svg_dihasilkan(): void
    {
        $svg = (new QrisDinamis)->gambarSvg('00020101021263041234');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    public function test_layar_kasir_menampilkan_kode_qr_bila_qris_apotek_sudah_diatur(): void
    {
        config(['apotek.qris.Apotek A' => $this->qrisStatisContoh()]);

        $this->actingAs($this->kasir())
            ->withSession(['kasir.cart' => [$this->obat()->id => 2]])
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSee('data-bagian-qris', false)
            ->assertSee('<svg', false)
            ->assertSeeText('Minta pembeli memindai kode ini');
    }

    public function test_memberi_keterangan_bila_kode_qris_belum_diatur(): void
    {
        config(['apotek.qris.Apotek A' => '']);

        $this->actingAs($this->kasir())
            ->withSession(['kasir.cart' => [$this->obat()->id => 1]])
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSeeText('Kode QRIS apotek belum diatur');
    }

    public function test_kode_qris_salah_salin_tidak_menghalangi_penjualan(): void
    {
        config(['apotek.qris.Apotek A' => 'teks-ngawur-bukan-qris']);

        $this->actingAs($this->kasir())
            ->withSession(['kasir.cart' => [$this->obat()->id => 1]])
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSeeText('Kode QRIS apotek tidak terbaca');
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
}
