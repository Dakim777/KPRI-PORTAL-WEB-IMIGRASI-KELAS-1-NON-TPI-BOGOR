@extends('layouts.app')
@section('title', 'Edit Transaksi Paspor')
@section('page-title', 'Edit Transaksi Pengiriman Paspor')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Form Edit Transaksi</h3>
            <span class="text-xs font-mono text-gray-500 font-bold">{{ $paspor->id_transaksi }}</span>
        </div>
        
        <div class="px-6 py-5">
            <form action="{{ route('paspor.update', $paspor->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                {{-- Bagian Transaksi Dasar --}}
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-4 border-b pb-2">Informasi Transaksi Dasar</h4>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Nama Penerima <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_penerima" value="{{ old('nama_penerima', $paspor->nama_penerima) }}" required
                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama_penerima') border-red-400 @enderror">
                            @error('nama_penerima')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Tanggal Transaksi <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_transaksi" value="{{ old('tanggal_transaksi', $paspor->tanggal_transaksi ? $paspor->tanggal_transaksi->format('Y-m-d') : '') }}" required
                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Status Pengiriman <span class="text-red-500">*</span></label>
                            <select name="status_pengiriman" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach(['transaksi', 'diproses', 'selesai', 'batal'] as $s)
                                    <option value="{{ $s }}" {{ old('status_pengiriman', $paspor->status_pengiriman) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Total Pembayaran - highlighted editable box --}}
                    <div class="bg-green-50 border-2 border-green-300 rounded-xl p-4 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-bold text-green-800">Total Pembayaran Pemohon (Rp)</label>
                            <button type="button" id="btnResetTotal"
                                onclick="resetTotal()"
                                class="text-xs text-green-600 underline hover:text-green-800 transition">
                                Hitung Ulang Otomatis
                            </button>
                        </div>
                        <input type="number" id="total_pembayaran_pemohon" name="total_pembayaran_pemohon"
                            value="{{ old('total_pembayaran_pemohon', $paspor->total_pembayaran_pemohon) }}"
                            required min="0"
                            class="w-full px-4 py-3 border-2 border-green-400 rounded-lg text-xl font-bold text-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
                            placeholder="0" />
                        <p class="text-xs text-green-600 mt-1.5">Edit bebas sesuai kesepakatan, atau klik <strong>Hitung Ulang Otomatis</strong> untuk menghitung dari biaya-biaya di bawah.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">No. HP (Opsional)</label>
                            <input type="text" name="no_hp" value="{{ old('no_hp', $paspor->no_hp) }}" placeholder="08..."
                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Metode Pembayaran (Opsional)</label>
                            <select name="metode_pembayaran" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Metode --</option>
                                <option value="Cash" {{ old('metode_pembayaran', $paspor->metode_pembayaran) == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Transfer" {{ old('metode_pembayaran', $paspor->metode_pembayaran) == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="QRIS" {{ old('metode_pembayaran', $paspor->metode_pembayaran) == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Alamat Tujuan Pengiriman <span class="text-red-500">*</span></label>
                        <textarea name="alamat_tujuan_pengiriman" rows="2" required class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('alamat_tujuan_pengiriman', $paspor->alamat_tujuan_pengiriman) }}</textarea>
                    </div>
                </div>

                {{-- Daftar Paspor --}}
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-4 border-b pb-2">Daftar Paspor</h4>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Jumlah Paspor <span class="text-red-500">*</span></label>
                        <input type="number" id="jumlah_paspor" name="jumlah_paspor" value="{{ old('jumlah_paspor', $paspor->jumlah_paspor) }}" required min="1"
                               class="w-full lg:w-1/3 px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div id="nama-paspor-container" class="space-y-3">
                        @php $oldList = old('nama_paspor_list', is_array($paspor->nama_paspor_list) ? $paspor->nama_paspor_list : json_decode($paspor->nama_paspor_list, true) ?? []); @endphp
                        @foreach($oldList as $index => $nama)
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold shrink-0">{{ $loop->iteration }}</span>
                                <input type="text" name="nama_paspor_list[]" value="{{ $nama }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Form Proses Pengiriman --}}
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h4 class="text-sm font-semibold text-blue-800 mb-4 border-b border-blue-200 pb-2">Detail Pengiriman (Diisi saat Diproses/Selesai)</h4>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-blue-800 uppercase tracking-wide mb-1.5">No. Resi</label>
                            <div class="flex items-center gap-2">
                                <input type="text" id="no_resi" name="no_resi" value="{{ old('no_resi', $paspor->no_resi) }}"
                                       class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                                       placeholder="Scan atau ketik nomor resi...">
                                <x-barcode-scanner targetId="no_resi" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-blue-800 uppercase tracking-wide mb-1.5">Biaya Kurir / Resi (Rp)</label>
                            <input type="number" id="biaya_kurir" name="biaya_kurir" value="{{ old('biaya_kurir', $paspor->biaya_kurir) }}" min="0"
                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-blue-800 uppercase tracking-wide mb-1.5">Handling Fee (Rp)</label>
                            <input type="number" id="handling_fee" name="handling_fee" value="{{ old('handling_fee', $paspor->handling_fee) }}" min="0"
                                   class="harga-input w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-blue-800 uppercase tracking-wide mb-1.5">Biaya Modal Packaging (Rp)</label>
                            <input type="number" id="biaya_modal_packaging" name="biaya_modal_packaging" value="{{ old('biaya_modal_packaging', $paspor->biaya_modal_packaging) }}" min="0"
                                   class="harga-input w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="harga_materai" class="block text-xs font-semibold text-blue-800 uppercase tracking-wide mb-1.5">Biaya Materai (Rp)</label>
                            <input type="number" id="harga_materai" name="harga_materai" value="{{ old('harga_materai', $paspor->harga_materai) }}" min="0"
                                class="harga-input w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="harga_surat_kuasa" class="block text-xs font-semibold text-blue-800 uppercase tracking-wide mb-1.5">Biaya Surat Kuasa (Rp)</label>
                            <input type="number" id="harga_surat_kuasa" name="harga_surat_kuasa" value="{{ old('harga_surat_kuasa', $paspor->harga_surat_kuasa) }}" min="0"
                                class="harga-input w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-blue-800 uppercase tracking-wide mb-1.5">Estimasi Tanggal Selesai</label>
                            <input type="date" name="estimasi_tanggal_selesai" value="{{ old('estimasi_tanggal_selesai', $paspor->estimasi_tanggal_selesai ? $paspor->estimasi_tanggal_selesai->format('Y-m-d') : '') }}"
                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-blue-800 uppercase tracking-wide mb-1.5">Tanggal Pengiriman Paspor</label>
                            <input type="date" name="tanggal_pengiriman_paspor" value="{{ old('tanggal_pengiriman_paspor', $paspor->tanggal_pengiriman_paspor ? $paspor->tanggal_pengiriman_paspor->format('Y-m-d') : '') }}"
                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-blue-800 uppercase tracking-wide mb-1.5">Tanggal Paspor Diterima</label>
                            <input type="date" name="tanggal_paspor_diterima" value="{{ old('tanggal_paspor_diterima', $paspor->tanggal_paspor_diterima ? $paspor->tanggal_paspor_diterima->format('Y-m-d') : '') }}"
                                   class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex gap-3">
                    <button type="submit" class="text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition"
                            style="background:#0D1F3C" onmouseover="this.style.background='#1A3557'" onmouseout="this.style.background='#0D1F3C'">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('paspor.show', $paspor->id) }}" class="px-6 py-2.5 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const jumlahInput       = document.getElementById('jumlah_paspor');
    const container         = document.getElementById('nama-paspor-container');
    const biayaKurir        = document.getElementById('biaya_kurir');
    const biayaPackaging    = document.getElementById('biaya_modal_packaging');
    const handlingFee       = document.getElementById('handling_fee');
    const totalPembayaran   = document.getElementById('total_pembayaran_pemohon');
    const hargaMaterai      = document.getElementById('harga_materai');
    const hargaSuratKuasa   = document.getElementById('harga_surat_kuasa');

    // Auto-update materai & surat kuasa when jumlah changes
    function syncAutoFields() {
        let count = parseInt(jumlahInput?.value) || 0;
        if (hargaMaterai && !hargaMaterai.dataset.manualEdit) hargaMaterai.value = count * 12000;
        if (hargaSuratKuasa && !hargaSuratKuasa.dataset.manualEdit) hargaSuratKuasa.value = count * 1000;
    }

    // Mark field as manually edited so jumlah change won't overwrite it
    if (hargaMaterai) hargaMaterai.addEventListener('input', () => { hargaMaterai.dataset.manualEdit = '1'; });
    if (hargaSuratKuasa) hargaSuratKuasa.addEventListener('input', () => { hargaSuratKuasa.dataset.manualEdit = '1'; });

    // Hitung total dari semua komponen biaya
    function hitungTotal() {
        let kurir   = parseFloat(biayaKurir?.value)     || 0;
        let pack    = parseFloat(biayaPackaging?.value) || 0;
        let fee     = parseFloat(handlingFee?.value)    || 0;
        let materai = parseFloat(hargaMaterai?.value)   || 0;
        let sk      = parseFloat(hargaSuratKuasa?.value)|| 0;
        if (totalPembayaran) totalPembayaran.value = kurir + pack + fee + materai + sk;
    }

    // Expose resetTotal globally for the "Hitung Ulang" button
    window.resetTotal = function() {
        if (hargaMaterai) delete hargaMaterai.dataset.manualEdit;
        if (hargaSuratKuasa) delete hargaSuratKuasa.dataset.manualEdit;
        syncAutoFields();
        hitungTotal();
    };

    // Auto-calc when any biaya field changes
    [biayaKurir, biayaPackaging, handlingFee, hargaMaterai, hargaSuratKuasa].forEach(el => {
        if (el) el.addEventListener('input', hitungTotal);
    });

    // Render nama paspor inputs
    function renderNamaInputs() {
        let count = parseInt(jumlahInput.value) || 0;
        let existingInputs = container.querySelectorAll('input[type="text"]');
        let values = Array.from(existingInputs).map(inp => inp.value);
        container.innerHTML = '';
        for (let i = 1; i <= count; i++) {
            let val = values[i-1] !== undefined ? values[i-1] : '';
            container.innerHTML += `
                <div class="flex items-center gap-3 mt-3">
                    <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold shrink-0">${i}</span>
                    <input type="text" name="nama_paspor_list[]" value="${val}" required placeholder="Nama di paspor ke-${i}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            `;
        }
    }

    if (jumlahInput) {
        jumlahInput.addEventListener('input', () => {
            renderNamaInputs();
            syncAutoFields();
            hitungTotal();
        });
    }
});
</script>
@endpush
