@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Edit: {{ $produk->nama_produk }}</h3>
        </div>
        <form method="POST" action="{{ route('kantin.produk.update', $produk) }}" class="px-6 py-6 space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="nama_produk" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" id="nama_produk" name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama_produk') border-red-400 @enderror" />
                @error('nama_produk')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1.5">Barcode / Kode Barang <span class="text-gray-400 font-normal">(opsional)</span></label>
                <div class="flex gap-2 items-start">
                    <div class="flex-1">
                        <input type="text" id="barcode" name="barcode" value="{{ old('barcode', $produk->barcode) }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 @error('barcode') border-red-400 @enderror" />
                        @error('barcode')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="w-40 shrink-0">
                        <x-barcode-scanner targetId="barcode" />
                    </div>
                </div>
            </div>
            <div>
                <label for="kategori_id" class="block text-sm font-medium text-gray-700 mb-1.5">Kategori Produk</label>
                <select id="kategori_id" name="kategori_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('kategori_id') border-red-400 @enderror">
                    <option value="">-- Pilih Kategori (Opsional) --</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ old('kategori_id', $produk->kategori_id) == $kat->id ? 'selected' : '' }}>
                            {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>
                @error('kategori_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="harga_beli" class="block text-sm font-medium text-gray-700 mb-1.5">Harga Beli / HPP (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" id="harga_beli" name="harga_beli" value="{{ old('harga_beli', $produk->harga_beli) }}" min="0" step="500" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('harga_beli') border-red-400 @enderror" />
                    @error('harga_beli')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="harga" class="block text-sm font-medium text-gray-700 mb-1.5">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" id="harga" name="harga" value="{{ old('harga', $produk->harga) }}" min="0" step="500" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('harga') border-red-400 @enderror" />
                    @error('harga')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="stok" class="block text-sm font-medium text-gray-700 mb-1.5">Stok <span class="text-red-500">*</span></label>
                <input type="number" id="stok" name="stok" value="{{ old('stok', $produk->stok) }}" min="0" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('stok') border-red-400 @enderror" />
                @error('stok')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition">
                    Perbarui
                </button>
                <a href="{{ route('kantin.produk.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
