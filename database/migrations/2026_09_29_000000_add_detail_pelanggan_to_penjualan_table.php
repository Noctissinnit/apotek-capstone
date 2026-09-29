<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom tambahan untuk kebutuhan laporan penjualan.
 * Tabel `penjualan` dipakai bersama oleh kasir (checkout) dan laporan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->string('nama_pelanggan')->nullable()->after('user_id');
            $table->string('metode_pembayaran', 30)->default('Tunai')->after('total');
            $table->text('keterangan')->nullable()->after('metode_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['nama_pelanggan', 'metode_pembayaran', 'keterangan']);
        });
    }
};
