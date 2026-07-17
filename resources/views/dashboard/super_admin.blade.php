@extends('layouts.app')

@section('title', 'Dashboard — Super Admin')
@section('page-title', 'Dashboard Super Admin')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Transaksi Kantin</p>
            <p class="text-3xl font-bold text-blue-700 mt-1">{{ number_format($stats['total_transaksi_kantin']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Transaksi Paspor</p>
            <p class="text-3xl font-bold text-indigo-700 mt-1">{{ number_format($stats['total_transaksi_paspor']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Produk</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ number_format($stats['total_produk']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Produk Habis</p>
            <p class="text-3xl font-bold text-red-600 mt-1">{{ number_format($stats['produk_habis']) }}</p>
        </div>
    </div>

    {{-- Recent tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Kantin Recent --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Transaksi Kantin Terbaru</h3>
                <a href="{{ route('kantin.transaksi') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Kasir</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-left">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($transaksi_kantin_terbaru as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $t->kasir->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $t->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paspor Recent --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Transaksi Paspor Terbaru</h3>
                <a href="{{ route('paspor.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Pemohon</th>
                            <th class="px-4 py-3 text-left">No. Resi</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-left">Waktu Input</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($transaksi_paspor_terbaru as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $t->nama_penerima }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $t->no_resi }}</td>
                            <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($t->total_pembayaran_pemohon, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $t->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
