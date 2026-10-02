<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan nama berkas gambar obat, misalnya "obat/paracetamol-xyz.jpg".
 * Berkasnya sendiri ada di folder public/uploads, bukan di database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->string('gambar')->nullable()->after('nama_obat');
        });
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropColumn('gambar');
        });
    }
};
