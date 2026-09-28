<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('no_faktur', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('tanggal_penjualan');
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat')->restrictOnDelete();
            $table->unsignedInteger('jumlah');
            $table->decimal('harga_jual', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penjualan');
        Schema::dropIfExists('penjualan');
    }
};