<?php

namespace App\Support;

/**
 * Hitungan uang dalam sen (bilangan bulat), supaya penjumlahan harga
 * tidak meleset karena pembulatan bilangan pecahan.
 */
class Rupiah
{
    /** "6000.50" menjadi 600050. */
    public static function keSen(string|int|float $nilai): int
    {
        $teks = is_string($nilai) ? $nilai : number_format((float) $nilai, 2, '.', '');
        [$bulat, $pecahan] = array_pad(explode('.', $teks, 2), 2, '');

        return ((int) $bulat * 100) + (int) str_pad(substr($pecahan, 0, 2), 2, '0');
    }

    /** 600050 menjadi "6000.50", siap disimpan ke kolom decimal. */
    public static function dariSen(int $sen): string
    {
        return number_format($sen / 100, 2, '.', '');
    }

    /** 600050 menjadi "Rp 6.001" untuk ditampilkan. */
    public static function tampil(int $sen): string
    {
        return 'Rp '.number_format($sen / 100, 0, ',', '.');
    }
}
