@extends('layouts.app')

@section('title', 'Data Pengiriman Paspor')
@section('page-title', 'Data Pengiriman Paspor')

@section('content')
<div class="space-y-5">

    {{-- ── Quick Stat Widgets ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Transaksi Hari Ini</p>
            <p class="text-3xl font-bold text-blue-700 mt-1">{{ $statHariIni }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Bulan Ini</p>
            <p class="text-3xl font-bold text-indigo-700 mt-1">{{ $statBulanIni }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Pemasukan Hari Ini</p>
            <p class="text-xl font-bold text-green-700 mt-1">Rp {{ number_format($omsetHariIni, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Filter (Grand)</p>
            <p class="text-xl font-bold text-orange-600 mt-1">Rp {{ number_format($summary->grand_total ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- ── Filter Summary ─────────────── --}}
    @if($summary && $summary->total_pengiriman > 0 && request()->hasAny(['search','status','tanggal','tanggal_dari','tanggal_sampai','bulan','tahun']))
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 grid grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-blue-500 text-xs">Total Pengiriman (Filter)</p>
            <p class="font-bold text-blue-800 text-lg">{{ number_format($summary->total_pengiriman) }}</p>
        </div>
        <div>
            <p class="text-blue-500 text-xs">Grand Total Pembayaran</p>
            <p class="font-bold text-blue-900 text-lg">Rp {{ number_format($summary->grand_total, 0, ',', '.') }}</p>
        </div>
    </div>
    @endif

    {{-- ── Top Action Bar ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('paspor.create') }}"
           class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Transaksi Baru
        </a>


    </div>

    {{-- ── Search & Filter Form ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <form method="GET" action="{{ route('paspor.index') }}" id="filterForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

                <div class="xl:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama penerima, ID transaksi, atau no. resi..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status Pengiriman</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Semua Status --</option>
                        @foreach(\App\Models\TransaksiPaspor::statusList() as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Transaksi (dari)</label>
                    <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Transaksi (sampai)</label>
                    <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                    <select name="bulan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Bulan --</option>
                        @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                    <select name="tahun" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Tahun --</option>
                        @foreach(range(now()->year, now()->year - 4) as $y)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['search','status','tanggal','tanggal_dari','tanggal_sampai','bulan','tahun']))
                <a href="{{ route('paspor.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Reset Filter
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Data Table ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tgl Transaksi</th>
                        <th class="px-4 py-3 text-left">ID Transaksi & Struk</th>
                        <th class="px-4 py-3 text-left">Nama Penerima</th>
                        <th class="px-4 py-3 text-left">No. Resi</th>
                        <th class="px-4 py-3 text-center">Jml Paspor</th>
                        <th class="px-4 py-3 text-right">Total Pembayaran</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transaksis as $t)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-xs">{{ $t->tanggal_transaksi?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-blue-700 whitespace-nowrap">{{ $t->id_transaksi }}</div>
                            <div class="text-xs text-gray-500">Struk: {{ $t->no_struk }}</div>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $t->nama_penerima }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600 whitespace-nowrap">{{ $t->no_resi ?? '-' }}</td>
                        <td class="px-4 py-3 text-center font-bold text-gray-700">{{ $t->jumlah_paspor }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-green-600 whitespace-nowrap">
                            Rp {{ number_format($t->total_pembayaran_pemohon, 0, ',', '.') }}
                        </td>

                        {{-- Status badge + quick-update dropdown --}}
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="{{ route('paspor.update-status', $t->id) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status_pengiriman" onchange="this.form.submit()"
                                    class="text-xs font-semibold px-2 py-1 rounded-full border-0 cursor-pointer
                                    {{ $t->statusColorClass() }} focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach(\App\Models\TransaksiPaspor::statusList() as $s)
                                    <option value="{{ $s }}" {{ $t->status_pengiriman === $s ? 'selected' : '' }} class="bg-white text-gray-800">
                                        {{ ucfirst($s) }}
                                    </option>
                                    @endforeach
                                </select>
                            </form>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('paspor.edit', $t->id) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium text-xs whitespace-nowrap">
                                    Detail/Edit
                                </a>
                                <span class="text-gray-300">|</span>
                                <a href="{{ route('paspor.print', $t->id) }}" target="_blank"
                                   class="text-green-600 hover:text-green-800 font-medium text-xs whitespace-nowrap">
                                    Struk
                                </a>
                                @if(auth()->user()->role === 'super_admin')
                                <form method="POST" action="{{ route('paspor.destroy', $t->id) }}" onsubmit="return confirm('Hapus data pengiriman ini? Data tidak dapat dikembalikan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs border-l border-gray-200 pl-2">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Tidak ada data yang cocok dengan filter
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                @if($transaksis->count() > 0)
                <tfoot class="bg-gray-50 text-xs font-semibold text-gray-700 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-right">
                            Total Halaman Ini:
                        </td>
                        <td class="px-4 py-3 text-right text-green-700">
                            Rp {{ number_format($transaksis->sum('total_pembayaran_pemohon'), 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($transaksis->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $transaksis->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
