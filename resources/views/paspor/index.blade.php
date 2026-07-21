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
                                @if($t->no_resi)
                                <span class="text-gray-300">|</span>
                                <button type="button" onclick="openTrackingModal({{ $t->id }}, '{{ $t->no_resi }}', '{{ $t->nama_penerima }}')"
                                        class="text-indigo-600 hover:text-indigo-800 font-medium text-xs whitespace-nowrap inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                    <span>Lacak</span>
                                </button>
                                <span class="text-gray-300">|</span>
                                <button type="button" onclick="sendWaResi({{ $t->id }}, '{{ $t->nama_penerima }}')"
                                        class="text-emerald-600 hover:text-emerald-800 font-medium text-xs whitespace-nowrap inline-flex items-center gap-1"
                                        title="Kirim notifikasi resi & status ke WhatsApp pemohon">
                                    <span>Kirim WA</span>
                                </button>
                                @endif
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

    {{-- ── Modal Lacak Resi (BinderByte Tracking Timeline) ──────────────────── --}}
    <div id="trackingModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
            <div class="bg-gradient-to-r from-blue-900 to-indigo-800 px-6 py-4 flex items-center justify-between text-white">
                <div>
                    <h3 class="font-bold text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        <span>Lacak Resi Pengiriman</span>
                    </h3>
                    <p id="modalResiSubtitle" class="text-xs text-blue-200 mt-0.5">Memuat data pengiriman...</p>
                </div>
                <button type="button" onclick="closeTrackingModal()" class="text-white/80 hover:text-white rounded-lg p-1.5 hover:bg-white/10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6">
                {{-- Loading Spinner --}}
                <div id="trackingLoading" class="py-12 text-center text-gray-500">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-medium animate-pulse">Memuat data pelacakan resi...</p>
                </div>

                {{-- Error View --}}
                <div id="trackingError" class="hidden py-8 text-center text-red-600">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3 text-red-600 text-xl font-bold">!</div>
                    <p id="trackingErrorMessage" class="text-sm font-semibold mb-1">Gagal melacak resi</p>
                    <p class="text-xs text-gray-500">Pastikan nomor resi valid atau API Key sudah terpasang.</p>
                </div>

                {{-- Success Timeline View --}}
                <div id="trackingContent" class="hidden space-y-5">
                    <div id="mockBadgeContainer" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        <div>
                            <p class="font-bold">Mode Simulasi (Mock Data)</p>
                            <p class="text-amber-700">Pasang `BINDERBYTE_API_KEY` di `.env` untuk melihat tracking live dari ekspedisi.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex items-center justify-between text-sm">
                        <div>
                            <p class="text-xs text-gray-400 uppercase font-bold">Status Saat Ini</p>
                            <p id="trackingStatusBadge" class="font-extrabold text-blue-700 text-base mt-0.5">ON PROCESS</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400 uppercase font-bold">Kurir</p>
                            <p id="trackingCourier" class="font-bold text-gray-800 text-base mt-0.5">POS</p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Riwayat Perjalanan (Manifest)</h4>
                        <div id="trackingTimeline" class="relative border-l-2 border-blue-600 ml-3 space-y-6 pl-5 py-1">
                            {{-- Injected via JS --}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-3.5 border-t border-gray-100 flex justify-end">
                <button type="button" onclick="closeTrackingModal()" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-xl text-sm font-semibold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
    function openTrackingModal(id, resi, nama) {
        document.getElementById('trackingModal').classList.remove('hidden');
        document.getElementById('modalResiSubtitle').textContent = `Resi: ${resi} (${nama})`;
        
        document.getElementById('trackingLoading').classList.remove('hidden');
        document.getElementById('trackingError').classList.add('hidden');
        document.getElementById('trackingContent').classList.add('hidden');

        fetch(`/paspor/${id}/track`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('trackingLoading').classList.add('hidden');
                
                if (data.status === 'error' || (typeof data.status === 'number' && data.status !== 200)) {
                    document.getElementById('trackingError').classList.remove('hidden');
                    document.getElementById('trackingErrorMessage').textContent = data.message || 'Data resi tidak ditemukan di ekspedisi.';
                    return;
                }

                document.getElementById('trackingContent').classList.remove('hidden');

                if (data.is_mock) {
                    document.getElementById('mockBadgeContainer').classList.remove('hidden');
                } else {
                    document.getElementById('mockBadgeContainer').classList.add('hidden');
                }

                const summary = data.data.summary || {};
                const history = data.data.history || [];

                document.getElementById('trackingStatusBadge').textContent = summary.status || 'PROSES';
                document.getElementById('trackingCourier').textContent = summary.courier || 'POS';

                const timelineEl = document.getElementById('trackingTimeline');
                timelineEl.innerHTML = '';

                if (history.length === 0) {
                    timelineEl.innerHTML = '<p class="text-xs text-gray-500 italic">Belum ada riwayat pergerakan paket.</p>';
                } else {
                    history.forEach((item, index) => {
                        const isFirst = index === 0;
                        timelineEl.innerHTML += `
                            <div class="relative">
                                <div class="absolute -left-[27px] top-1.5 w-3.5 h-3.5 rounded-full border-2 border-white ${isFirst ? 'bg-blue-600 ring-4 ring-blue-100' : 'bg-gray-300'}"></div>
                                <p class="text-xs text-gray-400 font-mono">${item.date || '-'}</p>
                                <p class="text-sm font-semibold text-gray-800 mt-0.5">${item.desc || '-'}</p>
                                ${item.location ? `<p class="text-xs text-blue-600 mt-0.5"><span class="font-semibold">Lokasi:</span> ${item.location}</p>` : ''}
                            </div>
                        `;
                    });
                }
            })
            .catch(err => {
                document.getElementById('trackingLoading').classList.add('hidden');
                document.getElementById('trackingError').classList.remove('hidden');
                document.getElementById('trackingErrorMessage').textContent = 'Terjadi kesalahan koneksi ke server.';
            });
    }

    function closeTrackingModal() {
        document.getElementById('trackingModal').classList.add('hidden');
    }

    function sendWaResi(id, nama) {
        if (!confirm(`Kirim notifikasi status & resi paspor ke WhatsApp pemohon (${nama}) sekarang?`)) {
            return;
        }

        fetch(`/paspor/${id}/send-wa`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.status === 'success') {
                alert(body.message);
            } else {
                alert('Gagal: ' + (body.message || 'Terjadi kesalahan saat mengirim WhatsApp. Pastikan bot sudah aktif di panel Admin.'));
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan koneksi ke server bot.');
            console.error(err);
        });
    }
    </script>

</div>
@endsection
