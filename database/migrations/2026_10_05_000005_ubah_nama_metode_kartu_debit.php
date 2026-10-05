<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mitra menyebut cara pembayarannya "Transfer/Debit", bukan "Kartu Debit",
 * karena mencakup transfer bank maupun gesek kartu di mesin EDC.
 * Transaksi lama ikut disesuaikan supaya laporan tidak terpecah dua.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('penjualan')
            ->where('metode_pembayaran', 'Kartu Debit')
            ->update(['metode_pembayaran' => 'Transfer/Debit']);
    }

    public function down(): void
    {
        DB::table('penjualan')
            ->where('metode_pembayaran', 'Transfer/Debit')
            ->update(['metode_pembayaran' => 'Kartu Debit']);
    }
};
