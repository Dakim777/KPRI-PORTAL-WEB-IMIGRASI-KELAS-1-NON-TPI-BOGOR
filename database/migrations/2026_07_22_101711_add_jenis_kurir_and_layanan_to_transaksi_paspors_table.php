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
            $table->string('jenis_kurir', 50)->nullable()->default('POS')->after('no_resi');
            $table->string('layanan_kurir', 100)->nullable()->default('Reguler')->after('jenis_kurir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_paspors', function (Blueprint $table) {
            $table->dropColumn(['jenis_kurir', 'layanan_kurir']);
        });
    }
};
