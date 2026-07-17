@extends('layouts.app')

@section('title', 'Edit Akun — ' . $user->name)
@section('page-title', 'Edit Akun: ' . $user->name)

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">Form Edit Akun</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kosongkan field password jika tidak ingin mengganti password.</p>
            </div>
            @php
                $roleClass = match($user->role) {
                    'super_admin'  => 'bg-blue-100 text-blue-800',
                    'kasir_kantin' => 'bg-green-100 text-green-800',
                    'kasir_paspor' => 'bg-purple-100 text-purple-800',
                    default        => 'bg-gray-100 text-gray-700',
                };
            @endphp
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $roleClass }}">
                Role Saat Ini: {{ ucfirst(str_replace('_', ' ', $user->role)) }}
            </span>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="px-6 py-6 space-y-5">
            @csrf
            @method('PUT')

            {{-- Nama --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror" />
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
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
                    <option value="kasir_kantin" {{ old('role', $user->role) === 'kasir_kantin' ? 'selected' : '' }}>Kasir Kantin</option>
                    <option value="kasir_paspor" {{ old('role', $user->role) === 'kasir_paspor' ? 'selected' : '' }}>Kasir Paspor</option>
                    <option value="super_admin"  {{ old('role', $user->role) === 'super_admin'  ? 'selected' : '' }}>Super Admin</option>
                </select>
                @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Separator --}}
            <div class="relative">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-dashed border-gray-200"></div></div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-3 text-xs text-gray-400">Ganti Password (opsional)</span>
                </div>
            </div>

            {{-- Password Baru + Konfirmasi --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                    <input type="password" id="password" name="password"
                        placeholder="Kosongkan jika tidak ganti"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror" />
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        placeholder="Ulangi password baru"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.users.show', $user->id) }}"
                   class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
