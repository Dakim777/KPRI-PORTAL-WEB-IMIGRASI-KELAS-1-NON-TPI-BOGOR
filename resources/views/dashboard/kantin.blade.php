@extends('layouts.app')

@section('title', 'Dashboard Kantin')
@section('page-title', 'Dashboard Kantin')

@section('content')
<div class="space-y-6">

    {{-- ── Stat Cards ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Transaksi Hari Ini</p>
            <p class="text-3xl font-bold mt-2" style="color:#0D1F3C">{{ $stats['total_transaksi_hari_ini'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Omset Hari Ini</p>
            <p class="text-xl font-bold text-green-700 mt-2">Rp {{ number_format($stats['omset_hari_ini'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Produk</p>
            <p class="text-3xl font-bold text-indigo-700 mt-2">{{ $stats['total_produk'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Stok Menipis (≤5)</p>
            <p class="text-3xl font-bold text-orange-600 mt-2">{{ $stats['stok_menipis'] }}</p>
        </div>
    </div>

    {{-- ── Action Buttons ───────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('kantin.pos') }}"
           class="inline-flex items-center gap-2 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition"
           style="background:#0D1F3C" onmouseover="this.style.background='#1A3557'" onmouseout="this.style.background='#0D1F3C'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buka Kasir / POS
        </a>
        <a href="{{ route('kantin.produk.index') }}"
           class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
            Master Produk
        </a>
        {{-- ── Export Laporan ────────────────────────────────────────── --}}
        <div class="ml-auto flex items-center gap-2">
            <form method="GET" action="{{ route('kantin.laporan.export') }}" class="flex items-center gap-2">
                <input type="date" name="dari"
                       value="{{ request('dari', now()->startOfMonth()->toDateString()) }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2" />
                <span class="text-gray-400 text-xs">s/d</span>
                <input type="date" name="sampai"
                       value="{{ request('sampai', now()->toDateString()) }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2" />
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-xs font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel
                </button>
            </form>
        </div>
    </div>
    {{-- ── Notifikasi Stok Habis ────────────────────────────────────────── --}}
    @if($stats['stok_habis'] > 0)
    <div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div class="flex-1">
            <p class="text-sm font-semibold text-red-800">
                {{ $stats['stok_habis'] }} produk stok habis
            </p>
            <div class="mt-1.5 flex flex-wrap gap-1.5">
                @foreach($produk_habis as $p)
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 font-medium">{{ $p->nama_produk }}</span>
                @endforeach
            </div>
            <a href="{{ route('kantin.produk.index', ['stok_filter' => 'habis']) }}"
               class="inline-block mt-2 text-xs text-red-700 underline font-medium">Kelola stok sekarang</a>
        </div>
    </div>
    @endif

    {{-- ── Notifikasi Stok Menipis ──────────────────────────────────────── --}}
    @if($stats['stok_menipis'] > 0)
    <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="flex-1">
            <p class="text-sm font-semibold text-orange-800">
                {{ $stats['stok_menipis'] }} produk stok menipis (≤ 5)
            </p>
            <div class="mt-1.5 flex flex-wrap gap-1.5">
                @foreach($produk_menipis as $p)
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-orange-100 text-orange-700 font-medium">{{ $p->nama_produk }} (Sisa: {{ $p->stok }})</span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── Rekap Bulanan Kantin ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Rekap Bulan Ini — {{ now()->translatedFormat('F Y') }}</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold" style="color:#0D1F3C">{{ $rekapBulanIni['total_transaksi'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Transaksi</p>
            </div>
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-lg font-bold text-green-700">Rp {{ number_format($rekapBulanIni['total_omset'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Omset</p>
            </div>
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-700">{{ $rekapBulanIni['total_produk_terjual'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Item Terjual</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-semibold text-gray-800">Grafik Penjualan</h3>
                <p class="text-xs text-gray-400 mt-0.5">7 hari terakhir</p>
            </div>
            <span class="text-xs text-gray-400">Total Omset (Rp)</span>
        </div>
        <div style="position:relative; height:280px;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    {{-- ── Transaksi Hari Ini ───────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Transaksi Hari Ini</h3>
            <a href="{{ route('kantin.transaksi') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Kasir</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Bayar</th>
                        <th class="px-4 py-3 text-right">Kembalian</th>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transaksi_terbaru as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $t->id }}</td>
                        <td class="px-4 py-3">{{ $t->kasir->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($t->jumlah_bayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-green-600">Rp {{ number_format($t->kembalian, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $t->created_at->format('H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('kantin.transaksi.show', $t->id) }}"
                                   class="text-blue-600 hover:underline text-xs font-medium">Detail</a>
                                <a href="{{ route('kantin.transaksi.print', $t->id) }}"
                                   target="_blank"
                                   class="text-gray-500 hover:text-gray-700 text-xs font-medium border-l border-gray-200 pl-2">Cetak</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi hari ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels  = @json($chartLabels);
    const dataset = @json($chartDataset);

    const formatRupiah = (value) => {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
    };

    const ctx = document.getElementById('salesChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Omset Penjualan',
                data: dataset,
                fill: true,
                tension: 0.4,
                borderColor: '#0D1F3C',
                borderWidth: 2.5,
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const { ctx: c, chartArea } = chart;
                    if (!chartArea) return 'rgba(13,31,60,0.05)';
                    const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, 'rgba(13,31,60,0.18)');
                    gradient.addColorStop(1, 'rgba(13,31,60,0.01)');
                    return gradient;
                },
                pointBackgroundColor: '#0D1F3C',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0D1F3C',
                    titleColor: '#7A9BB8',
                    bodyColor: '#fff',
                    padding: 10,
                    callbacks: {
                        label: (ctx) => '  ' + formatRupiah(ctx.parsed.y),
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#9ca3af', font: { size: 11 } },
                    border: { color: '#e5e7eb' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        color: '#9ca3af',
                        font: { size: 11 },
                        callback: (value) => {
                            if (value >= 1000000) return 'Rp ' + (value/1000000).toFixed(1) + 'Jt';
                            if (value >= 1000)    return 'Rp ' + (value/1000).toFixed(0) + 'Rb';
                            return 'Rp ' + value;
                        }
                    },
                    border: { color: '#e5e7eb' }
                }
            }
        }
    });
})();
</script>
@endpush
