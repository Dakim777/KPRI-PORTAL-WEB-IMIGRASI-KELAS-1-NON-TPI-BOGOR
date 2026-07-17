<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'KPRI') — Portal Kantor Imigrasi Bogor Kelas 1 Non TPI Bogor</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        navy: {
                            50:  '#f0f4f8',
                            100: '#d9e2ec',
                            200: '#bcccdc',
                            300: '#9fb3c8',
                            400: '#829ab1',
                            500: '#627d98',
                            600: '#486581',
                            700: '#334e68',
                            800: '#243b53',
                            900: '#102a43',
                            950: '#0a1e30',
                        },
                        imigr: {
                            DEFAULT: '#1A3A6B',
                            light:   '#2352A0',
                            mid:     '#1E4580',
                            hover:   '#1E4580',
                            muted:   '#8AAFE8',
                            bg:      '#EEF2F7',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 6px; font-size: 13.5px;
            transition: background 0.15s, color 0.15s; color: #8AAFE8;
        }
        .sidebar-link:hover { background: #1E4580; color: #fff; }
        .sidebar-link.active { background: #2352A0; color: #fff; font-weight: 500; }
        .sidebar-section { font-size: 10.5px; font-weight: 600; letter-spacing: 0.08em;
            text-transform: uppercase; color: #6B9BE8; padding: 0 12px; margin: 16px 0 6px; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #2352A0; border-radius: 4px; }
    </style>
</head>
<body class="bg-[#EEF2F7]" style="font-family:'Inter',sans-serif">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ──────────────────────────────────────────────── --}}
    <aside class="w-60 flex flex-col flex-shrink-0" style="background:#1A3A6B">

        {{-- Brand --}}
        <div class="px-4 py-5 border-b" style="border-color:#1E4580">
            <div class="flex items-center justify-center gap-3 mb-3">
                <img src="{{ asset('images/logo-kementrian-imigrasi.png') }}"
                     alt="Logo Kementerian Imigrasi dan Pemasyarakatan"
                     class="h-11 w-11 object-contain" />
                <img src="{{ asset('images/logo-ditjen-imigrasi.png') }}"
                     alt="Logo Ditjen Imigrasi"
                     class="h-11 w-11 object-contain" />
            </div>
            <div class="text-center">
                <div class="text-white font-bold text-base tracking-widest">KPRI</div>
                <div class="text-[9px] font-medium tracking-wide mt-1 uppercase leading-tight" style="color:#7A9BB8">Kantor Imigrasi Bogor<br>Kelas 1 Non TPI Bogor</div>
                <div class="text-[10px] mt-1.5 px-2 py-0.5 rounded-full inline-block" style="background:#1E4580;color:#8AAFE8">
                    {{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">

            @if(auth()->user()->role === 'super_admin')
                <div class="sidebar-section">Administrasi</div>
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Kelola Akun
                </a>
                <a href="{{ route('admin.logs') }}"
                   class="sidebar-link {{ request()->routeIs('admin.logs*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Log Aktivitas
                </a>
                <a href="{{ route('admin.trash.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.trash*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Tong Sampah
                </a>
            @endif

            @if(in_array(auth()->user()->role, ['kasir_kantin', 'super_admin']))
                <div class="sidebar-section">Modul Kantin</div>
                <a href="{{ route('kantin.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('kantin.dashboard') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Dashboard Kantin
                </a>
                <a href="{{ route('kantin.pos') }}"
                   class="sidebar-link {{ request()->routeIs('kantin.pos') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Kasir / POS
                </a>
                <a href="{{ route('kantin.pembelian.create') }}"
                   class="sidebar-link {{ request()->routeIs('kantin.pembelian.create') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Input Barang Masuk
                </a>
                <a href="{{ route('kantin.pembelian.index') }}"
                   class="sidebar-link {{ request()->routeIs('kantin.pembelian.index') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Riwayat Pembelian
                </a>
                <a href="{{ route('kantin.produk.index') }}"
                   class="sidebar-link {{ request()->routeIs('kantin.produk.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Master Produk
                </a>
                <a href="{{ route('kantin.kategori.index') }}"
                   class="sidebar-link {{ request()->routeIs('kantin.kategori*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Kategori Produk
                </a>
                <a href="{{ route('kantin.transaksi') }}"
                   class="sidebar-link {{ request()->routeIs('kantin.transaksi*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Riwayat Transaksi
                </a>
            @endif

            @if(in_array(auth()->user()->role, ['kasir_paspor', 'super_admin']))
                <div class="sidebar-section">Modul Paspor</div>
                <a href="{{ route('paspor.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('paspor.dashboard') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Dashboard Paspor
                </a>
                <a href="{{ route('paspor.create') }}"
                   class="sidebar-link {{ request()->routeIs('paspor.create') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Transaksi Baru
                </a>
                <a href="{{ route('paspor.index') }}"
                   class="sidebar-link {{ request()->routeIs('paspor.index') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Lihat Data
                </a>
            @endif
        </nav>

        {{-- User Info + Logout --}}
        <div class="px-3 py-3 border-t" style="border-color:#1E4580">
            <div class="flex items-center gap-3 px-3 py-2 mb-1">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                     style="background:#2352A0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <p class="text-xs truncate" style="color:#7A9BB8">{{ auth()->user()->name }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link w-full text-left" style="color:#7A9BB8">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main Content ──────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between flex-shrink-0">
            <h2 class="text-sm font-semibold text-gray-700 tracking-wide">@yield('page-title', 'Dashboard')</h2>
            <div class="flex items-center gap-4">
                {{-- Notifications (Sembunyikan untuk kasir kantin) --}}
                @if(auth()->user()->role !== 'kasir_kantin')
                <div class="relative x-dropdown">
                    <button type="button" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none" onclick="this.nextElementSibling.classList.toggle('hidden')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                        @if($unreadCount > 0)
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                        @endif
                    </button>
                    <div class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-100 z-50 overflow-hidden">
                        <div class="p-3 border-b border-gray-100 bg-gray-50">
                            <h3 class="text-sm font-semibold text-gray-700">Notifikasi ({{ $unreadCount }})</h3>
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            @forelse(auth()->user()->unreadNotifications as $notif)
                                <div class="p-3 border-b border-gray-50 hover:bg-gray-50 transition">
                                    <p class="text-xs text-gray-600 mb-1">{{ $notif->data['pesan'] ?? 'Notifikasi baru' }}</p>
                                    <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                        @csrf
                                        <button type="submit" class="text-[10px] text-blue-600 hover:underline">Tandai sudah dibaca</button>
                                    </form>
                                </div>
                            @empty
                                <div class="p-4 text-center text-xs text-gray-500">Tidak ada notifikasi baru.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endif

                <span class="text-xs text-gray-400">{{ now()->translatedFormat('l, d F Y') }}</span>
                <div class="flex items-center gap-2 pl-4 border-l border-gray-100">
                    <img src="{{ asset('images/logo-kementrian-imigrasi.png') }}" alt="" class="h-6 w-6 object-contain opacity-80" />
                    <img src="{{ asset('images/logo-ditjen-imigrasi.png') }}" alt="" class="h-6 w-6 object-contain opacity-80" />
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-6">

            @if(session('success'))
                <div class="mb-5 p-3.5 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-3">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    <p class="font-semibold mb-1.5">Terjadi kesalahan:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</div>

@stack('scripts')
</body>
</html>
