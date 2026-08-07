@extends('layouts.app')

@section('title', 'Input Data Pengiriman Paspor')
@section('page-title', 'Input Pengiriman Paspor Baru')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Form Data Pengiriman Paspor (Transaksi Baru)</h3>
            <p class="text-xs text-gray-400 mt-0.5">Semua field bertanda <span class="text-red-500">*</span> wajib diisi.</p>
        </div>

        <form method="POST" action="{{ route('paspor.store') }}" class="px-6 py-6 space-y-5">
            @csrf

            {{-- ─ Nama Penerima Transaksi ─────────────────────────────────────────────────── --}}
            <div>
                <label for="nama_penerima" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama Penerima (Pemohon Transaksi) <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama_penerima" name="nama_penerima"
                    value="{{ old('nama_penerima') }}" required
                    placeholder="Nama lengkap pemohon atau wakil keluarga"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama_penerima') border-red-400 @enderror" />
                @error('nama_penerima')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- ─ Tanggal Transaksi ─────────────────────────────────────────────────── --}}
            <div>
                <label for="tanggal_transaksi" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Tanggal Transaksi <span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal_transaksi" name="tanggal_transaksi"
                    value="{{ old('tanggal_transaksi', date('Y-m-d')) }}" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tanggal_transaksi') border-red-400 @enderror" />
                @error('tanggal_transaksi')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- ─ Jumlah Paspor & Nama Dinamis ────────────────────────────────────────── --}}
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <label for="jumlah_paspor" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Jumlah Paspor <span class="text-red-500">*</span>
                </label>
                <input type="number" id="jumlah_paspor" name="jumlah_paspor"
                    value="{{ old('jumlah_paspor') }}" required min="1"
                    placeholder="Contoh: 4"
                    class="w-full lg:w-1/3 px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('jumlah_paspor') border-red-400 @enderror" />
                @error('jumlah_paspor')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                
                <div id="nama-paspor-container" class="mt-4 space-y-3">
                    {{-- Akan diisi dinamis oleh Javascript --}}
                </div>
            </div>

            {{-- ─ Alamat Tujuan ────────────────────────────────────────────────────────── --}}
            <div>
                <label for="alamat_tujuan_pengiriman" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Alamat Tujuan Pengiriman <span class="text-red-500">*</span>
                </label>
                <textarea id="alamat_tujuan_pengiriman" name="alamat_tujuan_pengiriman" rows="3" required
                    placeholder="Alamat lengkap tujuan pengiriman..."
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('alamat_tujuan_pengiriman') border-red-400 @enderror">{{ old('alamat_tujuan_pengiriman') }}</textarea>
                @error('alamat_tujuan_pengiriman')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- ─ Data Tambahan (Opsional) ────────────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-1.5">No. HP WhatsApp <span class="text-xs text-indigo-600 font-normal">(Penting untuk Notifikasi Resi)</span></label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('no_hp') border-red-400 @enderror" />
                </div>
                <div>
                    <label for="metode_pembayaran" class="block text-sm font-medium text-gray-700 mb-1.5">Metode Pembayaran (Opsional)</label>
                    <select id="metode_pembayaran" name="metode_pembayaran" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Metode --</option>
                        <option value="Cash" {{ old('metode_pembayaran') == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Transfer" {{ old('metode_pembayaran') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="QRIS" {{ old('metode_pembayaran') == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="estimasi_tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1.5">Estimasi Tanggal Selesai (Opsional)</label>
                    <input type="date" id="estimasi_tanggal_selesai" name="estimasi_tanggal_selesai" value="{{ old('estimasi_tanggal_selesai') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('estimasi_tanggal_selesai') border-red-400 @enderror" />
                </div>
            </div>

            {{-- ─ Rincian Harga (Opsional di awal) ────────────────────────────────────────────────────────── --}}
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h4 class="text-sm font-semibold text-blue-800 mb-4 border-b border-blue-200 pb-2">Rincian Harga (Opsional di awal transaksi)</h4>
                
                {{-- ─ Widget Cek Tarif Ongkir Otomatis ────────────────── --}}
                <div class="mb-4 p-3 bg-blue-50 rounded-xl border border-blue-200 text-xs">
                    <div class="flex items-center gap-1.5 font-bold text-blue-900 mb-3">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Cek Tarif Ongkir Otomatis <span class="font-normal text-blue-500">&mdash; ketik nama kota untuk mencari</span></span>
                    </div>
                    <div class="flex flex-wrap items-end gap-2 w-full">
                        {{-- Kurir: native select, cukup 9 pilihan --}}
                        <div class="flex flex-col gap-1">
                            <label class="text-blue-700 font-semibold uppercase tracking-wide" style="font-size:0.65rem;">Ekspedisi</label>
                            <select id="rajaongkir_courier"
                                class="border border-blue-300 rounded-lg px-2.5 py-1.5 text-xs bg-white font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                style="min-width:170px; height:34px;">
                                <option value="pos">POS Indonesia (Mitra Utama)</option>
                                <option value="jne">JNE Express</option>
                                <option value="tiki">TIKI (Titipan Kilat)</option>
                                <option value="sicepat">SiCepat Ekspres</option>
                                <option value="jnt">J&T Express</option>
                                <option value="anteraja">AnterAja</option>
                                <option value="lion">Lion Parcel</option>
                                <option value="ninja">Ninja Xpress</option>
                                <option value="ide">ID Express</option>
                            </select>
                        </div>
                        {{-- Kota Tujuan: Tom Select searchable --}}
                        <div class="flex flex-col gap-1" style="min-width:220px; max-width:280px; flex:1;">
                            <label class="text-blue-700 font-semibold uppercase tracking-wide" style="font-size:0.65rem;">Kota Tujuan</label>
                            <select id="rajaongkir_destination">
                                <option value="">-- Pilih kota tujuan --</option>
                                @if(isset($daftarKota) && is_array($daftarKota))
                                    @foreach($daftarKota as $kota)
                                        <option value="{{ $kota['city_id'] }}">{{ $kota['city_name'] }}</option>
                                    @endforeach
                                @else
                                    <option value="dist_32.71.01">Kota Bogor (Bogor Tengah)</option>
                                    <option value="dist_32.01.01">Kab. Bogor (Cibinong)</option>
                                    <option value="dist_32.76.01">Kota Depok (Pancoran Mas)</option>
                                    <option value="dist_31.73.01">Jakarta Pusat (Gambir)</option>
                                    <option value="dist_31.71.01">Jakarta Selatan (Kebayoran Baru)</option>
                                    <option value="dist_32.75.01">Kota Bekasi (Bekasi Barat)</option>
                                    <option value="dist_36.71.01">Kota Tangerang (Tangerang)</option>
                                    <option value="dist_32.73.01">Kota Bandung (Bandung Wetan)</option>
                                @endif
                            </select>
                        </div>
                        <button type="button" onclick="autoCekOngkir(event)"
                            class="bg-blue-700 hover:bg-blue-800 text-white font-bold px-4 rounded-lg transition flex items-center gap-1.5 shadow-sm shrink-0"
                            style="height:34px; font-size:0.75rem;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Cek Tarif
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="jenis_kurir" class="block text-xs font-semibold text-blue-800 uppercase mb-1.5">Ekspedisi Pilihan</label>
                        <select id="jenis_kurir" name="jenis_kurir" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                            <option value="POS" {{ old('jenis_kurir') == 'POS' ? 'selected' : '' }}>POS Indonesia (Mitra Utama)</option>
                            <option value="JNE" {{ old('jenis_kurir') == 'JNE' ? 'selected' : '' }}>JNE Express</option>
                            <option value="TIKI" {{ old('jenis_kurir') == 'TIKI' ? 'selected' : '' }}>TIKI (Titipan Kilat)</option>
                            <option value="SICEPAT" {{ old('jenis_kurir') == 'SICEPAT' ? 'selected' : '' }}>SiCepat Ekspres</option>
                            <option value="JNT" {{ old('jenis_kurir') == 'JNT' ? 'selected' : '' }}>J&T Express</option>
                            <option value="ANTERAJA" {{ old('jenis_kurir') == 'ANTERAJA' ? 'selected' : '' }}>AnterAja</option>
                            <option value="LION" {{ old('jenis_kurir') == 'LION' ? 'selected' : '' }}>Lion Parcel</option>
                            <option value="NINJA" {{ old('jenis_kurir') == 'NINJA' ? 'selected' : '' }}>Ninja Xpress</option>
                            <option value="IDE" {{ old('jenis_kurir') == 'IDE' ? 'selected' : '' }}>ID Express</option>
                        </select>
                    </div>
                    <div>
                        <label for="layanan_kurir" class="block text-xs font-semibold text-blue-800 uppercase mb-1.5">Jenis Layanan</label>
                        <input type="text" id="layanan_kurir" name="layanan_kurir" value="{{ old('layanan_kurir', 'Reguler') }}" placeholder="Contoh: Reguler / YES / Kilat Khusus"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="biaya_kurir" class="block text-xs font-semibold text-blue-800 uppercase mb-1.5">Layanan Kurir (Rp)</label>
                        <input type="number" id="biaya_kurir" name="biaya_kurir" value="{{ old('biaya_kurir', 0) }}" min="0"
                            class="harga-input w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="biaya_modal_packaging" class="block text-xs font-semibold text-blue-800 uppercase mb-1.5">Safe Packaging (Rp)</label>
                        <input type="number" id="biaya_modal_packaging" name="biaya_modal_packaging" value="{{ old('biaya_modal_packaging', 1000) }}" min="0"
                            class="harga-input w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="handling_fee" class="block text-xs font-semibold text-blue-800 uppercase mb-1.5">Handling Fee (Rp)</label>
                        <input type="number" id="handling_fee" name="handling_fee" value="{{ old('handling_fee', 0) }}" min="0"
                            class="harga-input w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="harga_materai" class="block text-xs font-semibold text-blue-800 uppercase mb-1.5">Biaya Materai (Rp)</label>
                        <input type="number" id="harga_materai" name="harga_materai" value="{{ old('harga_materai', 0) }}" min="0"
                            class="harga-input w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="harga_surat_kuasa" class="block text-xs font-semibold text-blue-800 uppercase mb-1.5">Biaya Surat Kuasa (Rp)</label>
                        <input type="number" id="harga_surat_kuasa" name="harga_surat_kuasa" value="{{ old('harga_surat_kuasa', 0) }}" min="0"
                            class="harga-input w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- ─ Total Pembayaran Pemohon ────────────────────────────────── --}}
                <div class="mt-4 pt-4 border-t border-blue-200">
                    <label for="total_pembayaran_pemohon" class="block text-sm font-semibold text-blue-900 mb-1.5">
                        Total Pembayaran Pemohon (Bisa diedit)
                    </label>
                    <input type="number" id="total_pembayaran_pemohon" name="total_pembayaran_pemohon"
                        value="{{ old('total_pembayaran_pemohon') }}" min="0"
                        class="w-full px-4 py-2.5 border border-blue-300 rounded-lg text-sm font-bold text-green-700 bg-white focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>
            </div>

            {{-- ─ Actions ───────────────────────────────────────────────────────── --}}
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                    class="bg-blue-700 hover:bg-blue-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Simpan Data
                </button>
                <a href="{{ route('paspor.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const jumlahInput = document.getElementById('jumlah_paspor');
    const container = document.getElementById('nama-paspor-container');
    const biayaKurir = document.getElementById('biaya_kurir');
    const biayaPackaging = document.getElementById('biaya_modal_packaging');
    const handlingFee = document.getElementById('handling_fee');
    const totalPembayaran = document.getElementById('total_pembayaran_pemohon');
    const hargaMaterai = document.getElementById('harga_materai');
    const hargaSuratKuasa = document.getElementById('harga_surat_kuasa');
    
    // Track if user manually changed total
    let totalManuallyEdited = false;
    let materaiEdited = false;
    let skEdited = false;

    totalPembayaran.addEventListener('input', () => { totalManuallyEdited = true; });
    hargaMaterai.addEventListener('input', () => { materaiEdited = true; hitungTotal(); });
    hargaSuratKuasa.addEventListener('input', () => { skEdited = true; hitungTotal(); });

    function hitungTotal() {
        let count = parseInt(jumlahInput.value) || 0;
        
        if (!materaiEdited) {
            hargaMaterai.value = count * 12000;
        }
        if (!skEdited) {
            hargaSuratKuasa.value = count * 1000;
        }

        if (!totalManuallyEdited) {
            let kurir = parseFloat(biayaKurir.value) || 0;
            let pack = parseFloat(biayaPackaging.value) || 0;
            let fee = parseFloat(handlingFee.value) || 0;
            let materai = parseFloat(hargaMaterai.value) || 0;
            let sk = parseFloat(hargaSuratKuasa.value) || 0;
            
            let finalTotal = kurir + pack + fee + materai + sk;
            totalPembayaran.value = finalTotal;
        }
    }

    function renderNamaInputs() {
        let count = parseInt(jumlahInput.value) || 0;
        
        // Cache existing values
        let existingInputs = container.querySelectorAll('input[type="text"]');
        let values = Array.from(existingInputs).map(inp => inp.value);
        
        container.innerHTML = '';
        if (count > 0) {
            container.innerHTML = `<p class="text-xs font-semibold text-blue-700 mb-2">Silakan isi rincian ${count} nama pemilik paspor:</p>`;
            for(let i=1; i<=count; i++) {
                let val = values[i-1] !== undefined ? values[i-1] : '';
                container.innerHTML += `
                    <div class="mb-2 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold shrink-0">${i}</span>
                        <input type="text" name="nama_paspor_list[]" value="${val}" required placeholder="Nama di paspor ke-${i}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                `;
            }
        }
        
        hitungTotal();
    }

    jumlahInput.addEventListener('input', renderNamaInputs);
    document.querySelectorAll('.harga-input').forEach(el => {
        el.addEventListener('input', function() {
            totalManuallyEdited = false; // Reset flag when other prices change
            hitungTotal();
        });
    });

    // Render on load if old value exists
    if(jumlahInput.value) {
        renderNamaInputs();
    }
});

