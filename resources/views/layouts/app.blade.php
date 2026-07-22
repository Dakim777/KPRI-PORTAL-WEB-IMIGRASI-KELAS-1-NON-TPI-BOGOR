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
                <a href="{{ route('admin.whatsapp.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.whatsapp*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Gateway WhatsApp
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
            <div class="mt-3 pt-2.5 border-t border-blue-900/50 text-[10.5px] text-center flex items-center justify-center gap-2">
                <span class="text-blue-300/70 font-medium">Developed by</span>
                <img src="{{ asset('images/logo-if-itera-new.jpeg') }}" alt="Logo IF ITERA" class="w-auto object-contain rounded-sm" style="height: 20px;" />
                <span class="font-bold tracking-wider text-white">IF ITERA</span>
            </div>
        </div>
    </aside>

    {{-- ── Main Content ──────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between flex-shrink-0">
            <h2 class="text-sm font-semibold text-gray-700 tracking-wide">@yield('page-title', 'Dashboard')</h2>
            <div class="flex items-center gap-4">
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

            {{-- Footer / Watermark --}}
            <footer class="mt-8 pt-5 border-t border-gray-200/80 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-3 pb-2">
                <p>&copy; {{ date('Y') }} KPRI Kantor Imigrasi Kelas I Non-TPI Bogor.</p>
                <div class="flex items-center gap-2.5 bg-white/90 px-4 py-2 rounded-full border border-gray-200 shadow-sm">
                    <span class="text-gray-500 font-medium text-xs">Developed by</span>
                    <div class="flex items-center gap-2 font-bold text-gray-800">
                        <img src="{{ asset('images/logo-if-itera-new.jpeg') }}" alt="Logo IF ITERA" class="w-auto object-contain rounded" style="height: 24px;" />
                        <span class="tracking-wider text-xs">Informatika ITERA</span>
                    </div>
                </div>
            </footer>
        </main>
    </div>

</div>

@stack('scripts')
</body>
</html>
