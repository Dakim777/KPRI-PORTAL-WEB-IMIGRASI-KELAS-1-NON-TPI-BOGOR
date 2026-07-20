@extends('layouts.app')

@section('title', 'Tambah Akun Pengguna')
@section('page-title', 'Tambah Akun Pengguna Baru')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Form Tambah Akun</h3>
            <p class="text-xs text-gray-400 mt-0.5">Semua field bertanda <span class="text-red-500">*</span> wajib diisi.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="px-6 py-6 space-y-5">
            @csrf

            {{-- Nama --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    placeholder="Contoh: Ahmad Kasim"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror" />
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    placeholder="kasir@koperasi.com"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror" />
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Role --}}
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Role / Jabatan <span class="text-red-500">*</span>
                </label>
                <select id="role" name="role" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('role') border-red-400 @enderror">
                    <option value="">-- Pilih Role --</option>
                    <option value="kasir_paspor" {{ old('role') === 'kasir_paspor' ? 'selected' : '' }}>Kasir Paspor</option>
                    <option value="super_admin"  {{ old('role') === 'super_admin'  ? 'selected' : '' }}>Super Admin</option>
                </select>
                @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-gray-400">
                    Super Admin dapat mengakses semua modul + kelola pengguna.
                </p>
            </div>

            {{-- Password + Konfirmasi --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" required
                        placeholder="Min. 8 karakter"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror" />
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        placeholder="Ulangi password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
            </div>

            {{-- Info box --}}
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-700 flex items-start gap-2">
                
                <span>Kasir yang baru dibuat bisa langsung login menggunakan email dan password di atas. Pastikan data yang dimasukkan sudah benar sebelum disimpan.</span>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                    class="bg-blue-700 hover:bg-blue-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Simpan Akun
                </button>
                <a href="{{ route('admin.users.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
