# 🤖 BLUEPRINT & CONTEXTING DOKUMEN KHUSUS AI AGENT: MODUL KANTIN POS & INVENTARIS
**Project:** Web Sistem Kasir POS & Manajemen Inventaris Kantin KPRI Imigrasi Kelas 1 Non-TPI Bogor  
**Target Domain/Subdomain:** `https://kantin.kpriimigrasibogor.my.id` (Subdomain VPS aaPanel)  
**Framework / Tech Stack:** Laravel 13 | PHP 8.4 | MySQL 8.0 | Vanilla CSS/JS + Alpine.js | Maatwebsite Excel  
**Status Dokumen:** Blueprint Spesifikasi Emas (Gold Standard Blueprint untuk Pembuatan Project Baru)

---

## 📌 INSTRUKSI UTAMA UNTUK AI AGENT BARU (BACA SEBELUM CODING!)
Jika Anda (Gemini / Claude / Antigravity Agent) diberikan dokumen ini di tab/sesi konversasi baru, itu artinya **Anda ditugaskan untuk membangun dari awal (atau melanjutkan) Aplikasi Web Khusus Modul Kantin & POS Kasir KPRI Imigrasi Bogor**. 

Project ini sengaja **DIPISAHKAN** dari sistem utama tracking paspor (`kpriimigrasibogor.my.id`) atas arahan dosen pembimbing demi menerapkan prinsip arsitektur *Separation of Concerns* (pemisahan modul mandiri). 

**PANDUAN EKSEKUTIF YANG WAJIB ANDA PATUHI 100%:**
1. **Bangun Web Kantin yang Berkelas & Premium (Jangan Buat MVP Sederhana!):** UI/UX harus terlihat profesional, modern, bergaya glassmorphism/clean SaaS, memiliki micro-animation yang halus, warna yang harmonis, dan responsif.
2. **Jangan Kurangi Fitur Kunci:** Seluruh fitur yang tertera di dokumen ini (seperti **Algoritma Stok FIFO**, **Suara Beep Scanner Kasir Supermarket**, **Cetak Struk Thermal 80mm**, dan **Export Excel Multi-Sheet dengan rumus live pembagian laba 70:30**) adalah **HARAM HUKUMNYA UNTUK DILEWATKAN ATAU DISEDERHANAKAN**. Semua harus ada persis sesuai blueprint ini.
3. **Kompatibilitas Server VPS aaPanel:** Aplikasi ini akan dijalankan di server VPS Ubuntu 22.04 menggunakan aaPanel (PHP 8.4 + MySQL 8.0). Pastikan kode Anda mengikuti standar Laravel 13 modern dan siap produksi.

---

## I. ARSITEKTUR & SPESIFIKASI TEKNIS SISTEM

| Parameter | Spesifikasi / Detail |
| :--- | :--- |
| **Nama Aplikasi** | Portal POS Kasir & Inventaris Kantin KPRI Imigrasi Bogor |
| **URL Production** | **`https://kantin.kpriimigrasibogor.my.id`** (Subdomain di VPS aaPanel `103.247.11.194`) |
| **Framework Utama** | Laravel 13 (PHP 8.4) |
| **Database** | MySQL 8.0 (Nama DB: `sql_kantin_kpri`) |
| **Frontend UI/UX** | Vanilla CSS/JS Modern + Blade Components + Chart.js + Barcode Scanner API |
| **Export Engine** | `maatwebsite/excel` (atau `PhpOffice/PhpSpreadsheet`) untuk export laporan Excel multi-sheet dengan rumus live |
| **Audit Trail & Trash** | `spatie/laravel-activitylog` + Laravel Soft Deletes |

---

## II. STRUKTUR & SKEMA DATABASE LENGKAP (7 TABEL UTAMA)

Aplikasi Kantin harus memiliki skema tabel berikut dengan relasi yang tepat dan mendukung pelacakan harga pokok penjualan (HPP) berurutan secara **FIFO (First-In First-Out)**:

