<?php

namespace App\Http\Controllers;

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
            'total_transaksi_paspor' => TransaksiPaspor::count(),
            'total_hari_ini'         => TransaksiPaspor::whereDate('created_at', today())->count(),
            'total_bulan_ini'        => TransaksiPaspor::whereMonth('created_at', now()->month)
                                            ->whereYear('created_at', now()->year)->count(),
            'omset_bulan_ini'        => TransaksiPaspor::whereMonth('tanggal_transaksi', now()->month)
                                            ->whereYear('tanggal_transaksi', now()->year)->sum('total_pembayaran_pemohon'),
        ];

        $transaksi_paspor_terbaru = TransaksiPaspor::with('kasir')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.super_admin', compact(
            'stats',
            'transaksi_paspor_terbaru'
        ));
    }

    /**

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
