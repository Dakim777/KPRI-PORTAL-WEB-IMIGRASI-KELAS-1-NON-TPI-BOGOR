@extends('layouts.app')

@section('title', 'Input Barang Masuk')
@section('page-title', 'Scanner Barang Masuk (Pembelian)')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Scanner / Input Area --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-blue-700 px-5 py-4">
                <h3 class="font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Scan Barcode Disini
                </h3>
            </div>
            <div class="p-5">
                <p class="text-xs text-gray-500 mb-3">Pastikan kursor berada di kotak ini sebelum menembak barcode.</p>
                <input type="text" id="barcodeInput" autocomplete="off" autofocus
                    placeholder="Scan atau ketik barcode..."
                    class="w-full text-center font-mono text-lg px-4 py-3 border-2 border-blue-200 rounded-xl focus:outline-none focus:border-blue-600 focus:ring-0 transition" />
                
                {{-- Webcam scan option --}}
                <div class="mt-3">
                    <p class="text-xs text-gray-400 text-center mb-2">— atau —</p>
                    <x-barcode-scanner targetId="barcodeInput" />
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-2">Atau pilih manual (jika barcode rusak):</p>
                    <select id="manualSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Barang Manual --</option>
                        @foreach($produks as $p)
                            <option value="{{ $p->id }}" data-json="{{ json_encode($p) }}">{{ $p->nama_produk }} (Stok: {{ $p->stok }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div id="alertBox" class="hidden p-4 rounded-xl text-sm border flex items-start gap-2 shadow-sm">
            <span id="alertMessage"></span>
        </div>
    </div>

    {{-- Keranjang Barang Masuk --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Keranjang Barang Masuk</h3>
                <span class="text-xs text-gray-400" id="itemCount">0 Item</span>
            </div>
            
            <div class="p-0 flex-1 overflow-y-auto bg-gray-50/30" style="min-h: 300px;">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left">Barang</th>
                            <th class="px-4 py-3 text-center w-24">Qty Masuk</th>
                            <th class="px-4 py-3 text-right w-32">Harga Beli / Pcs</th>
                            <th class="px-4 py-3 text-right w-32">Subtotal</th>
                            <th class="px-4 py-3 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody" class="divide-y divide-gray-100 bg-white">
                        <tr id="emptyCart">
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                                Belum ada barang di scan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <span class="font-medium text-gray-600">Total Nilai Pembelian:</span>
                    <span class="text-xl font-bold text-blue-700" id="totalLabel">Rp 0</span>
                </div>
                
                <form method="POST" action="{{ route('kantin.pembelian.store') }}" id="formPembelian">
                    @csrf
                    <input type="hidden" name="items" id="itemsInput" value="[]">
                    <button type="submit" id="btnSimpan" disabled
                        class="w-full bg-blue-700 hover:bg-blue-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-3 rounded-xl font-semibold transition">
                        Simpan Barang Masuk & Update Stok
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let cart = [];
    
    const barcodeInput = document.getElementById('barcodeInput');
    const manualSelect = document.getElementById('manualSelect');
    const cartBody = document.getElementById('cartBody');
    const emptyCart = document.getElementById('emptyCart');
    const itemsInput = document.getElementById('itemsInput');
    const totalLabel = document.getElementById('totalLabel');
    const btnSimpan = document.getElementById('btnSimpan');
    const itemCount = document.getElementById('itemCount');
    const alertBox = document.getElementById('alertBox');
    const alertMessage = document.getElementById('alertMessage');

    // Auto focus to barcode
    document.addEventListener('click', function(e) {
        if(e.target.tagName !== 'INPUT' && e.target.tagName !== 'SELECT' && e.target.tagName !== 'BUTTON') {
            barcodeInput.focus();
        }
    });

    barcodeInput.addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            let code = this.value.trim();
            if(code !== '') {
                fetchProduct(code);
            }
            this.value = '';
        }
    });

    // Webcam scan: when barcode-scanner component fills the input, auto-trigger search
    barcodeInput.addEventListener('change', function() {
        let code = this.value.trim();
        if (code !== '') {
            fetchProduct(code);
            this.value = '';
        }
    });

    manualSelect.addEventListener('change', function() {
        if(this.value !== '') {
            let option = this.options[this.selectedIndex];
            let product = JSON.parse(option.getAttribute('data-json'));
            addToCart(product);
            this.value = '';
            barcodeInput.focus();
        }
    });

    function fetchProduct(barcode) {
        fetch(`{{ route('kantin.pembelian.search-barcode') }}?barcode=${barcode}`)
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    hideAlert();
                    if(typeof window.playBeep === 'function') window.playBeep(); // Beep sukses
                    addToCart(res.data);
                } else {
                    showAlert('Barcode tidak ditemukan di Master Produk!', 'error');
                }
            })
            .catch(err => {
                showAlert('Terjadi kesalahan jaringan.', 'error');
            });
    }

    function showAlert(msg, type) {
        alertMessage.textContent = msg;
        alertBox.className = type === 'error' 
            ? 'p-4 rounded-xl text-sm border flex items-start gap-2 shadow-sm bg-red-50 border-red-200 text-red-800 mt-4'
            : 'p-4 rounded-xl text-sm border flex items-start gap-2 shadow-sm bg-blue-50 border-blue-200 text-blue-800 mt-4';
        alertBox.classList.remove('hidden');
        setTimeout(() => hideAlert(), 3000);
    }

    function hideAlert() {
        alertBox.classList.add('hidden');
    }

    function addToCart(product) {
        let existing = cart.find(i => i.id === product.id);
        if(existing) {
            existing.qty += 1;
        } else {
            cart.push({
                id: product.id,
                nama_produk: product.nama_produk,
                barcode: product.barcode,
                harga_beli: product.harga_beli || product.harga || 0,
                qty: 1
            });
        }
        renderCart();
    }

    function updateQty(id, qty) {
        let item = cart.find(i => i.id === id);
        if(item) {
            item.qty = parseInt(qty) || 1;
            renderCart();
        }
    }

    function updateHarga(id, harga) {
        let item = cart.find(i => i.id === id);
        if(item) {
            item.harga_beli = parseFloat(harga) || 0;
            renderCart();
        }
    }

    function removeItem(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    function formatRp(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function renderCart() {
        if(cart.length === 0) {
            cartBody.innerHTML = '';
            cartBody.appendChild(emptyCart);
            emptyCart.style.display = 'table-row';
            btnSimpan.disabled = true;
            totalLabel.textContent = 'Rp 0';
            itemCount.textContent = '0 Item';
            itemsInput.value = '[]';
            return;
        }

        emptyCart.style.display = 'none';
        cartBody.innerHTML = '';
        
        let total = 0;

        cart.forEach(item => {
            let subtotal = item.qty * item.harga_beli;
            total += subtotal;

            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800">${item.nama_produk}</p>
                    <p class="text-xs text-gray-400 font-mono">${item.barcode || '-'}</p>
                </td>
                <td class="px-4 py-3">
                    <input type="number" min="1" value="${item.qty}" 
                        onchange="updateQty(${item.id}, this.value)"
                        class="w-full text-center px-2 py-1.5 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                </td>
                <td class="px-4 py-3">
                    <input type="number" min="0" value="${item.harga_beli}" 
                        onchange="updateHarga(${item.id}, this.value)"
                        class="w-full text-right px-2 py-1.5 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                </td>
                <td class="px-4 py-3 text-right font-semibold text-gray-700">
                    Rp ${formatRp(subtotal)}
                </td>
                <td class="px-4 py-3 text-center">
                    <button type="button" onclick="removeItem(${item.id})" class="text-red-400 hover:text-red-600">
                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;
            cartBody.appendChild(tr);
        });

        totalLabel.textContent = 'Rp ' + formatRp(total);
        itemCount.textContent = cart.length + ' Item';
        itemsInput.value = JSON.stringify(cart);
        btnSimpan.disabled = false;
    }
</script>
@endsection
