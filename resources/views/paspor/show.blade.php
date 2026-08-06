@extends('layouts.app')

@section('title', 'Detail Pengiriman Paspor #' . $paspor->id_transaksi)
@section('page-title', 'Detail Pengiriman Paspor')

@section('content')
<div class="max-w-3xl space-y-4">

    {{-- ── Main Detail Card ────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Header stripe --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800 text-lg">
                    {{ $paspor->id_transaksi }}
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">No. Struk: {{ $paspor->no_struk }} · Dibuat: {{ $paspor->created_at->format('d F Y, H:i:s') }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $paspor->statusColorClass() }}">
                {{ ucfirst($paspor->status_pengiriman) }}
            </span>
        </div>

        {{-- Detail grid --}}
        <div class="px-6 py-5">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-x-8 gap-y-5 text-sm">

                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Nama Penerima</p>
                    <p class="font-semibold text-gray-800">{{ $paspor->nama_penerima }}</p>
                </div>

                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Jumlah Paspor</p>
                    <p class="font-mono font-bold text-blue-700">{{ $paspor->jumlah_paspor }} Paspor</p>
                </div>
                
                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">No. Resi</p>
                    <p class="font-mono font-semibold text-gray-800">{{ $paspor->no_resi ?? 'Belum ada' }}</p>
                </div>

                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Ekspedisi & Layanan</p>
                    <p class="font-semibold text-gray-800">{{ strtoupper($paspor->jenis_kurir ?? 'POS') }} - {{ $paspor->layanan_kurir ?? 'Reguler' }}</p>
                </div>

                <div class="col-span-2 md:col-span-3">
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Daftar Nama Pemilik Paspor</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @php 
                            $list = is_array($paspor->nama_paspor_list) ? $paspor->nama_paspor_list : json_decode($paspor->nama_paspor_list, true) ?? [];
                        @endphp
                        @foreach($list as $nama)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 text-xs font-medium border border-gray-200">
                                {{ $nama }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="col-span-2 md:col-span-3">
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Alamat Tujuan Pengiriman</p>
                    <p class="font-semibold text-gray-800 leading-snug">{{ $paspor->alamat_tujuan_pengiriman }}</p>
                </div>

                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Tgl Transaksi</p>
                    <p class="font-semibold text-gray-800">{{ $paspor->tanggal_transaksi?->format('d M Y') }}</p>
                </div>

                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Tgl Kirim Paspor</p>
                    <p class="font-semibold text-gray-800">{{ $paspor->tanggal_pengiriman_paspor?->format('d M Y') ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Tgl Paspor Diterima</p>
                    <p class="font-semibold text-gray-800">{{ $paspor->tanggal_paspor_diterima?->format('d M Y') ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Input oleh (Kasir)</p>
                    <p class="font-semibold text-gray-800">{{ $paspor->kasir->name ?? '—' }}</p>
                </div>
            </div>

            <hr class="my-5 border-dashed border-gray-200" />

            {{-- Cost breakdown ──────────────────────────────────────────────── --}}
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Biaya Kurir / Resi</span>
                    <span class="font-medium text-gray-700">Rp {{ number_format($paspor->biaya_kurir, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Harga Modal Packaging</span>
                    <span class="font-medium text-gray-700">Rp {{ number_format($paspor->biaya_modal_packaging, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Handling Fee</span>
                    <span class="font-medium text-gray-700">Rp {{ number_format($paspor->handling_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Harga Materai (Auto {{ $paspor->jumlah_paspor }} x Rp 12.000)</span>
                    <span class="font-medium text-gray-700">Rp {{ number_format($paspor->harga_materai, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Harga Surat Kuasa (Auto {{ $paspor->jumlah_paspor }} x Rp 1.000)</span>
                    <span class="font-medium text-gray-700">Rp {{ number_format($paspor->harga_surat_kuasa, 0, ',', '.') }}</span>
                </div>
                
                <div class="flex justify-between pt-3 border-t border-gray-100 text-base font-bold">
                    <span class="text-gray-800">Total Pembayaran Pemohon</span>
                    <span class="text-green-700 text-lg">Rp {{ number_format($paspor->total_pembayaran_pemohon, 0, ',', '.') }}</span>
                </div>
                
                @php 
                    $pendapatan = $paspor->total_pembayaran_pemohon - ($paspor->biaya_kurir + $paspor->biaya_modal_packaging + $paspor->harga_materai + $paspor->harga_surat_kuasa);
                @endphp
                <div class="flex justify-between pt-1">
                    <span class="text-gray-500 font-semibold italic">Estimasi Pendapatan (Laba)</span>
                    <span class="text-blue-700 font-bold italic">Rp {{ number_format($pendapatan, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Quick Status Update Card ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-5">
        <h4 class="text-sm font-semibold text-gray-700 mb-4">Update Status Pengiriman</h4>
        <form method="POST" action="{{ route('paspor.update-status', $paspor->id) }}" id="formUpdateStatus">
            @csrf
            @method('PATCH')

            {{-- Status selector --}}
            <div class="flex items-center gap-3 mb-4">
                <select name="status_pengiriman" id="statusSelect"
                    onchange="toggleBiayaFields(this.value)"
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(\App\Models\TransaksiPaspor::statusList() as $s)
                        <option value="{{ $s }}" {{ $paspor->status_pengiriman === $s ? 'selected' : '' }} class="bg-white text-gray-800">
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                    class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap">
                    Simpan
                </button>
            </div>

            {{-- Editable biaya fields (selalu tampil, bisa disesuaikan kapanpun) --}}
            <div id="biayaFields" class="border-t border-gray-100 pt-4 space-y-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Sesuaikan Biaya (opsional)</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Biaya Kurir (Rp)</label>
                        <input type="number" name="biaya_kurir" min="0" step="500"
                            value="{{ old('biaya_kurir', $paspor->biaya_kurir) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Biaya Modal Packaging (Rp)</label>
                        <input type="number" name="biaya_modal_packaging" min="0" step="500"
                            value="{{ old('biaya_modal_packaging', $paspor->biaya_modal_packaging) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Harga Materai (Rp)</label>
                        <input type="number" name="harga_materai" min="0" step="1000"
                            value="{{ old('harga_materai', $paspor->harga_materai) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Harga Surat Kuasa (Rp)</label>
                        <input type="number" name="harga_surat_kuasa" min="0" step="500"
                            value="{{ old('harga_surat_kuasa', $paspor->harga_surat_kuasa) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Handling Fee (Rp)</label>
                        <input type="number" name="handling_fee" min="0" step="500"
                            value="{{ old('handling_fee', $paspor->handling_fee) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">No. Resi Pengiriman</label>
                        <div class="flex items-center gap-2">
                            <input type="text" id="no_resi_show" name="no_resi"
                                value="{{ old('no_resi', $paspor->no_resi) }}"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                                placeholder="Scan atau ketik nomor resi..." />
                            <x-barcode-scanner targetId="no_resi_show" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Ekspedisi</label>
                        <select name="jenis_kurir" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                            <option value="POS" {{ ($paspor->jenis_kurir ?? 'POS') == 'POS' ? 'selected' : '' }}>POS Indonesia (Mitra Utama)</option>
                            <option value="JNE" {{ ($paspor->jenis_kurir ?? 'POS') == 'JNE' ? 'selected' : '' }}>JNE Express</option>
                            <option value="TIKI" {{ ($paspor->jenis_kurir ?? 'POS') == 'TIKI' ? 'selected' : '' }}>TIKI (Titipan Kilat)</option>
                            <option value="SICEPAT" {{ ($paspor->jenis_kurir ?? 'POS') == 'SICEPAT' ? 'selected' : '' }}>SiCepat Ekspres</option>
                            <option value="JNT" {{ ($paspor->jenis_kurir ?? 'POS') == 'JNT' ? 'selected' : '' }}>J&T Express</option>
                            <option value="ANTERAJA" {{ ($paspor->jenis_kurir ?? 'POS') == 'ANTERAJA' ? 'selected' : '' }}>AnterAja</option>
                            <option value="LION" {{ ($paspor->jenis_kurir ?? 'POS') == 'LION' ? 'selected' : '' }}>Lion Parcel</option>
                            <option value="NINJA" {{ ($paspor->jenis_kurir ?? 'POS') == 'NINJA' ? 'selected' : '' }}>Ninja Xpress</option>
                            <option value="IDE" {{ ($paspor->jenis_kurir ?? 'POS') == 'IDE' ? 'selected' : '' }}>ID Express</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Layanan</label>
                        <input type="text" name="layanan_kurir" value="{{ $paspor->layanan_kurir ?? 'Reguler' }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold"
                            placeholder="Reguler / YES" />
                    </div>
                </div>

                {{-- Total Pembayaran - editable & prominent --}}
                <div class="bg-green-50 border-2 border-green-300 rounded-xl p-4 mt-3">
                    <label class="block text-sm font-bold text-green-800 mb-2">Total Pembayaran Pemohon (Rp) — <span class="font-normal text-green-700">bisa diedit bebas</span></label>
                    <input type="number" name="total_pembayaran_pemohon" min="0" step="500"
                        value="{{ old('total_pembayaran_pemohon', $paspor->total_pembayaran_pemohon) }}"
                        class="w-full px-4 py-3 border-2 border-green-400 rounded-lg text-lg font-bold text-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
                        placeholder="0" />
                    <p class="text-xs text-green-600 mt-1">Sesuaikan nominal ini sesuai kesepakatan dengan pemohon. Nilai ini yang akan tampil di Struk.</p>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toggleBiayaFields(val) {
            // Always visible now, but we can highlight when status is diproses
            const fields = document.getElementById('biayaFields');
            if (val === 'diproses' || val === 'selesai') {
                fields.style.display = 'block';
            } else {
                fields.style.display = 'block';
            }
        }
    </script>

    <div class="flex gap-3 flex-wrap items-center justify-between">
        <div class="flex gap-3">
            <a href="{{ route('paspor.index') }}"
               class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                Kembali ke Daftar
            </a>
            <a href="{{ route('paspor.create') }}"
               class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                Input Baru
            </a>
            <a href="{{ route('paspor.edit', $paspor->id) }}"
               class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Edit Data
            </a>
            <button type="button" onclick="lacakResiDetail({{ $paspor->id }}, '{{ $paspor->no_resi }}')"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                Lacak Resi
            </button>
            <a href="{{ route('paspor.print', $paspor->id) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Struk
            </a>
        </div>
        
        @if(auth()->user()->role === 'super_admin')
        <form method="POST" action="{{ route('paspor.destroy', $paspor->id) }}" onsubmit="return confirm('Hapus riwayat pengiriman ini? Data akan dipindahkan ke Tong Sampah.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 bg-red-100 text-red-600 hover:bg-red-200 px-4 py-2 rounded-lg text-sm font-semibold transition">
                Hapus Data
            </button>
        </form>
        @endif
    </div>

</div>

<script>
    function lacakResiDetail(id, resi) {
        if (!resi || resi.trim() === '') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Nomor Resi Belum Diinput',
                    text: 'Nomor resi belum diinput pada transaksi ini.',
                    confirmButtonColor: '#1d4ed8'
                });
            } else {
                alert('Nomor resi belum diinput pada transaksi ini.');
            }
            return;
        }
        window.location.href = `/paspor?search=${encodeURIComponent(resi)}`;
    }
</script>
@endsection
