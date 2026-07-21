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
                    <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-1.5">No. HP (Opsional)</label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="08..."
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
                
                {{-- ─ Widget Cek Tarif RajaOngkir Otomatis ────────────────── --}}
                <div class="mb-4 p-3 bg-blue-100/70 rounded-xl border border-blue-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-1.5 font-bold text-blue-900">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Cek Tarif RajaOngkir Otomatis:</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-end">
                        <select id="rajaongkir_courier" class="border border-blue-300 rounded-lg px-2.5 py-1.5 text-xs bg-white font-semibold focus:ring-2 focus:ring-blue-500">
                            <option value="pos">POS Indonesia</option>
                            <option value="jne">JNE Express</option>
                        </select>
                        <select id="rajaongkir_destination" class="border border-blue-300 rounded-lg px-2.5 py-1.5 text-xs max-w-[240px] bg-white font-semibold focus:ring-2 focus:ring-blue-500 truncate">
                            @if(isset($daftarKota) && is_array($daftarKota))
                                @foreach($daftarKota as $kota)
                                    <option value="{{ $kota['city_id'] }}" {{ $kota['city_id'] == '149' ? 'selected' : '' }}>
                                        {{ $kota['city_name'] }}
                                    </option>
                                @endforeach
                            @else
                                <option value="149" selected>Kota Bogor (Jawa Barat)</option>
                                <option value="80">Kabupaten Bogor (Jawa Barat)</option>
                                <option value="114">Kota Depok (Jawa Barat)</option>
                                <option value="152">Kota Jakarta Pusat (DKI Jakarta)</option>
                                <option value="153">Kota Jakarta Selatan (DKI Jakarta)</option>
                                <option value="40">Kota Bekasi (Jawa Barat)</option>
                                <option value="455">Kota Tangerang (Banten)</option>
                                <option value="23">Kota Bandung (Jawa Barat)</option>
                            @endif
                        </select>
                        <button type="button" onclick="autoCekOngkir(event)" class="bg-blue-700 hover:bg-blue-800 text-white font-bold px-3 py-1.5 rounded-lg transition flex items-center gap-1 shadow-sm">
                            Cek Tarif
                        </button>
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
    const dest = document.getElementById('rajaongkir_destination').value || '149';
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
                alert(`Tarif ${data.data.courier} (${data.data.service}): Rp ${cost.toLocaleString('id-ID')} (ETD: ${data.data.etd || '-'})`);
            } else {
                alert('Gagal mengambil tarif: ' + (data.message || 'Periksa ID kota tujuan atau koneksi API.'));
            }
        })
        .catch(err => {
            btn.innerHTML = oldText;
            btn.disabled = false;
            alert('Terjadi kesalahan koneksi saat mengecek ongkir.');
        });
}
</script>
@endpush
