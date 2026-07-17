<?php

namespace App\Http\Controllers;

use App\Models\KategoriKantin;
use App\Models\ProdukKantin;
use App\Models\TransaksiKantin;
use App\Models\TransaksiPaspor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Role-aware redirect from /dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        return match ($user->role) {
            'super_admin'  => redirect()->route('admin.dashboard'),
            'kasir_kantin' => redirect()->route('kantin.dashboard'),
            'kasir_paspor' => redirect()->route('paspor.dashboard'),
            default        => abort(403),
        };
    }

    /**
     * Super Admin Dashboard — overview of all modules.
     */
    public function superAdmin()
    {
        $stats = [
            'total_transaksi_kantin' => TransaksiKantin::count(),
            'total_transaksi_paspor' => TransaksiPaspor::count(),
            'total_produk'           => ProdukKantin::count(),
            'produk_habis'           => ProdukKantin::where('stok', 0)->count(),
        ];

        $transaksi_kantin_terbaru = TransaksiKantin::with('kasir')
            ->latest()
            ->take(5)
            ->get();

        $transaksi_paspor_terbaru = TransaksiPaspor::with('kasir')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.super_admin', compact(
            'stats',
            'transaksi_kantin_terbaru',
            'transaksi_paspor_terbaru'
        ));
    }

    /**
     * Kantin Dashboard.
     */
    public function kantin()
    {
        $stats = [
            'total_transaksi_hari_ini' => TransaksiKantin::whereDate('created_at', today())->count(),
            'omset_hari_ini'           => TransaksiKantin::whereDate('created_at', today())->sum('total_harga'),
            'total_produk'             => ProdukKantin::count(),
            'stok_menipis'             => ProdukKantin::where('stok', '<=', 5)->where('stok', '>', 0)->count(),
            'stok_habis'               => ProdukKantin::where('stok', 0)->count(),
        ];

        // Produk dengan stok = 0 untuk notifikasi
        $produk_habis = ProdukKantin::where('stok', 0)->orderBy('nama_produk')->get();
        
        // Produk dengan stok menipis (1-5) untuk notifikasi
        $produk_menipis = ProdukKantin::where('stok', '<=', 5)->where('stok', '>', 0)->orderBy('nama_produk')->get();

        $transaksi_terbaru = TransaksiKantin::with(['details.produk', 'kasir'])
            ->whereDate('created_at', today())
            ->latest()
            ->take(10)
            ->get();

        // ── Sales Chart: last 7 days ────────────────────────────────────────
        $chartLabels  = [];
        $chartDataset = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $chartLabels[]  = now()->subDays($i)->translatedFormat('d M');
            $chartDataset[] = (int) TransaksiKantin::whereDate('created_at', $date)->sum('total_harga');
        }

        // ── Laporan Rekap Bulanan (bulan ini) ──────────────────────────────
        $rekapBulanIni = [
            'total_transaksi' => TransaksiKantin::whereMonth('created_at', now()->month)
                                               ->whereYear('created_at', now()->year)->count(),
            'total_omset'     => TransaksiKantin::whereMonth('created_at', now()->month)
                                               ->whereYear('created_at', now()->year)->sum('total_harga'),
            'total_produk_terjual' => \App\Models\DetailKantin::whereHas('transaksi', function($q) {
                                            $q->whereMonth('created_at', now()->month)
                                              ->whereYear('created_at', now()->year);
                                        })->sum('qty'),
        ];

        return view('dashboard.kantin', compact(
            'stats',
            'transaksi_terbaru',
            'chartLabels',
            'chartDataset',
            'produk_habis',
            'produk_menipis',
            'rekapBulanIni'
        ));
    }

    /**
     * Paspor Dashboard.
     */
    public function paspor()
    {
        $stats = [
            'total_hari_ini'  => TransaksiPaspor::whereDate('created_at', today())->count(),
            'total_bulan_ini' => TransaksiPaspor::whereMonth('created_at', now()->month)
                                               ->whereYear('created_at', now()->year)->count(),
            'omset_hari_ini'  => TransaksiPaspor::whereDate('tanggal_transaksi', today())->sum('total_pembayaran_pemohon'),
            'omset_bulan_ini' => TransaksiPaspor::whereMonth('tanggal_transaksi', now()->month)
                                               ->whereYear('tanggal_transaksi', now()->year)->sum('total_pembayaran_pemohon'),
        ];

        $transaksi_terbaru = TransaksiPaspor::with('kasir')->latest()->take(10)->get();

        // ── Chart: 7 hari terakhir (jumlah pengiriman per hari) ───────────
        $chartLabels  = [];
        $chartDataset = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $chartLabels[]  = now()->subDays($i)->translatedFormat('d M');
            $chartDataset[] = (int) TransaksiPaspor::whereDate('created_at', $date)->count();
        }

        // ── Status breakdown (bulan ini) ───────────────────────────────────
        $statusBreakdown = TransaksiPaspor::selectRaw('status_pengiriman, COUNT(*) as total')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('status_pengiriman')
            ->pluck('total', 'status_pengiriman');

        // ── Rekap bulanan ──────────────────────────────────────────────────
        $rekapBulanIni = [
            'total_transaksi' => $stats['total_bulan_ini'],
            'total_omset'     => $stats['omset_bulan_ini'],
            'diproses'        => $statusBreakdown['Diproses'] ?? 0,
            'dikirim'         => $statusBreakdown['Dikirim'] ?? 0,
            'selesai'         => $statusBreakdown['Selesai'] ?? 0,
            'kendala'         => $statusBreakdown['Kendala'] ?? 0,
        ];

        return view('dashboard.paspor', compact(
            'stats',
            'transaksi_terbaru',
            'chartLabels',
            'chartDataset',
            'rekapBulanIni'
        ));
    }
}
