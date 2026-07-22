@extends('layouts.app')
@section('title', 'Tong Sampah')
@section('page-title', 'Tong Sampah (Arsip Terhapus)')

@section('content')
<div class="space-y-8">
    {{-- Transaksi Paspor --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 bg-red-50 flex items-center gap-2 rounded-t-xl">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <h3 class="font-semibold text-red-800">Transaksi Paspor Terhapus</h3>
        </div>
        <div class="overflow-x-auto rounded-b-xl" style="-webkit-overflow-scrolling: touch; transform: translateZ(0); will-change: scroll-position;">
            <table class="w-full min-w-[650px] text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Resi & Pemohon</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Dihapus Pada</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($trashedPaspor as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $p->nama_penerima }}</p>
                            <p class="text-xs text-gray-500 font-mono">{{ $p->no_resi }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $p->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 flex gap-2">
                            <form method="POST" action="{{ route('admin.trash.restore-paspor', $p->id) }}">
                                @csrf
                                <button class="px-3 py-1.5 bg-green-100 text-green-700 hover:bg-green-200 rounded text-xs font-semibold">Restore</button>
                            </form>
                            <form method="POST" action="{{ route('admin.trash.force-paspor', $p->id) }}" onsubmit="return confirm('Hapus permanen?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-semibold">Hapus Permanen</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Tong sampah kosong</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
