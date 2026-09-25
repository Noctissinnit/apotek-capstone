<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('obat', 'kategori')) {
            Schema::table('obat', function (Blueprint $table) {
                $table->dropColumn('kategori');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('obat', 'kategori')) {
            Schema::table('obat', function (Blueprint $table) {
                $table->string('kategori', 50)->nullable()->after('nama_obat');
            });
        }
    }
};
