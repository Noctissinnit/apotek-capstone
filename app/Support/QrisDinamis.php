<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use InvalidArgumentException;

/**
 * Menyisipkan nominal ke dalam kode QRIS statis milik apotek, sehingga
 * pembeli tinggal memindai tanpa mengetik jumlah bayarnya.
 *
 * Isi kode QRIS itu barisan teks berformat EMVCo: setiap bagian terdiri dari
 * dua angka penanda, dua angka panjang, lalu isinya. Yang diubah di sini:
 *   - penanda 01 : "11" (statis) menjadi "12" (sekali pakai)
 *   - penanda 54 : nominal pembayaran, disisipkan
 *   - penanda 63 : kode pemeriksa, dihitung ulang di akhir
 *
 * Catatan: pembayarannya tidak terkonfirmasi otomatis. Kasir tetap perlu
 * memeriksa notifikasi masuk sebelum menekan tombol proses pembayaran.
 */
class QrisDinamis
{
    private const PENANDA_JENIS = '01';

    private const PENANDA_NOMINAL = '54';

    private const PENANDA_PEMERIKSA = '63';

    /**
     * Ubah kode QRIS statis menjadi kode berisi nominal.
     *
     * @param  string  $statis  isi teks stiker QRIS apotek
     * @param  int  $nominal  jumlah bayar dalam rupiah penuh
     */
    public function dariStatis(string $statis, int $nominal): string
    {
        $statis = trim($statis);

        if ($statis === '' || $nominal < 1) {
            throw new InvalidArgumentException('Kode QRIS atau nominal tidak valid.');
        }

        $bagian = $this->uraikan($statis);

        $bagian[self::PENANDA_JENIS] = '12';
        $bagian[self::PENANDA_NOMINAL] = (string) $nominal;
        unset($bagian[self::PENANDA_PEMERIKSA]);

        // penanda disusun berurutan seperti aslinya, lalu kode pemeriksa di akhir
        ksort($bagian, SORT_STRING);

        $hasil = '';
        foreach ($bagian as $penanda => $isi) {
            $hasil .= $penanda.str_pad((string) strlen($isi), 2, '0', STR_PAD_LEFT).$isi;
        }

        $hasil .= self::PENANDA_PEMERIKSA.'04';

        return $hasil.$this->kodePemeriksa($hasil);
    }

    /**
     * Gambar kode QR sebagai SVG, supaya tajam di layar berapa pun ukurannya
     * dan tidak membutuhkan ekstensi gambar di server.
     */
    public function gambarSvg(string $isi, int $ukuran = 280): string
    {
        $penulis = new Writer(new ImageRenderer(new RendererStyle($ukuran, 1), new SvgImageBackEnd));

        return $penulis->writeString($isi);
    }

    /**
     * Pecah teks EMVCo menjadi pasangan penanda dan isinya.
     *
     * @return array<string, string>
     */
    private function uraikan(string $teks): array
    {
        $bagian = [];
        $posisi = 0;
        $panjangTeks = strlen($teks);

        while ($posisi + 4 <= $panjangTeks) {
            $penanda = substr($teks, $posisi, 2);
            $panjang = (int) substr($teks, $posisi + 2, 2);

            if ($panjang < 1 || $posisi + 4 + $panjang > $panjangTeks) {
                throw new InvalidArgumentException('Kode QRIS tidak terbaca pada penanda '.$penanda.'.');
            }

            $bagian[$penanda] = substr($teks, $posisi + 4, $panjang);
            $posisi += 4 + $panjang;
        }

        if (! isset($bagian['00'])) {
            throw new InvalidArgumentException('Kode QRIS tidak mengandung penanda pembuka.');
        }

        return $bagian;
    }

    /**
     * Kode pemeriksa CRC-16/CCITT-FALSE, dipakai standar QRIS agar pembaca
     * bisa menolak kode yang rusak atau salah ketik.
     */
    private function kodePemeriksa(string $teks): string
    {
        $crc = 0xFFFF;

        for ($i = 0; $i < strlen($teks); $i++) {
            $crc ^= ord($teks[$i]) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
