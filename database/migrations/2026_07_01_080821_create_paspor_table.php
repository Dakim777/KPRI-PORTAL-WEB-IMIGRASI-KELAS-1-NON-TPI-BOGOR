<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('transaksi_paspors');
        Schema::create('transaksi_paspors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_pemohon');
            $table->string('no_paspor')->nullable();
            $table->string('no_resi')->unique();
            $table->text('tujuan');
            $table->date('tanggal_kirim');
            $table->unsignedInteger('biaya_ongkir')->default(0);
            $table->unsignedInteger('biaya_tambahan')->default(0);
            $table->unsignedInteger('total_biaya')->default(0);
            $table->string('nama_petugas');
            $table->enum('status_pengiriman', ['Diproses', 'Dikirim', 'Selesai', 'Kendala'])
                  ->default('Diproses');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_paspors');
    }
};
