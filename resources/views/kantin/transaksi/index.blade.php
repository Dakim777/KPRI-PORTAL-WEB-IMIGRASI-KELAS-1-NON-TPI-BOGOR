@extends('layouts.app')

@section('title', 'Riwayat Transaksi Kantin')
@section('page-title', 'Riwayat Transaksi Kantin')

@section('content')
<div class="space-y-5">
    <form method="GET" action="{{ route('kantin.transaksi') }}" class="flex gap-2 flex-wrap">
        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Filter</button>
        @if(request('tanggal'))
        <a href="{{ route('kantin.transaksi') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
                    @forelse($transaksis as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400">{{ $t->id }}</td>
                        <td class="px-4 py-3">{{ $t->kasir->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($t->jumlah_bayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-green-600">Rp {{ number_format($t->kembalian, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $t->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('kantin.transaksi.show', $t->id) }}" class="text-blue-600 hover:underline text-xs font-medium">Detail</a>
                                @if(auth()->user()->role === 'super_admin')
                                <form method="POST" action="{{ route('kantin.transaksi.destroy', $t->id) }}" onsubmit="return confirm('Hapus riwayat transaksi ini? Data tidak dapat dikembalikan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium border-l border-gray-200 pl-2">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Belum ada transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transaksis->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $transaksis->links() }}</div>
        @endif
    </div>
</div>
@endsection
