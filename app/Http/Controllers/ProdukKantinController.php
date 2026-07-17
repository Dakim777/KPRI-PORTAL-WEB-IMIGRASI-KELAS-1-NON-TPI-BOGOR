<?php

namespace App\Http\Controllers;

use App\Imports\ProdukKantinImport;
use App\Models\KategoriKantin;
use App\Models\ProdukKantin;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProdukKantinController extends Controller
{
    public function index(Request $request)
    {
        $query = ProdukKantin::with('kategori')->latest();

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }
        if ($request->filled('stok_filter')) {
            if ($request->stok_filter === 'habis') {
                $query->where('stok', 0);
            } elseif ($request->stok_filter === 'menipis') {
                $query->where('stok', '>', 0)->where('stok', '<=', 5);
            }
        }

        $produks   = $query->paginate(15)->withQueryString();
        $kategoris = KategoriKantin::orderBy('nama_kategori')->get();

        return view('kantin.produk.index', compact('produks', 'kategoris'));
    }

    public function create()
    {
        $kategoris = KategoriKantin::orderBy('nama_kategori')->get();
        return view('kantin.produk.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kategori_id' => ['nullable', 'exists:kategori_kantins,id'],
            'nama_produk' => ['required', 'string', 'max:255'],
            'barcode'     => ['nullable', 'string', 'max:255', 'unique:produk_kantins,barcode'],
            'harga_beli'  => ['required', 'numeric', 'min:0'],
            'harga'       => ['required', 'numeric', 'min:0'],
            'stok'        => ['required', 'integer', 'min:0'],
        ]);

        ProdukKantin::create($data);

        return redirect()->route('kantin.produk.index')
            ->with('success', "Produk \"{$data['nama_produk']}\" berhasil ditambahkan.");
    }

    public function edit(ProdukKantin $produk)
    {
        $kategoris = KategoriKantin::orderBy('nama_kategori')->get();
        return view('kantin.produk.edit', compact('produk', 'kategoris'));
    }

    public function update(Request $request, ProdukKantin $produk)
    {
        $data = $request->validate([
            'kategori_id' => ['nullable', 'exists:kategori_kantins,id'],
            'nama_produk' => ['required', 'string', 'max:255'],
            'barcode'     => ['nullable', 'string', 'max:255', 'unique:produk_kantins,barcode,' . $produk->id],
            'harga_beli'  => ['required', 'numeric', 'min:0'],
            'harga'       => ['required', 'numeric', 'min:0'],
            'stok'        => ['required', 'integer', 'min:0'],
        ]);

        $produk->update($data);

        return redirect()->route('kantin.produk.index')
            ->with('success', "Produk \"{$data['nama_produk']}\" berhasil diperbarui.");
    }

    public function destroy(ProdukKantin $produk)
    {
        $nama = $produk->nama_produk;
        $produk->delete();

        return redirect()->route('kantin.produk.index')
            ->with('success', "Produk \"{$nama}\" berhasil dihapus.");
    }

    // show is not used in CRUD but required by resource
    public function show(ProdukKantin $produk)
    {
        return redirect()->route('kantin.produk.edit', $produk);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IMPORT EXCEL / CSV
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Proses upload & import file Excel/CSV untuk update stok massal.
     * Logika: jika nama produk sudah ada → UPDATE harga & stok.
     *         jika belum ada             → INSERT produk baru.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_import' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:5120', // max 5 MB
            ],
        ], [
            'file_import.required' => 'Pilih file Excel atau CSV terlebih dahulu.',
            'file_import.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file_import.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $import = new ProdukKantinImport();
        Excel::import($import, $request->file('file_import'));

        // Jika ada baris yang gagal validasi, tampilkan error + hasil parsial
        if (!empty($import->errors)) {
            $pesanError = implode(' | ', $import->errors);
            $pesanOk    = "Import selesai dengan catatan: {$import->updated} produk diperbarui, {$import->inserted} produk baru ditambahkan.";

            return redirect()->route('kantin.produk.index')
                ->with('warning', "{$pesanOk} ⚠️ Beberapa baris dilewati: {$pesanError}");
        }

        return redirect()->route('kantin.produk.index')
            ->with('success', "✅ Import berhasil! {$import->updated} produk diperbarui, {$import->inserted} produk baru ditambahkan.");
    }

    /**
     * Download file template Excel kosong sebagai panduan format import.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_produk.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // BOM untuk Excel agar UTF-8 terbaca dengan benar
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, ['nama_produk', 'harga', 'stok']);

            // Contoh data
            fputcsv($handle, ['Nasi Goreng', 15000, 50]);
            fputcsv($handle, ['Es Teh Manis', 5000, 100]);
            fputcsv($handle, ['Mie Ayam Spesial', 18000, 30]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
