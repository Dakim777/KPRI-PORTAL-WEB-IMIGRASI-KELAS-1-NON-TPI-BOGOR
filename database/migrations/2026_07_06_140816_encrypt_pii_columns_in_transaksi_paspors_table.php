<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PII Encryption Column Migration
 * ─────────────────────────────────────────────────────────────────────────────
 * Why `text`?
 *   Laravel's `encrypted` cast uses AES-256-CBC + base64 encoding.
 *   The resulting ciphertext is significantly longer than the original value.
 *   A VARCHAR(255) will overflow for long inputs; `text` (up to 65 535 bytes)
 *   safely accommodates any realistic encrypted payload.
 *
 * Columns affected:
 *   - no_paspor  → nullable text   (was nullable string/VARCHAR)
 *   - tujuan     → text            (was text — type stays, but kept explicit
 *                                   here for documentation clarity)
 *
 * Columns left as plaintext (searchable via SQL LIKE):
 *   - nama_pemohon
 *   - no_resi
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_paspors', function (Blueprint $table) {
            // no_paspor: string → text (to hold encrypted ciphertext)
            $table->text('no_paspor')->nullable()->change();

            // tujuan: already text, no structural change needed —
            // the `encrypted` cast is applied at the Eloquent layer only.
            // Listed here for explicit documentation of intent.
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_paspors', function (Blueprint $table) {
            // Revert to original type — WARNING: existing encrypted data
            // will be unreadable as plaintext after rollback.
            $table->string('no_paspor')->nullable()->change();
        });
    }
};
