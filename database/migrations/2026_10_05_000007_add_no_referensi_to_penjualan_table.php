<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor bukti pembayaran non-tunai, dipakai saat mencocokkan penjualan
 * dengan mutasi rekening. Sumbernya berbeda-beda:
 * QRIS dari notifikasi, transfer dari mutasi, kartu debit dari struk EDC.
 * Boleh dikosongkan agar kasir tidak terhambat saat ramai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->string('no_referensi', 50)->nullable()->after('metode_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn('no_referensi');
        });
    }
};
