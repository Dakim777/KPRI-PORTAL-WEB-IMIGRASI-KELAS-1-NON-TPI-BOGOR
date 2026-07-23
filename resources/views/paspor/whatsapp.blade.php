@extends('layouts.app')
@section('title', 'Koneksi & Status WhatsApp')
@section('page-title', 'Koneksi & Gateway WhatsApp (Self-Hosted)')

@section('content')
<div class="space-y-8 max-w-6xl mx-auto">

    {{-- Pesan Sukses / Error --}}
    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 text-green-800">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-red-800">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Banner Status Utama --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div id="status-icon-container" class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors {{ ($status['status'] ?? '') === 'connected' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600' }}">
                <svg id="status-icon" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if(($status['status'] ?? '') === 'connected')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @endif
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 id="status-title" class="text-xl font-bold text-gray-800">
                        @if(($status['status'] ?? '') === 'connected')
                        WhatsApp Terhubung & Aktif
                        @elseif(($status['status'] ?? '') === 'offline')
                        Service Bot Nonaktif (Offline)
                        @else
                        Menunggu Pemindaian QR Code
                        @endif
                    </h3>
                    <span id="status-badge" class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider {{ ($status['status'] ?? '') === 'connected' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $status['status'] ?? 'disconnected' }}
                    </span>
                </div>
                <p id="status-subtitle" class="text-sm text-gray-500 mt-1">
                    @if(($status['status'] ?? '') === 'connected')
                    Nomor Terhubung: <span class="font-mono font-bold text-gray-800">+{{ $status['phone'] }}</span> (Siap mengirimkan notifikasi resi paspor otomatis).
                    @elseif(($status['status'] ?? '') === 'offline')
                    Pastikan service Node.js (<code class="bg-gray-100 px-1 rounded">wa-service/index.js</code>) sudah dijalankan di terminal port 3001.
                    @else
                    Silakan buka aplikasi WhatsApp di HP Anda lalu scan QR Code di bawah untuk menghubungkan nomor.
                    @endif
                </p>
            </div>
        </div>

        {{-- Tombol Logout / Reset Sesi --}}
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('paspor.whatsapp.logout') }}" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan sesi nomor WhatsApp saat ini? Anda akan diminta memindai QR Code baru setelah sesi direset.')">
                @csrf
                <button type="submit" id="btn-logout" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Log Out / Ganti Nomor</span>
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Kolom Kiri: Pemindai QR Code & Panduan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center">
            <h4 class="text-lg font-bold text-gray-800 mb-2">Scan QR Code Tautan</h4>
            <p class="text-xs text-gray-500 mb-6 max-w-sm">QR Code akan diperbarui secara otomatis setiap beberapa detik hingga WhatsApp di HP Anda terhubung.</p>

            {{-- Area Tampilan QR / Status --}}
            <div id="qr-box" class="w-72 h-72 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center p-4 relative overflow-hidden transition-all">
                @if(($status['status'] ?? '') === 'connected')
                <div id="qr-connected" class="flex flex-col items-center justify-center text-green-600">
                    <svg class="w-20 h-20 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-bold text-base text-gray-800">WhatsApp Sudah Aktif!</span>
                    <span class="text-xs text-gray-500 mt-1">Nomor: +{{ $status['phone'] ?? '-' }}</span>
                </div>
                @elseif(!empty($status['qr_data_url']))
                <img id="qr-image" src="{{ $status['qr_data_url'] }}" alt="WhatsApp QR Code" class="w-64 h-64 object-contain rounded-xl shadow-sm bg-white p-2">
                @else
                <div id="qr-loading" class="flex flex-col items-center justify-center text-slate-400">
                    <svg class="w-10 h-10 animate-spin mb-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span class="text-xs font-medium text-gray-600">Menyiapkan QR Code...</span>
                    <span class="text-[11px] text-gray-400 mt-1">Tunggu sebentar atau pastikan bot aktif</span>
                </div>
                @endif
            </div>

            {{-- Langkah-Langkah Scan --}}
            <div class="mt-6 w-full text-left bg-slate-50 rounded-xl p-4 border border-slate-100">
                <h5 class="text-xs font-bold uppercase text-slate-600 tracking-wider mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Cara Menghubungkan WhatsApp:</span>
                </h5>
                <ol class="text-xs text-slate-600 space-y-1.5 list-decimal list-inside">
                    <li>Buka aplikasi <strong>WhatsApp</strong> di HP Anda.</li>
                    <li>Ketuk ikon <strong>Titik Tiga</strong> di pojok kanan atas (Android) atau menu <strong>Pengaturan</strong> (iPhone).</li>
                    <li>Pilih menu <strong>Perangkat Tertaut (Linked Devices)</strong>.</li>
                    <li>Ketuk tombol <strong>Tautkan Perangkat (Link a Device)</strong>.</li>
                    <li>Arahkan kamera HP ke gambar QR Code di atas.</li>
                </ol>
            </div>
        </div>

        {{-- Kolom Kanan: Tes Kirim Pesan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 pb-4 border-b border-gray-100 mb-4">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <h4 class="text-lg font-bold text-gray-800">🧪 Tes Kirim Pesan WhatsApp</h4>
                </div>
                <p class="text-xs text-gray-500 mb-6">Uji coba pengiriman pesan langsung dari dasbor untuk memastikan koneksi bot berjalan lancar sebelum dihubungkan ke form transaksi paspor.</p>

                <form method="POST" action="{{ route('paspor.whatsapp.send-test') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nomor HP Tujuan (Contoh: 081234567890)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 font-mono text-sm">📱</span>
                            <input type="text" name="phone" required placeholder="08xxxxxxxxxx" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-mono focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Isi Pesan Tes</label>
                        <textarea name="message" rows="5" required placeholder="Tuliskan pesan pengujian di sini..." class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">*TES KONEKSI WHATSAPP KPRI IMIGRASI BOGOR*

Halo! Ini adalah pesan pengujian dari sistem otomatis Koperasi Kantor Imigrasi Bogor. Jika Anda menerima pesan ini, artinya bot WhatsApp telah berfungsi dengan baik. ✅</textarea>
                    </div>

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Kirim Pesan Tes Sekarang</span>
                    </button>
                </form>
            </div>

            <div class="mt-6 p-3 bg-indigo-50/60 rounded-xl border border-indigo-100/80 text-left">
                <p class="text-xs text-indigo-800 flex items-start gap-2">
                    <span class="text-sm">💡</span>
                    <span><strong>Tips Pengujian:</strong> Anda bisa menghubungkan nomor pribadi Anda saat ini untuk masa pengujian. Jika sudah yakin lancar, tinggal klik tombol <strong>Log Out / Ganti Nomor</strong> dan hubungkan nomor resmi Koperasi.</span>
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Script Polling AJAX Live Status & QR Code --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function checkWaStatus() {
            fetch("{{ route('paspor.whatsapp.status-ajax') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const qrBox = document.getElementById('qr-box');
                const statusTitle = document.getElementById('status-title');
                const statusBadge = document.getElementById('status-badge');
                const statusSubtitle = document.getElementById('status-subtitle');
                const statusIconContainer = document.getElementById('status-icon-container');

                if (data.status === 'connected') {
                    // Update header
                    statusTitle.textContent = 'WhatsApp Terhubung & Aktif';
                    statusBadge.textContent = 'connected';
                    statusBadge.className = 'px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-green-100 text-green-700';
                    statusSubtitle.innerHTML = `Nomor Terhubung: <span class="font-mono font-bold text-gray-800">+${data.phone || '-'}</span> (Siap mengirimkan notifikasi resi paspor otomatis).`;
                    statusIconContainer.className = 'w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors bg-green-100 text-green-600';

                    // Update kotak QR jadi sukses
                    qrBox.innerHTML = `
                        <div id="qr-connected" class="flex flex-col items-center justify-center text-green-600">
                            <svg class="w-20 h-20 mb-3 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-bold text-base text-gray-800">WhatsApp Sudah Aktif!</span>
                            <span class="text-xs text-gray-500 mt-1">Nomor: +${data.phone || '-'}</span>
                        </div>
                    `;
                } else if (data.status === 'offline') {
                    statusTitle.textContent = 'Service Bot Nonaktif (Offline)';
                    statusBadge.textContent = 'offline';
                    statusBadge.className = 'px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-red-100 text-red-700';
                    statusSubtitle.innerHTML = 'Pastikan service Node.js (<code class="bg-gray-100 px-1 rounded">wa-service/index.js</code>) sudah dijalankan di terminal port 3001.';
                    statusIconContainer.className = 'w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors bg-red-100 text-red-600';

                    qrBox.innerHTML = `
                        <div class="flex flex-col items-center justify-center text-red-400">
                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs font-medium text-gray-600">Service Bot Belum Nyala</span>
                            <span class="text-[11px] text-gray-400 mt-1">Jalankan node wa-service/index.js</span>
                        </div>
                    `;
                } else {
                    // Disconnected / Menunggu scan
                    statusTitle.textContent = 'Menunggu Pemindaian QR Code';
                    statusBadge.textContent = 'disconnected';
                    statusBadge.className = 'px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-amber-100 text-amber-700';
                    statusSubtitle.textContent = 'Silakan buka aplikasi WhatsApp di HP Anda lalu scan QR Code di bawah untuk menghubungkan nomor.';
                    statusIconContainer.className = 'w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors bg-amber-100 text-amber-600';

                    if (data.qr_data_url) {
                        const currentImg = document.getElementById('qr-image');
                        if (!currentImg || currentImg.src !== data.qr_data_url) {
                            qrBox.innerHTML = `<img id="qr-image" src="${data.qr_data_url}" alt="WhatsApp QR Code" class="w-64 h-64 object-contain rounded-xl shadow-sm bg-white p-2 animate-fade-in">`;
                        }
                    } else {
                        if (!document.getElementById('qr-loading')) {
                            qrBox.innerHTML = `
                                <div id="qr-loading" class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-10 h-10 animate-spin mb-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span class="text-xs font-medium text-gray-600">Menyiapkan QR Code...</span>
                                    <span class="text-[11px] text-gray-400 mt-1">Tunggu sebentar...</span>
                                </div>
                            `;
                        }
                    }
                }
            })
            .catch(err => console.log('Polling WA status error:', err));
        }

        // Lakukan pengecekan setiap 3 detik secara otomatis tanpa reload halaman
        setInterval(checkWaStatus, 3000);
    });
</script>
@endsection
