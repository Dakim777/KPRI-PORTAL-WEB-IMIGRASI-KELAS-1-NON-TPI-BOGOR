@extends('layouts.app')
@section('title', 'Edit Kategori')
@section('page-title', 'Edit Kategori: {{ $kategori->nama_kategori }}')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Edit Kategori</h3>
        </div>
        <div class="px-6 py-5">
            <form method="POST" action="{{ route('kantin.kategori.update', $kategori) }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kategori"
                           value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 @error('nama_kategori') border-red-400 @enderror" />
                    @error('nama_kategori')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Deskripsi (opsional)</label>
                    <input type="text" name="deskripsi"
                           value="{{ old('deskripsi', $kategori->deskripsi) }}"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 @error('deskripsi') border-red-400 @enderror" />
                    @error('deskripsi')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition"
                            style="background:#0D1F3C" onmouseover="this.style.background='#1A3557'" onmouseout="this.style.background='#0D1F3C'">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('kantin.kategori.index') }}"
                       class="px-5 py-2.5 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
