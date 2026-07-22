<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-fit=cover, width=device-width, initial-scale=1.0">
    <title>Lacak Pengiriman Paspor — Portal KPRI Kantor Imigrasi Kelas I Bogor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        imigrasi: {
                            blue: '#0a2558',
                            dark: '#061738',
                            gold: '#d4af37',
                            light: '#f0f4f9'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-900 font-sans min-h-screen flex flex-col text-slate-100 selection:bg-blue-600 selection:text-white relative overflow-x-hidden">

    {{-- Background Glow --}}
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/3 -right-40 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

    {{-- Top Navbar --}}
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-700 flex items-center justify-center shadow-lg shadow-blue-500/20 text-white font-extrabold text-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h1 class="font-bold text-base sm:text-lg tracking-tight text-white leading-snug">
                        KPRI KANTOR IMIGRASI BOGOR
                    </h1>
                    <p class="text-xs text-blue-400 font-medium">Layanan Pengiriman Paspor & Pelacakan Resi</p>
                </div>
            </div>
            <div>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/60 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition shadow-sm hover:border-slate-600">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Login Petugas</span>
                </a>
            </div>
        </div>
    </header>

    {{-- Hero & Search Box --}}
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-10 sm:py-16 flex flex-col justify-center">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="inline-block px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/30 text-blue-400 text-xs font-semibold uppercase tracking-widest mb-4">
                Pelacakan Realtime
            </span>
            <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white mb-4 leading-tight">
                Lacak Status Paspor & Resi Ekspedisi Anda
            </h2>
            <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                Pantau proses penyerahan paspor Anda dari loket Koperasi Imigrasi Bogor hingga tiba dengan aman di alamat pengiriman Anda secara transparan.
            </p>
        </div>

        {{-- Search Card --}}
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 sm:p-6 shadow-2xl backdrop-blur-xl relative">
            <form id="trackForm" onsubmit="handlePublicTrack(event)" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" id="trackingQuery" required value="{{ request()->get('q') }}" placeholder="Masukkan Nomor Resi (misal: POS123...) atau ID Transaksi..." 
                           class="w-full bg-slate-900/90 border border-slate-700 rounded-xl pl-12 pr-4 py-3.5 text-sm sm:text-base text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono uppercase transition">
                </div>
                <button type="submit" id="trackButton" 
                        class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold px-7 py-3.5 rounded-xl transition shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2 text-sm sm:text-base">
                    <span>Lacak Sekarang</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                <span class="font-semibold text-slate-300">Contoh pencarian:</span>
                <button type="button" onclick="quickFill('POS99887766')" class="hover:text-blue-400 underline font-mono">POS99887766</button>
                <span>•</span>
                <button type="button" onclick="quickFill('TRX-PASPOR-260721-001')" class="hover:text-blue-400 underline font-mono">TRX-PASPOR-260721-001</button>
            </div>
        </div>

        {{-- Loading Spinner --}}
        <div id="resultLoading" class="hidden my-12 text-center">
            <div class="inline-flex items-center gap-3 bg-slate-800/80 border border-slate-700 px-6 py-4 rounded-2xl shadow-xl">
                <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-slate-300 font-medium text-sm animate-pulse">Menelusuri database KPRI & manifest ekspedisi...</span>
            </div>
        </div>

        {{-- Error Card --}}
        <div id="resultError" class="hidden my-8 bg-red-500/10 border border-red-500/30 rounded-2xl p-6 text-center max-w-xl mx-auto">
            <div class="w-12 h-12 bg-red-500/20 text-red-400 rounded-full flex items-center justify-center mx-auto mb-3 text-xl font-bold">!</div>
            <h4 id="errorMessage" class="text-red-400 font-bold text-base mb-1">Data tidak ditemukan</h4>
            <p class="text-slate-400 text-xs">Pastikan nomor resi atau ID transaksi yang Anda ketik sudah benar dan tercatat di loket Koperasi Imigrasi Bogor.</p>
        </div>

        {{-- Success Display Container --}}
        <div id="resultContainer" class="hidden my-8 space-y-6">
            
            {{-- Simulation Badge --}}
            <div id="publicMockBadge" class="hidden bg-amber-500/10 border border-amber-500/30 rounded-2xl p-4 flex items-center gap-3 text-amber-300 text-xs">
                <svg class="w-5 h-5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                <div>
                    <p class="font-bold">Mode Simulasi Lacak Resi (Mock Data)</p>
                    <p class="text-amber-300/80">Sistem saat ini menampilkan data simulasi resi. Pasang API Key BinderByte di server untuk data ekspedisi real.</p>
                </div>
            </div>

            {{-- KPRI Status Card --}}
            <div id="kpriCard" class="bg-slate-800/90 border border-slate-700 rounded-2xl p-6 shadow-xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-700/80 pb-5 mb-5">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-blue-400">Data Permohonan KPRI</span>
                        <h3 id="resIdTrans" class="text-xl font-bold text-white mt-0.5">TRX-PASPOR-...</h3>
                    </div>
                    <div>
                        <span id="resStatusBadge" class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold tracking-wide uppercase bg-blue-500/20 text-blue-300 border border-blue-500/40">
                            DIPROSES
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div class="bg-slate-900/60 p-3.5 rounded-xl border border-slate-800">
                        <span class="text-slate-400 block mb-1">Nama Pemohon</span>
                        <span id="resNama" class="font-bold text-slate-200 text-sm">Bud*** ***oso</span>
                    </div>
                    <div class="bg-slate-900/60 p-3.5 rounded-xl border border-slate-800">
                        <span class="text-slate-400 block mb-1">Jumlah Paspor</span>
                        <span id="resJml" class="font-bold text-slate-200 text-sm">1 Buku</span>
                    </div>
                    <div class="bg-slate-900/60 p-3.5 rounded-xl border border-slate-800">
                        <span class="text-slate-400 block mb-1">Tanggal Daftar</span>
                        <span id="resTgl" class="font-bold text-slate-200 text-sm">21/07/2026</span>
                    </div>
                    <div class="bg-slate-900/60 p-3.5 rounded-xl border border-slate-800">
                        <span class="text-slate-400 block mb-1">Nomor Resi</span>
                        <span id="resNoResi" class="font-mono font-bold text-blue-400 text-sm">-</span>
                    </div>
                </div>
            </div>

            {{-- Expedition Manifest Card --}}
            <div id="ekspedisiCard" class="bg-slate-800/90 border border-slate-700 rounded-2xl p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-4 mb-6">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        <h4 class="font-bold text-white text-base">Riwayat Perjalanan Resi Ekspedisi</h4>
                    </div>
                    <span id="resCourierName" class="text-xs font-mono font-bold bg-slate-900 px-3 py-1 rounded-lg border border-slate-700 text-slate-300">POS INDONESIA</span>
                </div>

                <div id="resManifestTimeline" class="relative border-l-2 border-blue-500 ml-3 pl-6 space-y-7 py-2">
                    {{-- Injected via JS --}}
                </div>
            </div>

            <div id="noResiWarning" class="hidden bg-slate-800/50 border border-slate-700 rounded-2xl p-6 text-center text-slate-400 text-sm">
                <p>Nomor resi pengiriman belum diinput oleh kasir/operator loket. Paspor Anda saat ini sedang dalam persiapan pengemasan.</p>
            </div>

        </div>
    </main>

    {{-- Footer with Watermark --}}
    <footer class="border-t border-slate-800 bg-slate-900/80 py-6 px-4 text-center text-xs text-slate-500">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>&copy; {{ date('Y') }} KPRI Kantor Imigrasi Kelas I Non-TPI Bogor. All rights reserved.</p>
            <div class="flex items-center gap-3 bg-slate-800/90 px-4 py-2 rounded-full border border-slate-700 shadow-sm">
                <span class="text-xs font-medium text-slate-400 tracking-wide">Developed by</span>
                <div class="flex items-center gap-2 font-bold text-slate-100">
                    <img src="{{ asset('images/logo-if-itera-new.jpeg') }}" alt="Logo IF ITERA" class="w-auto object-contain rounded" style="height: 26px;" />
                    <span class="tracking-wider text-xs">IF ITERA</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
    function quickFill(val) {
        document.getElementById('trackingQuery').value = val;
        document.getElementById('trackForm').dispatchEvent(new Event('submit'));
    }

    function handlePublicTrack(e) {
        e.preventDefault();
        const query = document.getElementById('trackingQuery').value.trim();
        if (!query) return;

        document.getElementById('resultLoading').classList.remove('hidden');
        document.getElementById('resultError').classList.add('hidden');
        document.getElementById('resultContainer').classList.add('hidden');

        fetch(`/api/track?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('resultLoading').classList.add('hidden');

                if (data.status === 'error' || (typeof data.status === 'number' && data.status !== 200)) {
                    document.getElementById('resultError').classList.remove('hidden');
                    document.getElementById('errorMessage').textContent = data.message || 'Data tidak ditemukan di sistem.';
                    return;
                }

                document.getElementById('resultContainer').classList.remove('hidden');

                if (data.is_mock) {
                    document.getElementById('publicMockBadge').classList.remove('hidden');
                } else {
                    document.getElementById('publicMockBadge').classList.add('hidden');
                }

                // Jika sumber adalah sistem KPRI
                if (data.source === 'kpri_system' && data.data.kpri) {
                    document.getElementById('kpriCard').classList.remove('hidden');
                    const k = data.data.kpri;
                    document.getElementById('resIdTrans').textContent = `${k.id_transaksi} (Struk: #${k.no_struk})`;
                    document.getElementById('resNama').textContent = k.nama_penerima_masked || '-';
                    document.getElementById('resJml').textContent = `${k.jumlah_paspor} Buku`;
                    document.getElementById('resTgl').textContent = k.tanggal_transaksi || '-';
                    document.getElementById('resNoResi').textContent = k.no_resi || 'Belum Diinput';

                    const badge = document.getElementById('resStatusBadge');
                    badge.textContent = k.status_internal ? k.status_internal.toUpperCase() : 'PROSES';
                    if (k.status_internal === 'selesai') {
                        badge.className = 'inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold tracking-wide uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/40';
                    } else if (k.status_internal === 'batal') {
                        badge.className = 'inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold tracking-wide uppercase bg-red-500/20 text-red-300 border border-red-500/40';
                    } else {
                        badge.className = 'inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold tracking-wide uppercase bg-blue-500/20 text-blue-300 border border-blue-500/40';
                    }

                    // Manifest ekspedisi
                    if (k.no_resi && k.no_resi !== '-') {
                        document.getElementById('ekspedisiCard').classList.remove('hidden');
                        document.getElementById('noResiWarning').classList.add('hidden');
                        if (data.data.ekspedisi) {
                            renderManifest(data.data.ekspedisi);
                        } else {
                            renderManifest({
                                summary: { awb: k.no_resi, courier: 'POS', status: 'ON PROCESS' },
                                history: [{ date: k.tanggal_transaksi || '-', desc: 'Resi pengiriman tercatat di loket Koperasi dan dalam proses pengiriman POS Indonesia.', location: 'Bogor' }]
                            });
                        }
                    } else {
                        document.getElementById('ekspedisiCard').classList.add('hidden');
                        document.getElementById('noResiWarning').classList.remove('hidden');
                    }
                } else if (data.source === 'ekspedisi_direct') {
                    // Cek resi langsung ke ekspedisi
                    document.getElementById('kpriCard').classList.add('hidden');
                    document.getElementById('noResiWarning').classList.add('hidden');
                    document.getElementById('ekspedisiCard').classList.remove('hidden');
                    renderManifest(data.data);
                }
            })
            .catch(err => {
                document.getElementById('resultLoading').classList.add('hidden');
                document.getElementById('resultError').classList.remove('hidden');
                document.getElementById('errorMessage').textContent = 'Terjadi kesalahan jaringan saat melacak status.';
            });
    }

    function renderManifest(ekspedisi) {
        const summary = ekspedisi.summary || {};
        const history = ekspedisi.history || [];

        document.getElementById('resCourierName').textContent = summary.courier ? summary.courier.toUpperCase() : 'EKSPEDISI';

        const timelineEl = document.getElementById('resManifestTimeline');
        timelineEl.innerHTML = '';

        if (history.length === 0) {
            timelineEl.innerHTML = '<p class="text-xs text-slate-400 italic">Belum ada riwayat pergerakan paket dari ekspedisi.</p>';
        } else {
            history.forEach((item, index) => {
                const isFirst = index === 0;
                timelineEl.innerHTML += `
                    <div class="relative">
                        <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full border-2 border-slate-900 ${isFirst ? 'bg-blue-500 ring-4 ring-blue-500/30' : 'bg-slate-600'}"></div>
                        <span class="text-xs text-slate-400 font-mono">${item.date || '-'}</span>
                        <p class="text-sm sm:text-base font-semibold text-white mt-1">${item.desc || '-'}</p>
                        ${item.location ? `<p class="text-xs text-blue-400 mt-1 flex items-center gap-1"><span class="font-semibold">Lokasi:</span> ${item.location}</p>` : ''}
                    </div>
                `;
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const queryInput = document.getElementById('trackingQuery');
        if (queryInput && queryInput.value.trim() !== '') {
            handlePublicTrack(new Event('submit'));
        }
    });
    </script>
</body>
</html>
