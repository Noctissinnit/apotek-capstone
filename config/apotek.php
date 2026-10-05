<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ambang peringatan kadaluarsa
    |--------------------------------------------------------------------------
    |
    | Dihitung dalam hari menuju tanggal kadaluarsa. Angka "siapkan_retur"
    | sebaiknya disamakan dengan batas waktu retur dari supplier — masih
    | menunggu jawaban mitra (task CR-01-E), sementara dipakai 90 hari.
    |
    */

    'peringatan_kadaluarsa' => [
        'segera_habis' => 30,
        'siapkan_retur' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Kode QRIS tiap apotek
    |--------------------------------------------------------------------------
    |
    | Isi teks dari stiker QRIS masing-masing apotek. Cara mengambilnya:
    | pindai stiker QRIS memakai aplikasi pembaca QR biasa di HP, lalu salin
    | teks panjang yang muncul (diawali "00020101021126...") ke berkas .env.
    |
    | Selama masih kosong, layar kasir menampilkan keterangan bahwa kode QRIS
    | belum diatur, dan pembayaran QRIS tetap bisa dicatat seperti biasa.
    |
    */

    'qris' => [
        'Apotek A' => env('QRIS_APOTEK_A', ''),
        'Apotek B' => env('QRIS_APOTEK_B', ''),
    ],

];
