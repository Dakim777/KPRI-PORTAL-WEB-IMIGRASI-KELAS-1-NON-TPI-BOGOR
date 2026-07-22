<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class SafeEncryptedCast implements CastsAttributes
{
    /**
     * Cast the given value when reading from database.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value) || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Jika data legacy di database belum terenkripsi (plaintext lama),
            // kembalikan nilai apa adanya agar tidak menyebabkan 500 Internal Server Error.
            return $value;
        }
    }

    /**
     * Prepare the given value for storage in database (AES-256-CBC via APP_KEY).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value) || $value === '') {
            return $value;
        }

        try {
            // Coba dekripsi terlebih dahulu untuk mengecek apakah value sudah terenkripsi
            Crypt::decryptString($value);
            return $value;
        } catch (DecryptException $e) {
            // Value berupa plaintext, enkripsi sebelum disimpan ke database
            return Crypt::encryptString((string) $value);
        }
    }
}
