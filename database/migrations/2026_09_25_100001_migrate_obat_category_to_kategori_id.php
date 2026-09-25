<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->unsignedBigInteger('kategori_id')->nullable()->after('nama_obat');
            $table->foreign('kategori_id')->references('id_kategori')->on('kategori')->nullOnDelete();
        });

        if (Schema::hasColumn('obat', 'kategori')) {
            DB::table('obat')
                ->whereNotNull('kategori')
                ->select('kategori')
                ->distinct()
                ->pluck('kategori')
                ->each(function (string $namaKategori): void {
                    DB::table('kategori')->insertOrIgnore([
                        'nama_kategori' => $namaKategori,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });

            DB::table('obat')->whereNotNull('kategori')->get()->each(function (object $obat): void {
                DB::table('obat')->where('id', $obat->id)->update([
                    'kategori_id' => DB::table('kategori')->where('nama_kategori', $obat->kategori)->value('id_kategori'),
                ]);
            });

            Schema::table('obat', function (Blueprint $table) {
                $table->dropColumn('kategori');
            });
        }
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->string('kategori', 50)->nullable()->after('nama_obat');
        });

        DB::table('obat')->whereNotNull('kategori_id')->get()->each(function (object $obat): void {
            DB::table('obat')->where('id', $obat->id)->update([
                'kategori' => DB::table('kategori')->where('id_kategori', $obat->kategori_id)->value('nama_kategori'),
            ]);
        });

        Schema::table('obat', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori_id');
        });
    }
};