### 1. Tabel `users` (Pengguna & Hak Akses)
- `id`, `name`, `email`, `password`
- `role`: Enum (`super_admin`, `kasir_kantin`)
- `deleted_at` (SoftDeletes)

### 2. Tabel `kategori_kantins` (Kategori Barang)
- `id`, `nama_kategori` (Contoh: Makanan Ringan, Minuman Dingin, Kebutuhan Pokok, ATK)
- `deskripsi` (nullable)

### 3. Tabel `produk_kantins` (Master Katalog Produk)
- `id`, `kategori_id` (Foreign key ke `kategori_kantins`)
- `kode_produk` / `barcode` (String unik, misal: `8998866200112`)
- `nama_produk` (String)
- `harga_beli` / HPP Default (Decimal/Integer — Harga modal dasar)
- `harga` (Decimal/Integer — Harga jual ke konsumen)
- `stok` (Integer — Total stok fisik saat ini)
- `deleted_at` (SoftDeletes) + ActivityLog aktif

### 4. Tabel `pembelian_kantins` (Header Kulakan / Restock Barang Masuk)
- `id`, `kasir_id` (Foreign key ke `users`)
- `total_pembelian` (Integer — Total uang modal yang dikeluarkan untuk restock)
- `created_at` (Tanggal kulakan barang masuk)

### 5. Tabel `detail_pembelians` (⚡ BATCH STOK FIFO — SANGAT KRUSIAL!)
Tabel ini mencatat setiap batch barang masuk beserta harga belinya saat itu. Kolom `sisa_qty` adalah kunci utama perhitungan FIFO:
- `id`, `pembelian_kantin_id` (Foreign key ke `pembelian_kantins`)
- `produk_kantin_id` (Foreign key ke `produk_kantins`)
- `qty` (Integer — Jumlah barang yang dibeli pada batch ini)
- `sisa_qty` (Integer — **Stok yang masih tersisa dari batch ini yang belum terjual**)
- `harga_satuan` (Integer — Harga modal/HPP per unit pada batch ini)
- `subtotal` (Integer — `qty * harga_satuan`)

### 6. Tabel `transaksi_kantins` (Header Penjualan POS Kasir)
- `id`, `kasir_id` (Foreign key ke `users`)
- `total_harga` (Integer — Total nilai belanja konsumen)
- `jumlah_bayar` (Integer — Uang yang dibayarkan konsumen)
- `kembalian` (Integer — `jumlah_bayar - total_harga`)
- `metode_bayar`: Enum (`cash`, `qris`) — Pilihan bayar tunai atau QRIS
- `deleted_at` (SoftDeletes) + ActivityLog aktif

### 7. Tabel `detail_kantins` (Item Terjual & Snapshot HPP FIFO)
- `id`, `transaksi_kantin_id` (Foreign key ke `transaksi_kantins`)
- `produk_kantin_id` (Foreign key ke `produk_kantins`)
- `qty` (Integer — Jumlah unit terjual)
- `harga_satuan` (Integer — Harga jual saat transaksi terjadi)
- `harga_modal` (Integer — **Snapshot HPP dari batch FIFO yang dipotong saat transaksi**)
- `subtotal` (Integer — `qty * harga_satuan`)

---

## III. LOGIKA BISNIS KRUSIAL YANG WAJIB DIIMPLEMENTASIKAN

### 1. ⚡ Algoritma Pemotongan Stok FIFO (First-In First-Out)
Saat kasir memproses pembayaran POS (`store` transaksi), sistem **TIDAK BOLEH** hanya mengurangi kolom `stok` di tabel `produk_kantins`. Sistem **WAJIB** mengambil antrean stok dari `detail_pembelians` yang paling lama (`orderBy('created_at', 'asc')`) di mana `sisa_qty > 0`.

