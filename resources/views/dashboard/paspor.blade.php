@extends('layouts.app')

@section('title', 'Dashboard Paspor')
@section('page-title', 'Dashboard Paspor')

@section('content')
<div class="space-y-6">

    {{-- ── Stat Cards ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Transaksi Hari Ini</p>
            <p class="text-3xl font-bold mt-2" style="color:#0D1F3C">{{ $stats['total_hari_ini'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Transaksi Bulan Ini</p>
            <p class="text-3xl font-bold text-indigo-700 mt-2">{{ $stats['total_bulan_ini'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Omset Hari Ini</p>
            <p class="text-xl font-bold text-green-700 mt-2">Rp {{ number_format($stats['omset_hari_ini'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Omset Bulan Ini</p>
            <p class="text-xl font-bold text-blue-600 mt-2">Rp {{ number_format($stats['omset_bulan_ini'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- ── Action Buttons + Export ──────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('paspor.create') }}"
           class="inline-flex items-center gap-2 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition"
           style="background:#0D1F3C" onmouseover="this.style.background='#1A3557'" onmouseout="this.style.background='#0D1F3C'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Transaksi Baru
        </a>
        <a href="{{ route('paspor.index') }}"
           class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
            Lihat Semua Data
        </a>

        @if(auth()->user()->role === 'super_admin')
        {{-- Export Laporan Paspor (Hanya Admin) --}}
        <div class="ml-auto flex items-center gap-2">
            <form method="GET" action="{{ route('paspor.export.kompleks') }}" data-is-download="true" class="flex items-center gap-2">
                <input type="date" name="tanggal_dari"
                       value="{{ request('tanggal_dari', now()->startOfMonth()->toDateString()) }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2" />
                <span class="text-gray-400 text-xs">s/d</span>
                <input type="date" name="tanggal_sampai"
                       value="{{ request('tanggal_sampai', now()->toDateString()) }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2" />
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-xs font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel (Kompleks)
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- ── Rekap Bulanan Paspor ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Rekap Bulan Ini — {{ now()->translatedFormat('F Y') }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold" style="color:#0D1F3C">{{ $rekapBulanIni['total_transaksi'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Pengiriman</p>
            </div>
            <div class="text-center p-3 rounded-lg" style="background:#EEF2F7">
                <p class="text-lg font-bold text-yellow-600">{{ $rekapBulanIni['diproses'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Diproses</p>
            </div>
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <p class="text-lg font-bold text-blue-700">{{ $rekapBulanIni['dikirim'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Dikirim</p>
            </div>
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <p class="text-lg font-bold text-green-700">{{ $rekapBulanIni['selesai'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Selesai</p>
            </div>
        </div>
        @if($rekapBulanIni['kendala'] > 0)
        <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <strong>{{ $rekapBulanIni['kendala'] }} pengiriman</strong> &nbsp;berstatus Kendala bulan ini.
        </div>
        @endif
    </div>

    {{-- ── Grafik 7 Hari Terakhir ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-semibold text-gray-800">Grafik Pengiriman</h3>
                <p class="text-xs text-gray-400 mt-0.5">7 hari terakhir (jumlah pengiriman per hari)</p>
            </div>
        </div>
        <div style="position:relative; height:260px;">
            <canvas id="pasporChart"></canvas>
        </div>
    </div>

    {{-- ── Transaksi Terbaru ────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Transaksi Terbaru</h3>
            <a href="{{ route('paspor.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>
        <div class="overflow-x-auto rounded-b-xl" style="-webkit-overflow-scrolling: touch; transform: translateZ(0); will-change: scroll-position;">
            <table class="w-full min-w-[750px] text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Pemohon</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">No. Resi</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 text-right whitespace-nowrap">Total</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Waktu</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transaksi_terbaru as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $t->nama_penerima }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $t->no_resi }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColor = match($t->status_pengiriman) {
                                    'Selesai'  => 'bg-green-100 text-green-700',
                                    'Dikirim'  => 'bg-blue-100 text-blue-700',
                                    'Kendala'  => 'bg-red-100 text-red-700',
                                    default    => 'bg-yellow-100 text-yellow-700',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColor }}">
                                {{ $t->status_pengiriman }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($t->total_pembayaran_pemohon, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('paspor.show', $t->id) }}" class="text-blue-600 hover:underline text-xs">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi</td></tr>
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

    const ctx = document.getElementById('pasporChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Pengiriman',
                data: dataset,
                backgroundColor: 'rgba(13,31,60,0.75)',
                borderColor: '#0D1F3C',
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0D1F3C',
                    titleColor: '#7A9BB8',
                    bodyColor: '#fff',
                    padding: 10,
                    callbacks: {
                        label: (ctx) => '  ' + ctx.parsed.y + ' pengiriman',
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
                        stepSize: 1,
                        callback: (v) => v % 1 === 0 ? v : ''
                    },
                    border: { color: '#e5e7eb' }
                }
            }
        }
    });
})();
</script>
@endpush
