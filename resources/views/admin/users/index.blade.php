@extends('layouts.app')

@section('title', 'Kelola Akun Pengguna')
@section('page-title', 'Kelola Akun Pengguna')

@section('content')
<div class="space-y-5">

    {{-- ── Stat Widgets ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Akun</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Super Admin</p>
            <p class="text-3xl font-bold text-blue-700 mt-1">{{ $stats['super_admin'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Kasir Paspor</p>
            <p class="text-3xl font-bold text-purple-700 mt-1">{{ $stats['kasir_paspor'] }}</p>
        </div>
    </div>

    {{-- ── Action Bar ──────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Akun Baru
        </a>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama / email..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-56" />
            <select name="role" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Semua Role --</option>
                <option value="super_admin"  {{ request('role') === 'super_admin'  ? 'selected' : '' }}>Super Admin</option>
                <option value="kasir_paspor" {{ request('role') === 'kasir_paspor' ? 'selected' : '' }}>Kasir Paspor</option>
            </select>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Cari
            </button>
            @if(request()->hasAny(['search','role']))
            <a href="{{ route('admin.users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">Reset</a>
            @endif
        </form>
    </div>

    {{-- ── Flash Messages ───────────────────────────────────────────────── --}}
    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
        {{ session('error') }}
    </div>
    @endif

    {{-- ── Users Table ──────────────────────────────────────────────────── --}}
    {{-- ── Users Table ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="overflow-x-auto rounded-xl" style="-webkit-overflow-scrolling: touch; transform: translateZ(0); will-change: scroll-position;">
            <table class="w-full min-w-[700px] text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left whitespace-nowrap">No.</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Nama</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Email</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Role</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Terdaftar</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors duration-100 {{ $user->id === auth()->id() ? 'bg-blue-50' : '' }}">
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ $loop->iteration + ($users->firstItem() - 1) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs text-white flex-shrink-0
                                    {{ $user->role === 'super_admin' ? 'bg-blue-600' : 'bg-purple-600' }}">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                    @if($user->id === auth()->id())
                                    <p class="text-[10px] text-blue-500 font-semibold">( Akun Anda )</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $roleLabel = match($user->role) {
                                    'super_admin'  => ['label' => 'Super Admin',  'class' => 'bg-blue-100 text-blue-800'],
                                    'kasir_paspor' => ['label' => 'Kasir Paspor', 'class' => 'bg-purple-100 text-purple-800'],
                                    default        => ['label' => $user->role,         'class' => 'bg-gray-100 text-gray-700'],
                                };
                            @endphp
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleLabel['class'] }}">
                                {{ $roleLabel['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500 whitespace-nowrap">
                            {{ $user->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.users.show', $user->id) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium text-xs whitespace-nowrap">Detail</a>
                                <span class="text-gray-300">|</span>
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="text-amber-600 hover:text-amber-800 font-medium text-xs">Edit</a>
                                <span class="text-gray-300">|</span>
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                      onsubmit="return confirm('Hapus akun {{ $user->name }}? Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Tidak ada akun yang cocok dengan pencarian
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
