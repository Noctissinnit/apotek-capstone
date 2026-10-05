<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Untuk pembayaran tunai, kasir mencatat uang yang diterima dari pembeli
 * dan sistem menghitung kembaliannya. Kolom ini dibiarkan kosong untuk
 * QRIS dan Transfer/Debit karena jumlahnya selalu pas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->decimal('uang_diterima', 14, 2)->nullable()->after('total');
            $table->decimal('kembalian', 14, 2)->nullable()->after('uang_diterima');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['uang_diterima', 'kembalian']);
        });
    }
};