**Implementasi Logika PHP yang Wajib Dipakai di `KantinController::store`:**
```php
foreach ($cartItems as $item) {
    $produk = ProdukKantin::findOrFail($item['id']);
    $sisaQtyDibutuhkan = $item['qty'];

    // Kurangi stok global master produk
    $produk->decrement('stok', $sisaQtyDibutuhkan);

    // 1. Alur FIFO: Ambil antrean kulakan terlama yang sisa_qty-nya masih > 0
    $batches = DetailPembelian::where('produk_kantin_id', $produk->id)
        ->where('sisa_qty', '>', 0)
        ->orderBy('created_at', 'asc')
        ->get();

    foreach ($batches as $batch) {
        if ($sisaQtyDibutuhkan <= 0) break;

        if ($batch->sisa_qty >= $sisaQtyDibutuhkan) {
            // Batch cukup untuk memenuhi seluruh sisa kebutuhan
            $qtyDiambil = $sisaQtyDibutuhkan;
            $batch->decrement('sisa_qty', $qtyDiambil);

            DetailKantin::create([
                'transaksi_kantin_id' => $transaksi->id,
                'produk_kantin_id'    => $produk->id,
                'qty'                 => $qtyDiambil,
                'harga_satuan'        => $item['harga_jual'],
                'harga_modal'         => $batch->harga_satuan, // HPP akurat dari kulakan terlama!
                'subtotal'            => $qtyDiambil * $item['harga_jual'],
            ]);

            $sisaQtyDibutuhkan = 0;
        } else {
            // Batch tidak cukup, kuras habis sisa di batch ini lalu lanjut ke batch berikutnya
            $qtyDiambil = $batch->sisa_qty;
            $batch->update(['sisa_qty' => 0]);

            DetailKantin::create([
                'transaksi_kantin_id' => $transaksi->id,
                'produk_kantin_id'    => $produk->id,
                'qty'                 => $qtyDiambil,
                'harga_satuan'        => $item['harga_jual'],
                'harga_modal'         => $batch->harga_satuan, // HPP dari kulakan ini
                'subtotal'            => $qtyDiambil * $item['harga_jual'],
            ]);

            $sisaQtyDibutuhkan -= $qtyDiambil;
        }
    }

    // 2. Fallback: Jika barang yang dijual adalah stok awal sebelum sistem FIFO berjalan
    if ($sisaQtyDibutuhkan > 0) {
        DetailKantin::create([
            'transaksi_kantin_id' => $transaksi->id,
            'produk_kantin_id'    => $produk->id,
            'qty'                 => $sisaQtyDibutuhkan,
            'harga_satuan'        => $item['harga_jual'],
            'harga_modal'         => $produk->harga_beli, // Fallback ke harga modal master
            'subtotal'            => $sisaQtyDibutuhkan * $item['harga_jual'],
        ]);
    }
}
```

---

### 2. 🔊 Suara Beep Scanner Kasir Supermarket (Natively via Web Audio API)
Halaman POS Kasir (`pos.blade.php`) **WAJIB** menghasilkan suara Beep tajam frekuensi tinggi (1880Hz) setiap kali barcode berhasil di-scan atau tombol produk diklik ke keranjang. Gunakan Javascript murni tanpa file MP3 eksternal agar 100% cepat dan tidak bergantung pada aset static:

```javascript
// Fungsi Beep Khas Scanner Kasir Supermarket
function playBeep() {
    try {
        const ctx  = new (window.AudioContext || window.webkitAudioContext)();
        const t    = ctx.currentTime;
        const freq = 1880; // Frekuensi tinggi khas scanner supermarket

        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.type            = 'sine';
        osc.frequency.value = freq;

        // Karakter suara: keras di awal, langsung putus tajam
        gain.gain.setValueAtTime(0, t);
        gain.gain.linearRampToValueAtTime(0.5, t + 0.005);
        gain.gain.setValueAtTime(0.5, t + 0.08);
        gain.gain.linearRampToValueAtTime(0, t + 0.10);

        osc.start(t);
        osc.stop(t + 0.12);
        osc.onended = () => ctx.close();
    } catch(e) {
        console.warn('playBeep failed:', e);
    }
}
```

---

