@extends('layouts.app')

@section('title', 'Riwayat Barang Masuk')
@section('page-title', 'Riwayat Pembelian & Barang Masuk')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Riwayat Pembelian</h2>
            <p class="text-sm text-gray-500 mt-1">Daftar transaksi barang masuk dari supplier.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('kantin.pembelian.create') }}" class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Input Barang Masuk
            </a>
            <a href="{{ route('kantin.laporan.pembelian.export') }}" class="bg-green-700 hover:bg-green-800 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Excel
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-start gap-2 shadow-sm">
        <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">ID</th>
                        <th class="px-6 py-4 text-left font-semibold">Waktu Masuk</th>
                        <th class="px-6 py-4 text-left font-semibold">Petugas / Kasir</th>
                        <th class="px-6 py-4 text-left font-semibold">Rincian Barang</th>
                        <th class="px-6 py-4 text-right font-semibold">Total Nilai Beli</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pembelians as $trx)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 font-mono text-gray-500 text-xs">#{{ str_pad($trx->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4 text-gray-600">
                            <span class="block text-gray-800 font-medium">{{ $trx->created_at->format('d/m/Y') }}</span>
                            <span class="text-xs">{{ $trx->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $trx->kasir->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <ul class="list-disc list-inside text-xs text-gray-600 space-y-1">
                                @foreach($trx->detailPembelians as $detail)
                                <li>
                                    <span class="font-medium text-gray-800">{{ $detail->produkKantin->nama_produk ?? 'Produk Terhapus' }}</span> 
                                    ({{ $detail->qty }} pcs @ Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }})
                                </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-800">
                            Rp {{ number_format($trx->total_pembelian, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                Belum ada data barang masuk.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pembelians->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $pembelians->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
