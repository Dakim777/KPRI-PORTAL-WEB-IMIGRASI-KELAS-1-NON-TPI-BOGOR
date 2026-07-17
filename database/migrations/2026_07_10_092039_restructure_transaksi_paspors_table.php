<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // To ensure a clean slate for the restructure, drop the old table first.
        Schema::dropIfExists('transaksi_paspors');

        Schema::create('transaksi_paspors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('id_transaksi')->unique(); // e.g., TRX-PASPOR-20260710-001
            $table->unsignedInteger('no_struk');      // Daily reset to 1
            
            $table->string('nama_penerima');
            $table->unsignedInteger('jumlah_paspor');
            $table->text('nama_paspor_list');         // Array of names for each passport (encrypted JSON string)
            $table->text('alamat_tujuan_pengiriman');
            $table->string('no_resi')->nullable();
            
            $table->decimal('total_pembayaran_pemohon', 15, 2);
            $table->decimal('biaya_kurir', 15, 2)->default(0);
            $table->decimal('biaya_modal_packaging', 15, 2)->default(1000);
            
            $table->decimal('harga_materai', 15, 2)->default(0);      // 12000 * jumlah_paspor
            $table->decimal('harga_surat_kuasa', 15, 2)->default(0);  // 1000 * jumlah_paspor
            
            $table->date('tanggal_transaksi');
            $table->date('tanggal_pengiriman_paspor')->nullable();
            $table->date('tanggal_paspor_diterima')->nullable();
            
            $table->enum('status_pengiriman', ['transaksi', 'diproses', 'batal', 'selesai'])->default('transaksi');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_paspors');
    }
};
