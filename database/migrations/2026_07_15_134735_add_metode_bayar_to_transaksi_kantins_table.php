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
        Schema::table('transaksi_kantins', function (Blueprint $table) {
            $table->string('metode_bayar', 10)->default('cash')->after('jumlah_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_kantins', function (Blueprint $table) {
            $table->dropColumn('metode_bayar');
        });
    }
};