### 3. 📷 Integrasi Barcode Scanner Fisik & Webcam QR Scanner
- **Physical Barcode Scanner:** Scanner fisik USB di kasir bekerja seperti keyboard (mengetik angka barcode lalu menekan `Enter`). Buat event listener di kotak pencarian/barcode input (`#searchProduk`) agar saat `Enter` ditekan, sistem otomatis mencocokkan barcode, memasukkan produk ke keranjang belanja, membunyikan `playBeep()`, dan mengosongkan input.
- **Webcam Scanner:** Sediakan tombol modal untuk membuka kamera device (`html5-qrcode` library) agar kasir bisa memindai barcode/QR produk langsung lewat kamera laptop/tablet.

---

### 4. 🖨️ Cetak Struk Thermal 80mm Otomatis (`window.print()`)
Saat transaksi selesai disimpan, kasir diarahkan ke rute cetak struk (`/kantin/transaksi/{id}/print`). Halaman ini harus didesain khusus dengan CSS `@media print` untuk lebar kertas thermal **80mm (sekitar 300px - 320px)** dengan font monospace bersih, memuat:
- Header Koperasi (KPRI Imigrasi Kelas 1 Non-TPI Bogor)
- Tanggal & Jam, No Struk, Nama Kasir, Metode Bayar (Cash/QRIS)
- Daftar item (Nama, Qty x Harga Satuan, Subtotal)
- Total Belanja, Bayar Tunai, Kembalian
- Footer ucapan terima kasih & auto trigger `window.print()` saat halaman dimuat.

---

### 5. 📊 Export Laporan Keuangan Excel Multi-Sheet (+ Rumus Live & Pembagian Laba 70:30)
Ini adalah fitur laporan yang paling disukai oleh manajemen koperasi. Export Excel (`LaporanPenjualanExport.php`) **WAJIB menghasilkan file `.xlsx` dengan 2 Sheet terpisah** dan menginjeksikan **Rumus Live Excel (`=...`)**, bukan angka mati!

#### Sheet 1: Rekapitulasi Global & Pembagian Laba (70% Koperasi : 30% Bu Ajat)
Sheet ini merangkum total penjualan per produk dalam periode yang dipilih dengan struktur kolom dan rumus live:
| Kolom | Header | Rumus / Logika |
| :---: | :--- | :--- |
| **A** | NO | Angka urut (`1, 2, 3...`) |
| **B** | NAMA PRODUK | Nama produk |
| **C** | TERJUAL (QTY) | Total akumulasi unit terjual (`$qty = $data['keluar']`) |
| **D** | TOTAL OMSET (KOTOR) | Nilai penjualan kotor (`$qty * $harga_jual`) |
| **E** | TOTAL MODAL (HPP) | Nilai modal FIFO akurat (`$qty_hpp * $hpp_lama + ...`) |
| **F** | **LABA KOTOR** | 👉 **Rumus Excel:** `=D{row}-E{row}` |
| **G** | **LABA KOPERASI (70%)** | 👉 **Rumus Excel:** `=F{row}*0.7` |
| **H** | **BAGIAN BU AJAT (30%)** | 👉 **Rumus Excel:** `=F{row}*0.3` |
| **Total**| *(Baris paling bawah)* | 👉 **Rumus Excel:** `=SUM(C4:C{lastRow})` (Berlaku untuk kolom C, D, E, F, G, H) |

#### Sheet 2: Detail Rincian Transaksi (Itemized Sheet)
Sheet kedua berisi daftar seluruh item transaksi satu per satu secara kronologis:
- Kolom: `No`, `Tanggal & Jam`, `No Struk`, `Kasir`, `Nama Produk`, `Qty`, `Harga Jual`, `Harga Modal (FIFO)`, `Subtotal Omset`, `Margin Laba Item (`=Subtotal - (Qty*Modal)`)`.

---

