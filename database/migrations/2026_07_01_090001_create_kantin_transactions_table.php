<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_kantins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_harga', 12, 2);
            $table->decimal('jumlah_bayar', 12, 2);
            $table->decimal('kembalian', 12, 2);
            $table->timestamps();
        });

        Schema::create('detail_kantins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_kantin_id')->constrained('transaksi_kantins')->cascadeOnDelete();
            $table->foreignId('produk_kantin_id')->constrained('produk_kantins')->cascadeOnDelete();
            $table->integer('qty');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_kantins');
        Schema::dropIfExists('transaksi_kantins');
    }
};
