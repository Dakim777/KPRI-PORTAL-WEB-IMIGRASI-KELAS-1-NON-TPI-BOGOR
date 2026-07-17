<?php

namespace App\Http\Controllers;

use App\Models\PembelianKantin;
use App\Models\DetailPembelian;
use App\Models\ProdukKantin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    /**
     * Tampilkan riwayat pembelian (Barang Masuk)
     */
    public function index()
    {
        $pembelians = PembelianKantin::with(['kasir', 'detailPembelians'])
            ->latest()
            ->paginate(20);

        return view('kantin.pembelian.index', compact('pembelians'));
    }

    /**
     * Tampilkan antarmuka Scanner Barcode (Barang Masuk)
     */
    public function create()
    {
        // Kirim semua produk agar bisa di-search jika scanner rusak
        $produks = ProdukKantin::orderBy('nama_produk')->get();
        return view('kantin.pembelian.create', compact('produks'));
    }

    /**
     * API: Ambil produk berdasarkan barcode (Dipanggil oleh Javascript)
     */
    public function searchBarcode(Request $request)
    {
        $barcode = $request->query('barcode');
        $produk = ProdukKantin::where('barcode', $barcode)->first();

        if ($produk) {
            return response()->json([
                'success' => true,
                'data' => $produk
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Barcode tidak ditemukan'
        ]);
    }

    /**
     * Simpan transaksi pembelian dan tambah stok
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|string'
        ]);

        $items = json_decode($request->items, true);

        if (empty($items)) {
            return redirect()->back()->with('error', 'Tidak ada barang yang diinput!');
        }

        DB::beginTransaction();
        try {
            $totalPembelian = 0;

            // Hitung total dulu
            foreach ($items as $item) {
                $totalPembelian += ($item['qty'] * $item['harga_beli']);
            }

            // Buat record pembelian_kantins
            $pembelian = PembelianKantin::create([
                'kasir_id' => Auth::id(),
                'total_pembelian' => $totalPembelian,
            ]);

            // Simpan detail dan tambah stok
            foreach ($items as $item) {
                DetailPembelian::create([
                    'pembelian_kantin_id' => $pembelian->id,
                    'produk_kantin_id' => $item['id'],
                    'qty' => $item['qty'],
                    'sisa_qty' => $item['qty'], // Untuk alur FIFO
                    'harga_satuan' => $item['harga_beli'],
                    'subtotal' => $item['qty'] * $item['harga_beli'],
                ]);

                // Tambah stok ke master produk dan update harga beli terakhir (untuk referensi kulakan)
                $produk = ProdukKantin::findOrFail($item['id']);
                $produk->stok += $item['qty'];
                $produk->harga_beli = $item['harga_beli'];
                $produk->save();
            }

            DB::commit();
            return redirect()->route('kantin.pembelian.index')->with('success', 'Transaksi Barang Masuk berhasil disimpan! Stok bertambah otomatis.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
