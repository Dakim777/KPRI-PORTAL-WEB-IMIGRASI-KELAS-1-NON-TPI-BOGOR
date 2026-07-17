@extends('layouts.app')

@section('title', 'Struk Transaksi #' . $transaksi->id)
@section('page-title', 'Struk Transaksi')

@section('content')
<div class="max-w-lg space-y-4">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-blue-700 px-6 py-5 text-white text-center">
            <h2 class="text-lg font-bold">PORTAL KOPERASI</h2>
            <p class="text-blue-200 text-xs mt-0.5">Struk Transaksi Kantin</p>
            <p class="text-blue-100 font-mono text-sm mt-2">#{{ str_pad($transaksi->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>

        <div class="px-6 py-5 space-y-4">
            <div class="grid grid-cols-2 text-sm gap-y-2">
                <span class="text-gray-500">Kasir</span>
                <span class="font-medium text-right">{{ $transaksi->kasir->name ?? '-' }}</span>
                <span class="text-gray-500">Waktu</span>
                <span class="font-medium text-right">{{ $transaksi->created_at->format('d/m/Y H:i:s') }}</span>
            </div>

            <hr class="border-dashed border-gray-200" />

            {{-- Line Items --}}
            <div class="space-y-2">
                @foreach($transaksi->details as $detail)
                <div class="flex justify-between text-sm">
                    <div>
                        <p class="font-medium text-gray-800">{{ $detail->produk->nama_produk ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $detail->qty }} × Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</p>
                    </div>
                    <span class="font-medium">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>

            <hr class="border-dashed border-gray-200" />

            {{-- Totals --}}
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total</span>
                    <span class="font-semibold">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Bayar</span>
                    <span class="font-semibold">Rp {{ number_format($transaksi->jumlah_bayar, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-base font-bold">
                    <span>Kembalian</span>
                    <span class="text-green-600">Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
                </div>
            </div>

            <p class="text-center text-xs text-gray-400 pt-2">Terima kasih telah berbelanja 🙏</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-3">
            <a href="{{ route('kantin.pos') }}"
               class="inline-flex items-center gap-2 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
               style="background:#0D1F3C" onmouseover="this.style.background='#1A3557'" onmouseout="this.style.background='#0D1F3C'">
                Transaksi Baru
            </a>
            <a href="{{ route('kantin.transaksi.print', $transaksi->id) }}"
               target="_blank"
               class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Struk
            </a>
            <a href="{{ route('kantin.transaksi') }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-semibold transition">
                Kembali ke Riwayat
            </a>
        </div>
        
        @if(auth()->user()->role === 'super_admin')
        <form method="POST" action="{{ route('kantin.transaksi.destroy', $transaksi->id) }}" onsubmit="return confirm('Hapus riwayat transaksi ini? Data tidak dapat dikembalikan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 bg-red-100 text-red-600 hover:bg-red-200 px-4 py-2 rounded-lg text-sm font-semibold transition">
                Hapus Transaksi
            </button>
        </form>
        @endif
    </div>
</div>
@endsection
