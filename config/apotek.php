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

];