### 6. 🛡️ Role-Based Access Control (RBAC), Audit Log & Soft Deletes (Trash)
- **Roles:** `super_admin` berhak melakukan segalanya termasuk melihat laporan laba 70:30, menambah/edit produk, dan mengakses menu Trash/Audit Log. `kasir_kantin` hanya berhak membuka halaman POS Kasir, melihat produk, dan melakukan transaksi harian.
- **Audit Log (`spatie/laravel-activitylog`):** Setiap perubahan harga jual/modal pada produk atau pembatalan transaksi harus tercatat otomatis beserta nilai lama (`old`) dan nilai baru (`attributes`) serta siapa kasir yang mengutak-atiknya.
- **Trash & Restore:** Semua produk, kategori, atau transaksi yang dihapus tidak boleh hilang permanen dari database (`SoftDeletes`). Sediakan halaman Tempat Sampah (`TrashController`) bagi `super_admin` untuk memulihkan (`restore`) atau menghapus permanen (`forceDelete`).

---

## IV. UI / UX DESIGN SYSTEM (STUDIO QUALITY DESIGN)

Untuk memberikan kesan **WOW** sejak tatapan pertama, patuhi panduan estetika berikut:
1. **Palet Warna Harmonik:** Gunakan paduan warna profesional (misal: Deep Emerald Green / Slate Blue berpadu dengan clean white dan subtle gray).
2. **Typography Modern:** Gunakan Google Fonts modern seperti **Inter**, **Outfit**, atau **Plus Jakarta Sans**.
3. **Card & Glassmorphism:** Tampilkan kartu statistik dashboard (Omset Hari Ini, Laba Bersih, Transaksi Sukses, Produk Menipis) dengan bayangan halus (*soft shadow*) dan sentuhan transparan modern.
4. **Notifikasi Peringatan Stok Menipis (`stok <= 5`):** Di dashboard utama dan POS kasir, berikan badge peringatan warna merah/oranye menyala jika ada barang yang stoknya tersisa ≤ 5 unit.
5. **Responsif Sempurna:** Tampilan POS Kasir harus nyaman digunakan di layar PC desktop monitor lebar maupun tablet kasir layar sentuh.

---

## V. PANDUAN DEPLOYMENT KE SUBDOMAIN VPS AAPANEL

Jika aplikasi sudah selesai dikembangkan di lokal (`/Users/dakim/Herd/portal-kantin`), ikuti langkah deployment gratis ke VPS aaPanel berikut:

1. **Buat Subdomain di Panel DNS Rumahweb:**
   Tambah A Record baru dengan Host `kantin` diarahkan ke IP VPS `103.247.11.194`.
2. **Buat Website Baru di aaPanel (`http://103.247.11.194:888`):**
   - Menu **Website** -> **Add site** -> Domain: `kantin.kpriimigrasibogor.my.id`.
   - Centang **Create Database (MySQL)** -> Nama DB: `sql_kantin_kpri`.
   - Pilih PHP Version: **PHP-84**.
3. **Setup Vhost Nginx Rewrite Rules (Di aaPanel):**
   Pada menu konfigurasi website -> **URL rewrite**, masukkan rule standar Laravel:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```
4. **Hubungkan GitHub & Deploy:**
   - Push project ke repository private GitHub.
   - Di terminal VPS over SSH (`ssh root@103.247.11.194 -p 22`), masuk ke `/www/wwwroot/kantin.kpriimigrasibogor.my.id`, lakukan `git clone / pull`.
   - Jalankan perintah persiapan produksi:
     ```bash
     composer install --optimize-autoloader --no-dev
     php artisan migrate --force --seed
     php artisan config:cache
     php artisan route:cache
     php artisan view:cache
     chown -R www:www storage bootstrap/cache
     chmod -R 775 storage bootstrap/cache
     ```
5. **Aktifkan SSL Let's Encrypt:**
   Di menu SSL aaPanel, klik **Let's Encrypt** -> centang `kantin.kpriimigrasibogor.my.id` -> klik **Apply**. Web POS Kantin siap digunakan 100%!

---
*Dokumentasi Blueprint Emas ini dibuat oleh AI Assistant Antigravity (Google DeepMind) untuk menduplikasi dan memisahkan Modul Kantin KPRI Imigrasi Bogor secara sempurna tanpa kehilangan satu fitur pun.*
