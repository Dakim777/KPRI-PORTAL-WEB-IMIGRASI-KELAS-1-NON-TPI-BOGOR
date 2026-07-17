<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add barcode to produk_kantins
        Schema::table('produk_kantins', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique()->after('nama_produk');
        });

        // 2. Create pembelian_kantins table
        Schema::create('pembelian_kantins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_pembelian', 12, 2);
            $table->timestamps();
        });

        // 3. Create detail_pembelians table
        Schema::create('detail_pembelians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_kantin_id')->constrained('pembelian_kantins')->cascadeOnDelete();
            $table->foreignId('produk_kantin_id')->constrained('produk_kantins')->cascadeOnDelete();
            $table->integer('qty');
            $table->decimal('harga_satuan', 12, 2); // Harga beli
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembelians');
        Schema::dropIfExists('pembelian_kantins');
        
        Schema::table('produk_kantins', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