function autoCekOngkir(e) {
    const dest = document.getElementById('rajaongkir_destination').value;
    if (!dest) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Tujuan Kosong',
                text: 'Silakan pilih kota tujuan pengiriman terlebih dahulu!',
                confirmButtonColor: '#1d4ed8'
            });
        } else {
            alert('Silakan pilih kota tujuan pengiriman terlebih dahulu!');
        }
        return;
    }
    const courier = document.getElementById('rajaongkir_courier').value || 'pos';
    const btn = e.currentTarget || e.target;
    const oldText = btn.innerHTML;
    btn.innerHTML = 'Memeriksa...';
    btn.disabled = true;

    fetch(`/paspor/api/cek-ongkir?destination=${dest}&courier=${courier}&weight=200`)
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = oldText;
            btn.disabled = false;
            if (data.status === 'success' && data.data) {
                const cost = data.data.biaya_ongkir || 0;
                const courierField = document.getElementById('biaya_kurir');
                courierField.value = cost;
                courierField.dispatchEvent(new Event('input'));
                const jkField = document.getElementById('jenis_kurir');
                if (jkField && data.data.courier) jkField.value = data.data.courier.toUpperCase();
                const lkField = document.getElementById('layanan_kurir');
                if (lkField && data.data.service) lkField.value = data.data.service;

                Swal.fire({
                    title: 'Tarif Ongkir Ditemukan',
                    html: `
                        <div class="text-left font-sans mt-2">
                            <div class="bg-blue-50/80 border border-blue-100 rounded-xl p-4 mb-4 text-center">
                                <span class="text-xs font-semibold text-imigr uppercase tracking-wider block mb-1">Ekspedisi & Layanan</span>
                                <span class="text-lg font-bold text-gray-900">${data.data.courier} &mdash; ${data.data.service}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mb-3 text-center">
                                <div class="bg-gray-50 border border-gray-100 rounded-lg p-3">
                                    <span class="text-xs text-gray-500 block mb-1">Biaya Ongkir</span>
                                    <span class="text-base font-bold text-imigr">Rp ${cost.toLocaleString('id-ID')}</span>
                                </div>
                                <div class="bg-gray-50 border border-gray-100 rounded-lg p-3">
                                    <span class="text-xs text-gray-500 block mb-1">Estimasi Tiba (ETD)</span>
                                    <span class="text-base font-bold text-gray-700">${data.data.etd || '-'}</span>
                                </div>
                            </div>
                            ${data.is_mock ? '<p class="text-xs text-amber-600 bg-amber-50 p-2.5 rounded-lg border border-amber-200 text-center font-medium mt-2">(Mode Simulasi - API Key BinderByte belum dipasang di .env)</p>' : ''}
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'Terapkan Tarif Ini',
                    confirmButtonColor: '#1A3A6B',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-gray-100 font-sans p-6',
                        confirmButton: 'rounded-xl px-6 py-2.5 font-semibold text-sm shadow-md'
                    }
                });
            } else {
                Swal.fire({
                    title: 'Layanan Kurir Tidak Tersedia',
                    html: `<p class="text-sm text-gray-600 font-sans leading-relaxed text-center py-2">${data.message || 'Layanan untuk kurir pilihan ini tidak menyediakan rute pengantaran ke daerah tersebut.'}</p>`,
                    icon: 'warning',
                    confirmButtonText: 'Pilih Ekspedisi / Daerah Lain',
                    confirmButtonColor: '#d33',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-gray-100 font-sans p-6',
                        confirmButton: 'rounded-xl px-6 py-2.5 font-semibold text-sm shadow-md'
                    }
                });
            }
        })
        .catch(err => {
            btn.innerHTML = oldText;
            btn.disabled = false;
            Swal.fire({
                title: 'Gangguan Koneksi',
                text: 'Terjadi kesalahan koneksi atau waktu habis saat menghubungi server ekspedisi resmi. Silakan coba kembali.',
                icon: 'error',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#d33',
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-gray-100 font-sans p-6',
                    confirmButton: 'rounded-xl px-6 py-2.5 font-semibold text-sm shadow-md'
                }
            });
        });
}
</script>


{{-- Tom Select: hanya untuk field kota tujuan (524+ kota, searchable) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
<style>
    /* ─── Styling Tom Select agar matching dengan desain form ─── */
    #rajaongkir_destination-ts-wrapper .ts-control {
        border: 1px solid #93c5fd;
        border-radius: 0.5rem;
        padding: 0 10px;
        height: 34px;
        min-height: 34px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #1e3a5f;
        background: #fff;
        box-shadow: none;
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 4px;
    }
    #rajaongkir_destination-ts-wrapper .ts-control:focus,
    #rajaongkir_destination-ts-wrapper.focus .ts-control {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 2px rgba(29,78,216,0.12);
        outline: none;
    }
    /* Input teks pencarian di dalam control */
    #rajaongkir_destination-ts-wrapper .ts-control input {
        font-size: 0.75rem !important;
        font-weight: 500 !important;
        color: #374151 !important;
        min-width: 80px;
    }
    /* Panah dropdown */
    #rajaongkir_destination-ts-wrapper.single .ts-control::after {
        content: '';
        border: 4px solid transparent;
        border-top-color: #1d4ed8;
        position: absolute;
        right: 10px;
        top: 50%;
        margin-top: 0px;
    }
    /* Dropdown list */
    #rajaongkir_destination-ts-wrapper .ts-dropdown {
        font-size: 0.75rem;
        border: 1px solid #bfdbfe;
        border-radius: 0.5rem;
        box-shadow: 0 4px 16px rgba(30,58,138,0.10);
        z-index: 99999;
        margin-top: 2px;
    }
    #rajaongkir_destination-ts-wrapper .ts-dropdown .ts-dropdown-content {
        max-height: 200px;
        padding: 4px 0;
    }
    #rajaongkir_destination-ts-wrapper .ts-dropdown .option {
        font-size: 0.72rem;
        padding: 5px 12px;
        color: #374151;
    }
    #rajaongkir_destination-ts-wrapper .ts-dropdown .option:hover,
    #rajaongkir_destination-ts-wrapper .ts-dropdown .option.active {
        background: #eff6ff;
        color: #1d4ed8;
    }
    /* Hilangkan x (clear) button - tidak diperlukan */
    #rajaongkir_destination-ts-wrapper .ts-control .clear-button {
        display: none !important;
    }
    /* Placeholder text */
    #rajaongkir_destination-ts-wrapper .ts-control .placeholder {
        color: #9ca3af;
        font-weight: 400;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function () {
    function doInit() {
        var destEl = document.getElementById('rajaongkir_destination');
        if (destEl && !destEl.tomselect) {
            new TomSelect(destEl, {
                maxItems: 1,
                create: false,
                sortField: false,
                searchField: ['text'],
                placeholder: 'Ketik nama kota/kabupaten...',
                allowEmptyOption: true,
                maxOptions: 80,
                render: {
                    no_results: function(data, escape) {
                        return '<div style="padding:8px 12px;color:#9ca3af;font-size:0.72rem;font-style:italic;">Kota tidak ditemukan. Coba kata kunci lain.</div>';
                    }
                }
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', doInit);
    } else {
        doInit();
    }
})();
</script>
@endpush
