<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login — KPRI | Portal Kantor Imigrasi Bogor Kelas 1 Non TPI Bogor</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0d2347 0%,#163260 50%,#1E4585 100%)">

<div class="w-full max-w-sm">
    {{-- Card --}}
    <div class="bg-white rounded-xl shadow-2xl overflow-hidden">

        {{-- Header --}}
        <div class="px-8 py-7 text-center" style="background:#163260">
            <div class="flex items-center justify-center gap-4 mb-4">
                <img src="{{ asset('images/logo-kementrian-imigrasi.png') }}"
                     alt="Logo Kementerian Imigrasi dan Pemasyarakatan RI"
                     class="h-14 w-14 object-contain" />
                <img src="{{ asset('images/logo-ditjen-imigrasi.png') }}"
                     alt="Logo Direktorat Jenderal Imigrasi"
                     class="h-14 w-14 object-contain" />
            </div>
            <h1 class="text-xl font-bold text-white tracking-widest">KPRI</h1>
            <p class="text-[10px] font-medium tracking-wide mt-1 uppercase" style="color:#8AAFE8">Kantor Imigrasi Bogor<br>Kelas 1 Non TPI Bogor</p>
            <p class="text-xs mt-2" style="color:#6B9BE8">Silakan masuk untuk melanjutkan</p>
        </div>

        {{-- Form --}}
        <div class="px-8 py-7">
            @if($errors->any())
                <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4" data-loader-title="Memverifikasi Kredensial..." data-loader-desc="Mohon tunggu, sedang memeriksa username dan password Anda...">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Email</label>
                    <input
                        id="email" type="email" name="email"
                        value="{{ old('email') }}" required autocomplete="email"
                        placeholder="kasir@koperasi.com"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:border-transparent transition @error('email') border-red-400 @enderror"
                        style="focus:ring-color:#1A3557"
                    />
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Password</label>
                    <input
                        id="password" type="password" name="password"
                        required autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:border-transparent transition"
                    />
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 border-gray-300 rounded" />
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                </div>

                <button type="submit"
                    class="w-full text-white font-semibold py-2.5 px-4 rounded-lg transition text-sm mt-2"
                    style="background:#0D1F3C"
                    onmouseover="this.style.background='#1A3557'"
                    onmouseout="this.style.background='#0D1F3C'">
                    Masuk
                </button>
            </form>


        </div>
    </div>

    <div class="mt-6 flex flex-col items-center justify-center text-xs" style="color:#8AAFE8">
        <p class="text-center font-medium">
            KPRI — Kantor Imigrasi Bogor Kelas 1 Non TPI Bogor &copy; {{ date('Y') }}
        </p>
    </div>
</div>

{{-- Credit Logo - Pojok Kanan Bawah --}}
<div class="fixed bottom-5 right-5 z-50 flex items-center gap-2.5 bg-white/95 backdrop-blur-md px-4 py-2 rounded-full border border-gray-200 shadow-xl transition-all">
    <span class="text-gray-600 font-medium text-xs">Developed by</span>
    <div class="flex items-center gap-2 font-bold text-gray-900">
        <img src="{{ asset('images/logo-if-itera-new.jpeg') }}" alt="Logo Informatika ITERA" class="w-auto object-contain rounded" style="height: 28px;" />
        <span class="tracking-wider text-xs">Informatika ITERA</span>
    </div>
</div>

@include('components.loading-buffer')
</body>
</html>
