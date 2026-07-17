<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk_kantins', function (Blueprint $table) {
            // Nullable so existing products are not broken
            $table->foreignId('kategori_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('kategori_kantins')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('produk_kantins', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\KategoriKantin::class);
            $table->dropColumn('kategori_id');
        });
    }
};
