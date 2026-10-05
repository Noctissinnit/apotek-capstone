<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom tambahan untuk modul barang masuk (CR-01-B):
 * satuan yang dipakai saat input, diskon, jumlah hasil konversi,
 * dan batch yang terbentuk dari baris faktur ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->string('apotek')->nullable()->after('user_id')->index();
        });

        DB::table('pembelian')
            ->whereNull('apotek')
            ->update([
                'apotek' => DB::raw('(select apotek from users where users.id = pembelian.user_id)'),
            ]);

        Schema::table('detail_pembelian', function (Blueprint $table) {
            // 'box' atau 'satuan' — menentukan arti kolom jumlah dan harga_beli
            $table->string('satuan_input', 10)->default('box')->after('obat_id');

            // isi per box saat transaksi terjadi, disimpan agar riwayat tetap benar
            // walaupun isi per box di master obat diubah kemudian
            $table->unsignedInteger('isi_per_box')->default(1)->after('satuan_input');

            $table->decimal('diskon_persen', 5, 2)->nullable()->after('harga_beli');

            // jumlah setelah dikonversi ke satuan terkecil
            $table->unsignedInteger('jumlah_satuan')->default(0)->after('jumlah');

            // modal satu satuan terkecil setelah diskon
            $table->decimal('modal_per_satuan', 12, 2)->nullable()->after('diskon_persen');

            $table->foreignId('batch_obat_id')->nullable()->after('obat_id')->constrained('batch_obat')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detail_pembelian', function (Blueprint $table) {
            $table->dropForeign(['batch_obat_id']);
            $table->dropColumn(['satuan_input', 'isi_per_box', 'diskon_persen', 'jumlah_satuan', 'modal_per_satuan', 'batch_obat_id']);
        });

        Schema::table('pembelian', function (Blueprint $table) {
            $table->dropIndex(['apotek']);
            $table->dropColumn('apotek');
        });
    }
};
