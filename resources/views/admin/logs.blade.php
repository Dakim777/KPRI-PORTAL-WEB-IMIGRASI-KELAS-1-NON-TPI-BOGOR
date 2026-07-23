@extends('layouts.app')
@section('title', 'Log Aktivitas Sistem')
@section('page-title', 'Audit Trail (Log Aktivitas)')

@section('content')
<div class="space-y-5">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-xl">
            <h3 class="font-semibold text-gray-800">Daftar Aktivitas Pengguna</h3>
        </div>
        <div class="overflow-x-auto rounded-b-xl" style="-webkit-overflow-scrolling: touch; transform: translateZ(0); will-change: scroll-position;">
            <table class="w-full min-w-[750px] text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Waktu</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Pengguna</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Aksi</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Target (Model)</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap">Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $log->causer ? $log->causer->name : 'Sistem' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $color = match($log->description) {
                                    'created' => 'bg-green-100 text-green-700',
                                    'updated' => 'bg-blue-100 text-blue-700',
                                    'deleted' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold uppercase {{ $color }}">
                                {{ $log->description }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 font-mono">
                            {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-700">
                            @php 
                                $changesData = $log->attribute_changes && $log->attribute_changes->count() > 0 
                                                ? $log->attribute_changes 
                                                : $log->properties;
                            @endphp
                            @if($log->description === 'updated' && isset($changesData['attributes']) && isset($changesData['old']))
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach($changesData['attributes'] as $key => $newValue)
                                        @php $oldValue = $changesData['old'][$key] ?? null; @endphp
                                        @if($oldValue != $newValue)
                                            <li>
                                                Ubah <b>{{ str_replace('_', ' ', Str::title($key)) }}</b> 
                                                dari <span class="line-through text-red-500">{{ $oldValue ?: 'kosong' }}</span> 
                                                ke <span class="text-green-600 font-medium">{{ $newValue ?: 'kosong' }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @elseif($log->description === 'created')
                                <span class="text-green-600 font-medium">Membuat data baru.</span>
                            @elseif($log->description === 'deleted')
                                <span class="text-red-600 font-medium">Menghapus data ini.</span>
                            @elseif($changesData && $changesData->count() > 0)
                                <details class="cursor-pointer">
                                    <summary class="text-blue-600 hover:underline">Lihat Detail</summary>
                                    <pre class="mt-2 p-2 bg-gray-100 rounded text-[10px] overflow-x-auto">{{ json_encode($changesData, JSON_PRETTY_PRINT) }}</pre>
                                </details>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada log aktivitas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
