<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('detail_kantins');
        Schema::dropIfExists('detail_pembelians');
        Schema::dropIfExists('transaksi_kantins');
        Schema::dropIfExists('pembelian_kantins');
        Schema::dropIfExists('produk_kantins');
        Schema::dropIfExists('kategori_kantins');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tables are dropped permanently because the Kantin module was removed.
    }
};
