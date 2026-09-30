<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tiap transaksi penjualan dicatat milik apotek mana, supaya riwayat dan
 * laporan bisa dipisah dengan pasti, tidak hanya ditebak dari kasirnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->string('apotek')->nullable()->after('user_id')->index();
        });

        // Transaksi lama diisi mengikuti apotek kasir yang membuatnya
        DB::table('penjualan')
            ->whereNull('apotek')
            ->update([
                'apotek' => DB::raw('(select apotek from users where users.id = penjualan.user_id)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropIndex(['apotek']);
            $table->dropColumn('apotek');
        });
    }
};
