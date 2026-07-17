<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPenjualanExport;
use App\Models\DetailKantin;
use App\Models\DetailPembelian;
use App\Models\ProdukKantin;
use App\Models\TransaksiKantin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class KantinController extends Controller
{
    /**
     * POS page — shows all products with stock > 0.
     */
    public function pos()
    {
        $produks = ProdukKantin::with('kategori')->where('stok', '>', 0)->orderBy('nama_produk')->get();
        $kategoris = \App\Models\KategoriKantin::orderBy('nama_kategori')->get();
        return view('kantin.pos', compact('produks', 'kategoris'));
    }

    /**
     * Save a POS transaction — wrapped in a DB transaction.
     */
    public function simpanTransaksi(Request $request)
    {
        if ($request->has('items') && is_string($request->items)) {
            $request->merge(['items' => json_decode($request->items, true) ?? []]);
        }

        $request->validate([
            'items'        => ['required', 'array', 'min:1'],
            'items.*.id'   => ['required', 'exists:produk_kantins,id'],
            'items.*.qty'  => ['required', 'integer', 'min:1'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
            'metode_bayar' => ['required', 'in:cash,qris'],
        ]);

        DB::beginTransaction();

        try {
            $totalHarga = 0;
            $itemsData  = [];

            foreach ($request->items as $item) {
                $produk = ProdukKantin::lockForUpdate()->findOrFail($item['id']);

                if ($produk->stok < $item['qty']) {
                    DB::rollBack();
                    return back()->withErrors([
                        'stok' => "Stok \"{$produk->nama_produk}\" tidak mencukupi (tersisa {$produk->stok}).",
                    ]);
                }

                $subtotal    = $produk->harga * $item['qty'];
                $totalHarga += $subtotal;

                $itemsData[] = [
                    'produk'       => $produk,
                    'qty'          => $item['qty'],
                    'harga_satuan' => $produk->harga,
                    'subtotal'     => $subtotal,
                ];

                $produk->decrement('stok', $item['qty']);
            }

            $jumlahBayar  = (float) $request->jumlah_bayar;
            $metodeBayar  = $request->metode_bayar;

            if ($metodeBayar === 'cash' && $jumlahBayar < $totalHarga) {
                DB::rollBack();
                return back()->withErrors(['jumlah_bayar' => 'Jumlah bayar kurang dari total harga.']);
            }

            // Untuk QRIS, jumlah bayar = total harga
            if ($metodeBayar === 'qris') {
                $jumlahBayar = $totalHarga;
            }

            $kembalian = $jumlahBayar - $totalHarga;

            $transaksi = TransaksiKantin::create([
                'kasir_id'     => Auth::id(),
                'total_harga'  => $totalHarga,
                'jumlah_bayar' => $jumlahBayar,
                'kembalian'    => $kembalian,
                'metode_bayar' => $metodeBayar,
            ]);

            foreach ($itemsData as $detail) {
                $produk = $detail['produk'];
                $sisaQtyDibutuhkan = $detail['qty'];

                // 1. Alur FIFO: Ambil antrean kulakan terlama yang sisa_qty-nya masih > 0
                $batches = DetailPembelian::where('produk_kantin_id', $produk->id)
                    ->where('sisa_qty', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($sisaQtyDibutuhkan <= 0) break;

                    if ($batch->sisa_qty >= $sisaQtyDibutuhkan) {
                        // Batch cukup untuk memenuhi kebutuhan
                        $qtyDiambil = $sisaQtyDibutuhkan;
                        $batch->sisa_qty -= $qtyDiambil;
                        $batch->save();

                        DetailKantin::create([
                            'transaksi_kantin_id' => $transaksi->id,
                            'produk_kantin_id'    => $produk->id,
                            'qty'                 => $qtyDiambil,
                            'harga_satuan'        => $detail['harga_satuan'],
                            'harga_modal'         => $batch->harga_satuan, // HPP dari kulakan terlama ini
                            'subtotal'            => $qtyDiambil * $detail['harga_satuan'],
                        ]);

                        $sisaQtyDibutuhkan = 0;
                    } else {
                        // Batch tidak cukup, kuras habis batch ini, lalu lanjut ke batch berikutnya
                        $qtyDiambil = $batch->sisa_qty;
                        $batch->sisa_qty = 0;
                        $batch->save();

                        DetailKantin::create([
                            'transaksi_kantin_id' => $transaksi->id,
                            'produk_kantin_id'    => $produk->id,
                            'qty'                 => $qtyDiambil,
                            'harga_satuan'        => $detail['harga_satuan'],
                            'harga_modal'         => $batch->harga_satuan, // HPP dari kulakan ini
                            'subtotal'            => $qtyDiambil * $detail['harga_satuan'],
                        ]);

                        $sisaQtyDibutuhkan -= $qtyDiambil;
                    }
                }

                // 2. Fallback: Jika ternyata barang yang dijual adalah sisa stok awal (belum ada history detail_pembelians)
                if ($sisaQtyDibutuhkan > 0) {
                    DetailKantin::create([
                        'transaksi_kantin_id' => $transaksi->id,
                        'produk_kantin_id'    => $produk->id,
                        'qty'                 => $sisaQtyDibutuhkan,
                        'harga_satuan'        => $detail['harga_satuan'],
                        'harga_modal'         => $produk->harga_beli, // Fallback pakai harga master
                        'subtotal'            => $sisaQtyDibutuhkan * $detail['harga_satuan'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('kantin.transaksi.show', $transaksi->id)
                ->with('success', 'Transaksi berhasil disimpan.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
        }
    }

    /**
     * Riwayat transaksi kantin.
     */
    public function riwayat(Request $request)
    {
        $query = TransaksiKantin::with(['kasir', 'details.produk'])->latest();

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $transaksis = $query->paginate(15)->withQueryString();

        return view('kantin.transaksi.index', compact('transaksis'));
    }

    /**
     * Detail satu transaksi (struk).
     */
    public function showTransaksi(TransaksiKantin $transaksi)
    {
        $transaksi->load(['details.produk', 'kasir']);
        return view('kantin.transaksi.show', compact('transaksi'));
    }

    /**
     * Thermal receipt print view — 80mm paper format.
     * Auto-triggers browser print dialog on load.
     */
    public function printReceipt(TransaksiKantin $transaksi)
    {
        $transaksi->load(['details.produk', 'kasir']);
        return view('kantin.transaksi.print', compact('transaksi'));
    }

    /**
     * Export laporan penjualan kantin ke Excel.
     * Supports optional date range via query params: ?dari=YYYY-MM-DD&sampai=YYYY-MM-DD
     */
    public function exportExcel(Request $request)
    {
        $dari   = $request->input('dari');
        $sampai = $request->input('sampai');

        $suffix   = ($dari && $sampai) ? "{$dari}_sd_{$sampai}" : now()->format('Ymd_His');
        $filename = "laporan-penjualan-kantin_{$suffix}.xlsx";

        return Excel::download(new LaporanPenjualanExport($dari, $sampai), $filename);
    }

    /**
     * Download Laporan Excel Pembelian Kantin (Barang Masuk)
     * Supports optional date range via query params: ?dari=YYYY-MM-DD&sampai=YYYY-MM-DD
     */
    public function exportPembelianExcel(Request $request)
    {
        $dari   = $request->input('dari');
        $sampai = $request->input('sampai');

        $suffix   = ($dari && $sampai) ? "{$dari}_sd_{$sampai}" : now()->format('Ymd_His');
        $filename = "laporan-pembelian-kantin_{$suffix}.xlsx";

        return Excel::download(new \App\Exports\LaporanPembelianExport($dari, $sampai), $filename);
    }

    /**
     * Hapus riwayat transaksi kantin (hanya untuk Super Admin).
     */
    public function destroyTransaksi(TransaksiKantin $transaksi)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Aksi tidak diizinkan. Hanya Super Admin yang dapat menghapus data.');
        }

        $transaksi->delete();

        return redirect()->route('kantin.transaksi')
            ->with('success', 'Riwayat transaksi kantin berhasil dihapus.');
    }
}
