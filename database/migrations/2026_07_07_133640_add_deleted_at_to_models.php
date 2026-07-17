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
        Schema::table('transaksi_paspors', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('produk_kantins', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('transaksi_kantins', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_paspors', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('produk_kantins', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('transaksi_kantins', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
