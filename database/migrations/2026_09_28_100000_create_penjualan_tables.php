<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('riwayat_penjualan')) {
            Schema::create('riwayat_penjualan', function (Blueprint $table) {
                $table->id();
                $table->string('no_transaksi', 50)->unique();
                $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                $table->string('nama_pelanggan')->nullable();
                $table->dateTime('tanggal_penjualan')->index();
                $table->decimal('total', 14, 2)->default(0);
                $table->string('metode_pembayaran', 30)->default('Tunai');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('detail_riwayat_penjualan')) {
            Schema::create('detail_riwayat_penjualan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('penjualan_id')->constrained('riwayat_penjualan')->cascadeOnDelete();
                $table->foreignId('obat_id')->constrained('obat')->restrictOnDelete();
                $table->unsignedInteger('jumlah');
                $table->decimal('harga_jual', 12, 2);
                $table->decimal('subtotal', 14, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_riwayat_penjualan');
        Schema::dropIfExists('riwayat_penjualan');
    }
};
