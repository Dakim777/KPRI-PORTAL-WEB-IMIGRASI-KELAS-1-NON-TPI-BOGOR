<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hapus role kasir_kantin dari enum users dan bersihkan akun lama kantin.
     */
    public function up(): void
    {
        // 1. Hapus semua user dengan role kasir_kantin (akun kantin lama yang tidak relevan)
        DB::table('users')->where('role', 'kasir_kantin')->delete();

        // 2. Ubah enum role: hapus 'kasir_kantin', sisakan hanya 'super_admin' dan 'kasir_paspor'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'kasir_paspor') NOT NULL DEFAULT 'kasir_paspor'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan enum ke 3 role jika rollback
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'kasir_kantin', 'kasir_paspor') NOT NULL DEFAULT 'kasir_kantin'");
    }
};
