@extends('layouts.app')

@section('title', 'Kasir POS Kantin')
@section('page-title', 'Kasir / Point of Sale')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Product Grid --}}
    <div class="lg:col-span-2 space-y-4">
    {{-- Professional Scanner Bar --}}
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-3 flex items-center gap-3 transition-all focus-within:border-blue-500 focus-within:shadow-md">
            <div class="bg-blue-600 p-2.5 rounded-lg text-white shadow-sm shrink-0">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 6h2v12H4V6zm3 0h1v12H7V6zm2 0h3v12H9V6zm4 0h1v12h-1V6zm2 0h2v12h-2V6zm3 0h2v12h-2V6z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-[11px] font-bold text-blue-800 mb-1 uppercase tracking-wider flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Scanner Ready
                </p>
                <input id="searchProduk" type="text" placeholder="Tembak barcode atau cari nama produk disini..." oninput="filterProduk()" autocomplete="off" autofocus
                    class="w-full bg-white border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm text-gray-800 font-medium placeholder-gray-400 transition" />
            </div>
            {{-- Webcam scan button --}}
            <div class="shrink-0 w-36">
                <p class="text-[10px] font-bold text-blue-700 mb-1 uppercase tracking-wider text-center">atau webcam</p>
                <x-barcode-scanner targetId="searchProduk" />
            </div>
            <div class="hidden sm:block shrink-0 px-3 border-l border-blue-200 text-right">
                <p class="text-xs text-gray-500">Total Produk</p>
                <p class="text-lg font-bold text-blue-800">{{ count($produks) }}</p>
            </div>
        </div>

        {{-- Category Tabs --}}
        <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-100">
            <button type="button" onclick="setCategory('', this)" class="kategori-tab active px-4 py-1.5 rounded-full text-xs font-semibold bg-blue-700 text-white shadow-sm transition">Semua</button>
            @foreach($kategoris as $kat)
                <button type="button" onclick="setCategory('{{ $kat->id }}', this)" class="kategori-tab px-4 py-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">{{ $kat->nama_kategori }}</button>
            @endforeach
        </div>

        <div id="produkGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($produks as $produk)
            <button
                type="button"
                data-id="{{ $produk->id }}"
                data-nama="{{ $produk->nama_produk }}"
                data-barcode="{{ strtolower($produk->barcode ?? '') }}"
                data-harga="{{ $produk->harga }}"
                data-stok="{{ $produk->stok }}"
                data-kategori="{{ $produk->kategori_id }}"
                onclick="tambahItem(this)"
                class="produk-card bg-white rounded-xl border border-gray-200 p-4 text-left hover:border-blue-400 hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="flex items-start justify-between">
                    <p class="text-sm font-semibold text-gray-800 leading-tight pr-2">{{ $produk->nama_produk }}</p>
                    @if($produk->kategori)
                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap">{{ $produk->kategori->nama_kategori }}</span>
                    @endif
                </div>
                <p class="text-blue-700 font-bold text-sm mt-1">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Stok: {{ $produk->stok }}</p>
            </button>
            @endforeach
        </div>
    </div>

    {{-- Cart / Order Summary --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-4">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Keranjang</h3>
                <button onclick="clearCart()" class="text-xs text-red-500 hover:text-red-700">Kosongkan</button>
            </div>

            {{-- Cart Items --}}
            <div id="cartItems" class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                <p id="cartEmpty" class="px-5 py-6 text-center text-gray-400 text-sm">Pilih produk dari sebelah kiri</p>
            </div>

            {{-- Summary --}}
            <div class="px-5 py-4 space-y-3 border-t border-gray-100">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Total</span>
                    <span id="totalHarga" class="font-bold text-gray-800">Rp 0</span>
                </div>

                {{-- Metode Pembayaran --}}
                <div>
                    <label class="text-xs text-gray-500 mb-1.5 block font-medium">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" id="btnCash" onclick="setMetodeBayar('cash')" class="metode-btn active flex flex-col items-center gap-1 border-2 border-blue-600 bg-blue-50 rounded-lg py-2 text-xs font-semibold text-blue-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Cash
                        </button>
                        <button type="button" id="btnQris" onclick="setMetodeBayar('qris')" class="metode-btn flex flex-col items-center gap-1 border-2 border-gray-200 bg-white rounded-lg py-2 text-xs font-semibold text-gray-500 transition hover:border-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            QRIS
                        </button>
                    </div>
                </div>

                {{-- Cash: input jumlah bayar & kembalian --}}
                <div id="cashSection">
                    <label class="text-xs text-gray-500 mb-1 block">Jumlah Bayar (Rp)</label>
                    <input
                        type="number"
                        id="jumlahBayar"
                        min="0"
                        step="1000"
                        placeholder="0"
                        oninput="hitungKembalian()"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <div class="flex justify-between text-sm mt-2">
                        <span class="text-gray-600">Kembalian</span>
                        <span id="kembalian" class="font-bold text-green-600">Rp 0</span>
                    </div>
                </div>

                {{-- QRIS: info bayar pas --}}
                <div id="qrisSection" class="hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                        <p class="text-xs text-blue-700 font-semibold">Tagihan akan sesuai total belanja</p>
                        <p class="text-lg font-bold text-blue-800 mt-1" id="qrisTotal">Rp 0</p>
                        <p class="text-[10px] text-blue-500 mt-1">Silakan arahkan QRIS ke scanner pelanggan</p>
                    </div>
                </div>

                {{-- Hidden form to POST --}}
                <form id="posForm" method="POST" action="{{ route('kantin.pos.simpan') }}">
                    @csrf
                    <input type="hidden" id="formItems" name="items" />
                    <input type="hidden" id="formJumlahBayar" name="jumlah_bayar" />
                    <input type="hidden" id="formMetodeBayar" name="metode_bayar" value="cash" />

                    <button
                        type="button"
                        onclick="submitTransaksi()"
                        id="btnBayar"
                        disabled
                        class="w-full bg-blue-700 hover:bg-blue-800 disabled:opacity-40 disabled:cursor-not-allowed text-white px-4 py-3 rounded-lg text-sm font-semibold transition mt-1"
                    >
                        Bayar Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let cart = {}; // { produkId: { nama, harga, qty, stok } }

    function tambahItem(btn) {
        const id    = btn.dataset.id;
        const nama  = btn.dataset.nama;
        const harga = parseFloat(btn.dataset.harga);
        const stok  = parseInt(btn.dataset.stok);

        if (!cart[id]) {
            cart[id] = { nama, harga, qty: 0, stok };
        }

        if (cart[id].qty >= stok) {
            alert(`Stok "${nama}" tidak mencukupi (maks. ${stok}).`);
            return;
        }

        cart[id].qty++;
        renderCart();
    }

    function kurangItem(id) {
        if (!cart[id]) return;
        cart[id].qty--;
        if (cart[id].qty <= 0) delete cart[id];
        renderCart();
    }

    function clearCart() {
        cart = {};
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const empty     = document.getElementById('cartEmpty');
        const keys      = Object.keys(cart);

        if (keys.length === 0) {
            container.innerHTML = '<p class="px-5 py-6 text-center text-gray-400 text-sm">Pilih produk dari sebelah kiri</p>';
            updateTotals();
            return;
        }

        container.innerHTML = keys.map(id => {
            const item     = cart[id];
            const subtotal = item.harga * item.qty;
            return `
            <div class="px-5 py-3 flex items-center justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">${item.nama}</p>
                    <p class="text-xs text-gray-400">Rp ${item.harga.toLocaleString('id-ID')} × ${item.qty}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button onclick="kurangItem('${id}')" class="w-6 h-6 rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-600 flex items-center justify-center text-sm font-bold transition">−</button>
                    <span class="text-xs font-bold w-5 text-center">${item.qty}</span>
                    <button onclick="tambahItemById('${id}')" class="w-6 h-6 rounded-full bg-gray-100 hover:bg-green-100 hover:text-green-600 flex items-center justify-center text-sm font-bold transition">+</button>
                    <span class="text-xs font-semibold text-blue-700 w-24 text-right">Rp ${subtotal.toLocaleString('id-ID')}</span>
                </div>
            </div>`;
        }).join('');

        updateTotals();
    }

    function tambahItemById(id) {
        const item = cart[id];
        if (!item) return;
        if (item.qty >= item.stok) {
            alert(`Stok "${item.nama}" tidak mencukupi (maks. ${item.stok}).`);
            return;
        }
        item.qty++;
        renderCart();
    }

    function updateTotals() {
        const total = Object.values(cart).reduce((sum, i) => sum + i.harga * i.qty, 0);
        document.getElementById('totalHarga').textContent = 'Rp ' + total.toLocaleString('id-ID');
        hitungKembalian();
    }

    // --- Metode Pembayaran ---
    let metodeBayar = 'cash';

    function setMetodeBayar(metode) {
        metodeBayar = metode;
        document.getElementById('formMetodeBayar').value = metode;

        const btnCash = document.getElementById('btnCash');
        const btnQris = document.getElementById('btnQris');
        const cashSection = document.getElementById('cashSection');
        const qrisSection = document.getElementById('qrisSection');

        if (metode === 'cash') {
            btnCash.className = 'metode-btn flex flex-col items-center gap-1 border-2 border-blue-600 bg-blue-50 rounded-lg py-2 text-xs font-semibold text-blue-700 transition';
            btnQris.className = 'metode-btn flex flex-col items-center gap-1 border-2 border-gray-200 bg-white rounded-lg py-2 text-xs font-semibold text-gray-500 transition hover:border-blue-400';
            cashSection.classList.remove('hidden');
            qrisSection.classList.add('hidden');
        } else {
            btnQris.className = 'metode-btn flex flex-col items-center gap-1 border-2 border-blue-600 bg-blue-50 rounded-lg py-2 text-xs font-semibold text-blue-700 transition';
            btnCash.className = 'metode-btn flex flex-col items-center gap-1 border-2 border-gray-200 bg-white rounded-lg py-2 text-xs font-semibold text-gray-500 transition hover:border-blue-400';
            cashSection.classList.add('hidden');
            qrisSection.classList.remove('hidden');
        }
        hitungKembalian();
    }

    function hitungKembalian() {
        const total = Object.values(cart).reduce((sum, i) => sum + i.harga * i.qty, 0);
        const hasItems = Object.keys(cart).length > 0;

        // Update QRIS total display
        document.getElementById('qrisTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');

        if (metodeBayar === 'qris') {
            // QRIS: langsung valid selama ada item
            document.getElementById('btnBayar').disabled = !(hasItems && total > 0);
            return;
        }

        // Cash: hitung kembalian seperti biasa
        const bayar     = parseFloat(document.getElementById('jumlahBayar').value) || 0;
        const kembalian = bayar - total;

        const el = document.getElementById('kembalian');
        el.textContent = 'Rp ' + Math.max(0, kembalian).toLocaleString('id-ID');
        el.className   = kembalian < 0 ? 'font-bold text-red-600' : 'font-bold text-green-600';

        document.getElementById('btnBayar').disabled = !(hasItems && bayar >= total && total > 0);
    }

    function submitTransaksi() {
        const total = Object.values(cart).reduce((sum, i) => sum + i.harga * i.qty, 0);

        if (Object.keys(cart).length === 0) { alert('Keranjang kosong!'); return; }

        let bayar = total; // default untuk QRIS
        if (metodeBayar === 'cash') {
            bayar = parseFloat(document.getElementById('jumlahBayar').value) || 0;
            if (bayar < total) { alert('Jumlah bayar kurang!'); return; }
        }

        const items = Object.entries(cart).map(([id, item]) => ({ id, qty: item.qty }));

        document.getElementById('formItems').value       = JSON.stringify(items);
        document.getElementById('formJumlahBayar').value = bayar;
        document.getElementById('posForm').submit();
    }

    let activeCategory = '';

    function setCategory(id, btnElement) {
        activeCategory = id;
        
        // Update tab styling
        document.querySelectorAll('.kategori-tab').forEach(btn => {
            btn.classList.remove('bg-blue-700', 'text-white', 'font-semibold', 'shadow-sm', 'active');
            btn.classList.add('bg-gray-100', 'text-gray-600', 'font-medium');
        });
        btnElement.classList.remove('bg-gray-100', 'text-gray-600', 'font-medium');
        btnElement.classList.add('bg-blue-700', 'text-white', 'font-semibold', 'shadow-sm', 'active');

        filterProduk();
    }

    function filterProduk() {
        const q     = document.getElementById('searchProduk').value.toLowerCase();
        const cards = document.querySelectorAll('.produk-card');
        
        cards.forEach(card => {
            const matchName = card.dataset.nama.toLowerCase().includes(q);
            const matchCat  = (activeCategory === '') || (card.dataset.kategori === activeCategory);
            
            card.style.display = (matchName && matchCat) ? '' : 'none';
        });
    }

    // --- Audio Beep (Supermarket Scanner Style) ---
    function playBeep() {
        try {
            const ctx  = new (window.AudioContext || window.webkitAudioContext)();
            const t    = ctx.currentTime;
            const freq = 1880; // Frekuensi tinggi khas scanner kasir supermarket

            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.type            = 'sine';
            osc.frequency.value = freq;

            // Karakter suara: keras di awal, langsung putus tajam (seperti scanner)
            gain.gain.setValueAtTime(0, t);
            gain.gain.linearRampToValueAtTime(0.5, t + 0.005); // naik sangat cepat
            gain.gain.setValueAtTime(0.5, t + 0.08);            // tahan sebentar
            gain.gain.linearRampToValueAtTime(0, t + 0.10);     // putus tajam

            osc.start(t);
            osc.stop(t + 0.12);
            osc.onended = () => ctx.close();
        } catch(e) {
            console.warn('playBeep failed:', e);
        }
    }

    // --- Barcode Scanner Listener ---
    const searchInput = document.getElementById('searchProduk');

    // Auto focus search input so scanner is always ready
    document.addEventListener('click', function(e) {
        if(e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON') {
            searchInput.focus();
        }
    });

    searchInput.addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            let code = this.value.trim().toLowerCase();
            
            if(code !== '') {
                // Find product by exact barcode match
                let card = document.querySelector(`.produk-card[data-barcode="${code}"]`);
                if(card) {
                    playBeep(); // Bunyikan beep sukses
                    tambahItem(card);
                    this.value = ''; // clear input
                    filterProduk(); // reset view
                } else {
                    alert("Barcode tidak ditemukan di Kasir!");
                    this.value = '';
                    filterProduk();
                }
            }
        }
    });

    // Webcam scan: auto-trigger product lookup when barcode-scanner fills the input
    searchInput.addEventListener('change', function() {
        let code = this.value.trim().toLowerCase();
        if (code !== '') {
            let card = document.querySelector(`.produk-card[data-barcode="${code}"]`);
            if (card) {
                playBeep(); // Bunyikan beep sukses webcam
                tambahItem(card);
            } else {
                // Barcode not found - highlight the search so user sees the text
                this.style.borderColor = '#f87171';
                setTimeout(() => { this.style.borderColor = ''; }, 2000);
            }
            setTimeout(() => {
                this.value = '';
                filterProduk();
            }, 100);
        }
    });
</script>
@endpush
