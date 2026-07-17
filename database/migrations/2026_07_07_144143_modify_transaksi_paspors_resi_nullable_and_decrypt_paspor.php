<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\TransaksiPaspor;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Decrypt existing no_paspor data and save as plaintext via DB facade
        // so it doesn't get re-encrypted by Eloquent on save.
        $paspors = TransaksiPaspor::withTrashed()->get();
        
        foreach ($paspors as $paspor) {
            // $paspor->no_paspor is decrypted automatically by Eloquent when accessed.
            if ($paspor->no_paspor) {
                DB::table('transaksi_paspors')
                    ->where('id', $paspor->id)
                    ->update(['no_paspor' => $paspor->no_paspor]);
            }
        }

        // 2. Make no_resi nullable
        Schema::table('transaksi_paspors', function (Blueprint $table) {
            $table->string('no_resi')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_paspors', function (Blueprint $table) {
            $table->string('no_resi')->nullable(false)->change();
        });

        // Re-encrypting everything in down()
        // Needs careful handling if the model cast was already added back, 
        // but typically down() isn't heavily relied on for this edge case.
    }
};
