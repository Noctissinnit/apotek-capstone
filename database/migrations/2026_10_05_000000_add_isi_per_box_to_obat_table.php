<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Apotek membeli obat per box, tetapi menjual dalam satuan terkecil.
 * Kolom ini menyimpan isi satu box dalam satuan terkecil, misalnya
 * 1 box = 100 tablet. Dipakai saat barang masuk untuk menghitung
 * penambahan stok dan modal per satuan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->unsignedInteger('isi_per_box')->default(1)->after('satuan');
        });
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropColumn('isi_per_box');
        });
    }
};
