@extends('layouts.app')

@section('title', 'Master Produk Kantin')
@section('page-title', 'Master Produk Kantin')

@section('content')
<div class="space-y-5">

    {{-- ── Action Bar ──────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('kantin.produk.create') }}"
           class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Produk
        </a>

        {{-- Trigger Import Panel --}}
        <button onclick="document.getElementById('importPanel').classList.toggle('hidden')"
            class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import Excel / CSV
        </button>

        {{-- Download Template --}}
        <a href="{{ route('kantin.produk.template') }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download Template
        </a>

        {{-- Filter Kategori --}}
        <form method="GET" action="{{ route('kantin.produk.index') }}" class="flex items-center gap-2 ml-auto">
            <select name="kategori_id" onchange="this.form.submit()" class="border border-gray-300 text-sm rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $kat)
                    <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                        {{ $kat->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- ── Import Panel (collapsible) ──────────────────────────────────── --}}
    <div id="importPanel" class="{{ session()->hasAny(['warning']) ? '' : 'hidden' }} bg-white rounded-xl shadow-sm border-2 border-green-200 overflow-hidden">
        <div class="bg-green-50 px-5 py-3 border-b border-green-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span class="font-semibold text-green-800 text-sm">Import Excel / CSV — Update Stok Massal</span>
            </div>
            <button onclick="document.getElementById('importPanel').classList.add('hidden')" class="text-green-400 hover:text-green-700 text-xs">Tutup</button>
        </div>

        <div class="px-5 py-5 grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Upload Form --}}
            <div>
                <form method="POST" action="{{ route('kantin.produk.import') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="file_import" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Pilih File Excel atau CSV <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="file_import" name="file_import"
                            accept=".xlsx,.xls,.csv"
                            class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-700 file:text-white file:font-semibold file:cursor-pointer hover:file:bg-green-800 border border-gray-300 rounded-lg py-1.5 focus:outline-none @error('file_import') border-red-400 @enderror" />
                        @error('file_import')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-gray-400">Format: .xlsx / .xls / .csv — Maks. 5 MB</p>
                    </div>
                    <button type="submit"
                        class="bg-green-700 hover:bg-green-800 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                        Mulai Import
                    </button>
                </form>
            </div>

            {{-- Panduan Format --}}
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 text-sm">
                <p class="font-semibold text-gray-700 mb-2">Panduan Format File</p>
                <p class="text-xs text-gray-500 mb-3">File harus memiliki 3 kolom header di baris pertama:</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-3 py-1.5 text-left font-bold">nama_produk</th>
                                <th class="px-3 py-1.5 text-left font-bold">harga</th>
                                <th class="px-3 py-1.5 text-left font-bold">stok</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr>
                                <td class="px-3 py-1.5">Nasi Goreng</td>
                                <td class="px-3 py-1.5">15000</td>
                                <td class="px-3 py-1.5">50</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-1.5">Es Teh Manis</td>
                                <td class="px-3 py-1.5">5000</td>
                                <td class="px-3 py-1.5">100</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 space-y-1 text-xs text-gray-500">
                    <p>— Nama yang <strong>sudah ada</strong> → harga & stok diperbarui</p>
                    <p>— Nama yang <strong>belum ada</strong> → produk baru ditambahkan</p>
                    <p>* Nilai stok adalah <strong>stok baru (bukan tambahan)</strong></p>
                    <p><a href="{{ route('kantin.produk.template') }}" class="text-green-700 hover:underline font-medium">Download template CSV</a> sebagai panduan</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Flash Messages ───────────────────────────────────────────────── --}}
    @if(session('warning'))
    <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm flex items-start gap-2">
        
        <span>{{ session('warning') }}</span>
    </div>
    @endif

    {{-- ── Produk Table ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Nama Produk</th>
                        <th class="px-4 py-3 text-left">Kode Barang</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-right">Harga Beli / HPP</th>
                        <th class="px-4 py-3 text-right">Harga Jual</th>
                        <th class="px-4 py-3 text-right">Stok</th>
                        <th class="px-4 py-3 text-left">Diperbarui</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($produks as $produk)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $produk->id }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $produk->nama_produk }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $produk->barcode ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($produk->kategori)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $produk->kategori->nama_kategori }}
                                </span>
                            @else
                                <span class="text-gray-400 italic text-xs">Tanpa Kategori</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500 text-xs">Rp {{ number_format($produk->harga_beli, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-800">Rp {{ number_format($produk->harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $produk->stok == 0 ? 'bg-red-100 text-red-700' : ($produk->stok <= 5 ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700') }}">
                                {{ $produk->stok }} pcs
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $produk->updated_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('kantin.produk.edit', $produk) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                                <span class="text-gray-300">|</span>
                                <form method="POST" action="{{ route('kantin.produk.destroy', $produk) }}"
                                      onsubmit="return confirm('Hapus produk {{ $produk->nama_produk }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            Belum ada produk. Tambahkan manual atau import dari Excel.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($produks->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $produks->links() }}</div>
        @endif
    </div>

</div>
@endsection
