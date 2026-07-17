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
            $table->string('no_hp')->nullable()->after('nama_penerima');
            $table->enum('metode_pembayaran', ['Cash', 'Transfer', 'QRIS'])->nullable()->after('total_pembayaran_pemohon');
            $table->decimal('handling_fee', 15, 2)->nullable()->default(0)->after('biaya_modal_packaging');
            $table->date('estimasi_tanggal_selesai')->nullable()->after('tanggal_paspor_diterima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_paspors', function (Blueprint $table) {
            $table->dropColumn([
                'no_hp',
                'metode_pembayaran',
                'handling_fee',
                'estimasi_tanggal_selesai'
            ]);
        });
    }
};
