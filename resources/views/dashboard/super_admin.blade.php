@extends('layouts.app')

@section('title', 'Dashboard — Super Admin')
@section('page-title', 'Dashboard Super Admin (Pelacakan Paspor)')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Pengiriman Paspor</p>
            <p class="text-3xl font-bold text-indigo-700 mt-1">{{ number_format($stats['total_transaksi_paspor']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Pengiriman Hari Ini</p>
            <p class="text-3xl font-bold text-blue-700 mt-1">{{ number_format($stats['total_hari_ini']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Pengiriman Bulan Ini</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ number_format($stats['total_bulan_ini']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Omset Bulan Ini</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($stats['omset_bulan_ini'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Recent table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Transaksi Paspor Terbaru</h3>
            <a href="{{ route('paspor.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pemohon / Penerima</th>
                        <th class="px-4 py-3 text-left">No. Resi</th>
                        <th class="px-4 py-3 text-left">Kasir</th>
                        <th class="px-4 py-3 text-right">Total Biaya</th>
                        <th class="px-4 py-3 text-left">Waktu Input</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transaksi_paspor_terbaru as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $t->nama_penerima }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-blue-600">{{ $t->no_resi }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $t->kasir->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-emerald-700">Rp {{ number_format($t->total_pembayaran_pemohon, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $t->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada transaksi pengiriman paspor</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
