@extends('layouts.app')

@section('title', 'Detail Akun — ' . $user->name)
@section('page-title', 'Detail Akun Pengguna')

@section('content')
<div class="max-w-2xl space-y-4">

    {{-- ── Profil Card ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 flex items-start gap-5">
            {{-- Avatar --}}
            <div class="w-16 h-16 rounded-full flex items-center justify-center font-bold text-2xl text-white flex-shrink-0
                {{ $user->role === 'super_admin' ? 'bg-blue-600' : ($user->role === 'kasir_kantin' ? 'bg-green-600' : 'bg-purple-600') }}">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h3 class="font-bold text-gray-800 text-xl">{{ $user->name }}</h3>
                    @if($user->id === auth()->id())
                    <span class="text-[10px] bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded-full">Akun Anda</span>
                    @endif
                </div>
                <p class="text-gray-500 text-sm mt-0.5">{{ $user->email }}</p>

                @php
                    $roleLabel = match($user->role) {
                        'super_admin'  => ['label' => 'Super Admin',  'class' => 'bg-blue-100 text-blue-800'],
                        'kasir_kantin' => ['label' => 'Kasir Kantin', 'class' => 'bg-green-100 text-green-800'],
                        'kasir_paspor' => ['label' => 'Kasir Paspor', 'class' => 'bg-purple-100 text-purple-800'],
                        default        => ['label' => $user->role,         'class' => 'bg-gray-100 text-gray-700'],
                    };
                @endphp
                <span class="mt-2 inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $roleLabel['class'] }}">
                    {{ $roleLabel['label'] }}
                </span>
            </div>

            <div class="flex gap-2 flex-shrink-0">
                <a href="{{ route('admin.users.edit', $user->id) }}"
                   class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Edit
                </a>
                @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                      onsubmit="return confirm('Hapus akun {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                        Hapus
                    </button>
                </form>
                @endif
            </div>
        </div>

        <div class="border-t border-gray-100 px-6 py-4 grid grid-cols-2 gap-4 text-sm bg-gray-50">
            <div>
                <p class="text-gray-400 text-xs">Akun Dibuat</p>
                <p class="font-medium text-gray-700">{{ $user->created_at->format('d/m/Y H:i:s') }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Terakhir Diperbarui</p>
                <p class="font-medium text-gray-700">{{ $user->updated_at->format('d/m/Y H:i:s') }}</p>
            </div>
        </div>
    </div>

    {{-- ── Statistik Aktivitas ───────────────────────────────────────────── --}}
    @if(in_array($user->role, ['kasir_kantin', 'kasir_paspor', 'super_admin']))
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h4 class="font-semibold text-gray-700 mb-4 text-sm">Statistik Aktivitas</h4>
        <div class="grid grid-cols-2 gap-4">
            @if(in_array($user->role, ['kasir_kantin', 'super_admin']))
            <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                <p class="text-xs text-green-600 font-medium">Total Transaksi Kantin</p>
                <p class="text-3xl font-bold text-green-700 mt-1">{{ number_format($stats['total_kantin']) }}</p>
                <p class="text-xs text-green-500 mt-1">Omset: Rp {{ number_format($stats['omset_kantin'], 0, ',', '.') }}</p>
            </div>
            @endif
            @if(in_array($user->role, ['kasir_paspor', 'super_admin']))
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-100">
                <p class="text-xs text-purple-600 font-medium">Total Pengiriman Paspor</p>
                <p class="text-3xl font-bold text-purple-700 mt-1">{{ number_format($stats['total_paspor']) }}</p>
                <p class="text-xs text-purple-500 mt-1">Omset: Rp {{ number_format($stats['omset_paspor'], 0, ',', '.') }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Akses Modul ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h4 class="font-semibold text-gray-700 mb-3 text-sm">Akses Modul</h4>
        <div class="space-y-2 text-sm">
            <div class="flex items-center justify-between py-2 border-b border-gray-50">
                <span class="text-gray-600">Kelola Akun Pengguna (Admin)</span>
                <span class="{{ $user->role === 'super_admin' ? 'text-green-600 font-bold' : 'text-red-400' }}">
                    {{ $user->role === 'super_admin' ? 'Diizinkan' : 'Tidak' }}
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-50">
                <span class="text-gray-600">Modul Kantin (POS + Produk)</span>
                <span class="{{ in_array($user->role, ['kasir_kantin','super_admin']) ? 'text-green-600 font-bold' : 'text-red-400' }}">
                    {{ in_array($user->role, ['kasir_kantin','super_admin']) ? 'Diizinkan' : 'Tidak' }}
                </span>
            </div>
            <div class="flex items-center justify-between py-2">
                <span class="text-gray-600">Modul Paspor (Pengiriman)</span>
                <span class="{{ in_array($user->role, ['kasir_paspor','super_admin']) ? 'text-green-600 font-bold' : 'text-red-400' }}">
                    {{ in_array($user->role, ['kasir_paspor','super_admin']) ? 'Diizinkan' : 'Tidak' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── Navigation ───────────────────────────────────────────────────── --}}
    <a href="{{ route('admin.users.index') }}"
       class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
        Kembali ke Daftar Akun
    </a>

</div>
@endsection
