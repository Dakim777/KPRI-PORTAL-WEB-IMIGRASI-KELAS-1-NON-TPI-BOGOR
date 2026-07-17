<?php

namespace App\Imports;

use App\Models\ProdukKantin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ProdukKantinImport implements
    ToCollection,
    WithHeadingRow,
    SkipsEmptyRows
{
    /**
     * Track results for flash message feedback.
     */
    public int $updated  = 0;
    public int $inserted = 0;
    public array $errors = [];

    /**
     * Expected column headers in the Excel file (case-insensitive after normalization):
     *   nama_produk | harga | stok
     *
     * Behaviour:
     *   - If a row's nama_produk already exists (case-insensitive) → UPDATE harga & stok.
     *   - If nama_produk not found → INSERT as new product.
     *   - stok values are REPLACED (not added), so the file should contain the new absolute stok.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because row 1 is the header

            // Normalise keys: trim & lowercase
            $row = $row->map(fn ($val) => is_string($val) ? trim($val) : $val);

            $namaProduk = $row['nama_produk'] ?? null;
            $hargaBeli  = $row['harga_beli']  ?? 0;
            $harga      = $row['harga']       ?? null;
            $stok       = $row['stok']        ?? null;

            // ── Row-level validation ──────────────────────────────────────────
            $validator = Validator::make(
                ['nama_produk' => $namaProduk, 'harga_beli' => $hargaBeli, 'harga' => $harga, 'stok' => $stok],
                [
                    'nama_produk' => ['required', 'string', 'max:255'],
                    'harga'       => ['required', 'numeric', 'min:0'],
                    'stok'        => ['required', 'integer', 'min:0'],
                ],
                [
                    'nama_produk.required' => "Baris {$rowNumber}: Kolom 'nama_produk' wajib diisi.",
                    'harga.required'       => "Baris {$rowNumber}: Kolom 'harga' wajib diisi.",
                    'harga.numeric'        => "Baris {$rowNumber}: Kolom 'harga' harus berupa angka.",
                    'stok.required'        => "Baris {$rowNumber}: Kolom 'stok' wajib diisi.",
                    'stok.integer'         => "Baris {$rowNumber}: Kolom 'stok' harus berupa bilangan bulat.",
                ]
            );

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $msg) {
                    $this->errors[] = $msg;
                }
                continue; // skip this row, continue with the next
            }

            // ── Upsert: cari berdasarkan nama (case-insensitive) ─────────────
            $existing = ProdukKantin::whereRaw('LOWER(nama_produk) = ?', [strtolower($namaProduk)])->first();

            if ($existing) {
                $existing->update([
                    'harga_beli' => (int) $hargaBeli,
                    'harga'      => (int) $harga,
                    'stok'       => (int) $stok,
                ]);
                $this->updated++;
            } else {
                ProdukKantin::create([
                    'nama_produk' => $namaProduk,
                    'harga_beli'  => (int) $hargaBeli,
                    'harga'       => (int) $harga,
                    'stok'        => (int) $stok,
                ]);
                $this->inserted++;
            }
        }
    }
}
