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
        Schema::table('detail_pembelians', function (Blueprint $table) {
            $table->integer('sisa_qty')->default(0)->after('qty');
        });

        Schema::table('detail_kantins', function (Blueprint $table) {
            $table->decimal('harga_modal', 12, 2)->default(0)->after('harga_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_pembelians', function (Blueprint $table) {
            $table->dropColumn('sisa_qty');
        });

        Schema::table('detail_kantins', function (Blueprint $table) {
            $table->dropColumn('harga_modal');
        });
    }
};
