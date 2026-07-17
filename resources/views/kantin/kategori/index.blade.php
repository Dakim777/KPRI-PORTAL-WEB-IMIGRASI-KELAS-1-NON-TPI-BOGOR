@extends('layouts.app')
@section('title', 'Kategori Produk Kantin')
@section('page-title', 'Kategori Produk Kantin')

@section('content')
<div class="space-y-5">

    {{-- Action Bar --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('kantin.kategori.create') }}"
           class="inline-flex items-center gap-2 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition"
           style="background:#0D1F3C" onmouseover="this.style.background='#1A3557'" onmouseout="this.style.background='#0D1F3C'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori
        </a>
        <a href="{{ route('kantin.produk.index') }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-semibold transition">
            Kembali ke Master Produk
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Nama Kategori</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-center">Jumlah Produk</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($kategoris as $k)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $k->id }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $k->nama_kategori }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $k->deskripsi ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                {{ $k->produks_count }} produk
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex items-center gap-3">
                                <a href="{{ route('kantin.kategori.edit', $k) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                                <form method="POST" action="{{ route('kantin.kategori.destroy', $k) }}"
                                      onsubmit="return confirm('Hapus kategori {{ $k->nama_kategori }}? Produk terkait tidak akan dihapus.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">
                            Belum ada kategori. Tambahkan kategori pertama Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kategoris->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $kategoris->links() }}</div>
        @endif
    </div>
</div>
@endsection
