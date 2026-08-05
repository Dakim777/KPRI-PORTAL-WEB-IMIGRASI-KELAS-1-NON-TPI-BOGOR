# 🤖 CONTEXTING PROMPT UNTUK CLAUDE AI — PEMBUATAN DOKUMEN UAT
## Sistem Portal KPRI Kantor Imigrasi Kelas I Non-TPI Bogor

Salin seluruh teks di bawah ini, lalu paste ke sesi chat baru di Claude AI. Claude akan langsung membuatkan dokumen UAT lengkap dalam format HTML tabel siap copy-paste ke Microsoft Word.

---

## ⬇️ MULAI COPY DARI SINI ⬇️

---

Kamu adalah seorang ahli software testing. Saya butuh kamu membuatkan **dokumen User Acceptance Testing (UAT)** lengkap untuk Laporan Praktik Kerja Lapangan (PKL) saya. Outputnya harus berupa **file HTML** dengan tabel-tabel UAT yang sudah rapi, bergaris hitam, font Times New Roman 9.5pt, max-width 530px, dan **langsung siap di-copy-paste ke Microsoft Word** tanpa perlu formatting ulang.

---

## INFORMASI PROYEK

**Judul Proyek:**
"Rancang Bangun Sistem Informasi Pelacakan Pengiriman Paspor Berbasis Web Menggunakan Live Tracking API dan WhatsApp Gateway pada Koperasi Kantor Imigrasi Kelas I Non-TPI Bogor"

**URL Produksi:** `https://kpriimigrasibogor.my.id`

**Tech Stack:** Laravel 13, MySQL 8.0, PHP 8.4, Nginx, Node.js (WhatsApp Baileys Gateway), BinderByte API (Pelacakan Resi Ekspedisi)

---

## LATAR BELAKANG (DARI TOR)

Koperasi Karyawan Kantor Imigrasi Kelas I Non-TPI Bogor menangani pendistribusian paspor kepada pemohon yang berkasnya sudah selesai diproses di kantor imigrasi. Selama ini pencatatan pengiriman masih dikerjakan dengan cara konvensional, yaitu kasir menuliskan data pemohon dan nomor resi pada buku atau spreadsheet yang tersimpan di komputer kasir, sehingga hanya bisa dilihat oleh orang yang memegang berkas tersebut.

Cara kerja seperti ini menimbulkan sejumlah kendala di lapangan. Catatan transaksi tersebar di berbagai tempat dan rawan hilang, sementara nomor telepon dan alamat pemohon dituliskan apa adanya tanpa perlindungan, sehingga berisiko apabila berkas tersebut jatuh ke tangan yang salah. Pemohon sendiri baru mengetahui posisi paspornya ketika menelepon atau datang langsung ke kantor, karena memang belum ada cara lain untuk mengeceknya. Bendahara koperasi juga kesulitan menyusun laporan keuangan sebab datanya tidak tersimpan dalam bentuk yang siap diolah, sementara konfirmasi pengiriman masih mengandalkan nomor WhatsApp pribadi kasir yang riwayatnya sulit ditelusuri kembali.

---

## TUJUAN PEKERJAAN (DARI TOR — 8 BUTIR)

1. Mengganti pencatatan manual dengan sistem yang tersentralisasi, sehingga data transaksi koperasi tidak lagi tercecer di berbagai tempat.
2. Menyediakan halaman pelacakan yang bisa langsung dibuka oleh masyarakat pemohon tanpa perlu login atau membuat akun.
3. Menghubungkan sistem dengan API BinderByte agar posisi paket dari berbagai ekspedisi bisa dipantau dari satu tempat saja.
4. Mengirim kabar status pengiriman ke pemohon lewat WhatsApp secara otomatis, tanpa kasir perlu mengetik pesan satu per satu.
5. Menyimpan data pribadi pemohon dalam bentuk terenkripsi di basis data, bukan sekadar teks biasa yang mudah dibaca siapa saja.
6. Memudahkan bendahara koperasi mengunduh rekap transaksi dalam format Excel kapan pun dibutuhkan.
7. Mencatat jejak aktivitas pengguna di dalam sistem sebagai bahan penelusuran apabila suatu saat diperlukan.
8. Memberi mahasiswa pengalaman menjalani proses pengembangan perangkat lunak dari awal hingga sistem benar-benar dipakai di lingkungan produksi.

---

## DAFTAR LENGKAP FITUR YANG HARUS DIUJI (BERDASARKAN KODE SUMBER AKTUAL)

### A. MODUL PUBLIK (Tanpa Login — Untuk Masyarakat Pemohon Paspor)
1. **Halaman Utama Portal Publik** — Membuka `https://kpriimigrasibogor.my.id` menampilkan form pelacakan paspor profesional dengan logo Ditjen Imigrasi & ITERA.
2. **Pelacakan Paspor via ID Transaksi** — Input `KBG-YYMMDD-XXX` (contoh: `KBG-260722-001`) → sistem menampilkan status internal + timeline resi live.
3. **Pelacakan Paspor via Nomor Resi** — Input nomor resi POS/JNE/dll → sistem mencari di database lalu menampilkan timeline live dari BinderByte API.
4. **Smart Normalizer Input** — Input `kbg260722001` (tanpa tanda hubung, huruf kecil) tetap ditemukan oleh sistem.
5. **Masking Nama Penerima (PII Protection)** — Nama pemohon ditampilkan dalam bentuk masked (contoh: `Bud*** ***oso`) demi perlindungan privasi.
6. **Graceful Fallback 404** — Jika ID/resi tidak ditemukan, sistem menampilkan pesan ramah "Data tidak ditemukan" tanpa error 500.
7. **Multi-Courier Auto-Discovery** — Jika resi tidak terdeteksi di kurir utama, sistem otomatis memindai ke 8 kurir lainnya.

### B. MODUL AUTENTIKASI
8. **Login Kasir/Admin** — Login melalui `/login` dengan email dan password.
9. **Redirect Role-Based** — Setelah login, `super_admin` diarahkan ke `/admin/dashboard`, `kasir_paspor` diarahkan ke `/paspor/dashboard`.
10. **Logout** — Tombol logout membersihkan sesi dan mengarahkan ke halaman login.
11. **Multi-Device Session** — Kasir bisa login di 2 perangkat berbeda secara bersamaan tanpa saling logout.

### C. MODUL DASHBOARD
12. **Dashboard Super Admin** — Menampilkan statistik total transaksi, transaksi hari ini, transaksi bulan ini, omset bulan ini, dan 10 transaksi terbaru.
13. **Dashboard Kasir Paspor** — Menampilkan statistik harian & bulanan, chart grafik 7 hari terakhir, dan rekap breakdown status pengiriman.

### D. MODUL TRANSAKSI PASPOR (CRUD)
14. **Tambah Transaksi Paspor Baru** — Kasir mengisi form (`nama_penerima`, `jumlah_paspor`, `nama_paspor_list`, `alamat_tujuan_pengiriman`, `tanggal_transaksi`, `no_hp`, `jenis_kurir`, `layanan_kurir`, `metode_pembayaran`, `biaya_kurir`, `handling_fee`). Sistem otomatis meng-generate ID Transaksi `KBG-YYMMDD-XXX` dan No Struk.
15. **Lihat Detail Transaksi** — Halaman detail menampilkan semua informasi transaksi termasuk data PII yang di-decrypt otomatis.
16. **Edit Transaksi Paspor** — Mengubah data transaksi yang sudah ada (nama, alamat, biaya, resi, status, dll).
17. **Hapus Transaksi (Soft Delete)** — Hanya `super_admin` yang dapat menghapus. Data masuk ke Trash (tidak dihapus permanen).
18. **Pencarian & Filter** — Tabel riwayat paspor mendukung pencarian (search), filter status (transaksi/diproses/selesai/batal), filter tanggal, dan filter bulan/tahun.
19. **Pagination** — Tabel menampilkan 20 data per halaman dengan navigasi halaman yang berfungsi.

### E. MODUL STATUS PENGIRIMAN
20. **Update Status ke "Diproses"** — Status berubah ke `diproses`, tanggal pengiriman otomatis terisi, biaya modal packaging default Rp 1.000 auto-set.
21. **Update Status ke "Selesai"** — Status berubah ke `selesai`, tanggal paspor diterima otomatis terisi. Status ini hanya diubah manual oleh kasir (tidak auto-sync).
22. **Update Status ke "Batal"** — Status berubah ke `batal`.
23. **Notifikasi WA Otomatis Saat Status "Diproses"/"Selesai"** — Jika ada nomor resi dan nomor HP, sistem otomatis kirim notifikasi WhatsApp ke pemohon saat status diubah.

### F. MODUL BARCODE SCANNER WEBCAM
24. **Scan Barcode Resi via Webcam** — Modal edit resi memiliki fitur pemindai kamera (`Html5QrcodeScanner`). Kasir memindai barcode/QR resi → resi otomatis terisi di form → bunyi beep sukses.

### G. MODUL CEK ONGKIR & LACAK RESI (KASIR)
25. **Cek Ongkir API** — Kasir dapat mengecek estimasi ongkir ke kota tujuan dari 9 ekspedisi via API BinderByte.
26. **Lacak Resi Internal** — Kasir dapat melacak posisi paket dari halaman detail transaksi via tombol "Lacak Resi" yang memanggil API BinderByte.

### H. MODUL CETAK STRUK & EXPORT EXCEL
27. **Cetak Struk/Kwitansi** — Kasir mencetak kwitansi bertampilan formal dengan ID Transaksi `KBG-...`, barcode, URL pelacakan, dan rincian biaya.
28. **Export Laporan Excel (Kompleks)** — Hanya `super_admin`. Mengunduh rekap transaksi lengkap dalam format `.xlsx` dengan filter tanggal.

### I. MODUL WHATSAPP GATEWAY
29. **Halaman Manajemen WA Gateway** — Kasir/Admin dapat melihat status koneksi WhatsApp bot, memindai QR Code, dan mengirim pesan tes.
30. **Kirim Pesan WA Manual** — Tombol "Kirim WA" pada halaman detail transaksi untuk mengirim ulang notifikasi resi ke pemohon.

### J. MODUL MANAJEMEN PENGGUNA (SUPER ADMIN ONLY)
31. **Lihat Daftar Pengguna** — Tabel seluruh akun kasir & admin dengan pencarian dan filter role.
32. **Tambah Akun Pengguna Baru** — Membuat akun `kasir_paspor` atau `super_admin` baru (validasi email unik, password min 8 karakter).
33. **Edit Akun Pengguna** — Mengubah nama, email, role pengguna.
34. **Hapus Akun Pengguna** — Menghapus akun kasir/admin.
35. **Reset Password Pengguna** — Super Admin dapat mereset password pengguna lain.

### K. MODUL AUDIT LOG & TRASH (SUPER ADMIN ONLY)
36. **Audit Log (Activity Log)** — Menampilkan riwayat seluruh aktivitas operasional (siapa mengubah apa, kapan, nilai sebelum & sesudah perubahan).
37. **Trash (Soft Delete Recovery)** — Melihat daftar transaksi yang telah dihapus.
38. **Restore Transaksi dari Trash** — Mengembalikan transaksi yang dihapus ke data aktif.
39. **Force Delete Transaksi** — Menghapus transaksi secara permanen dari database.

### L. MODUL PROFIL PENGGUNA
40. **Edit Profil** — Kasir/Admin dapat mengubah nama dan email profil masing-masing.

### M. KEAMANAN DATA (NON-FUNGSIONAL / VERIFIKASI)
41. **Enkripsi PII AES-256** — Data `nama_penerima`, `no_hp`, `alamat_tujuan_pengiriman`, `nama_paspor_list` tersimpan terenkripsi di database (verifikasi via query raw MySQL).
42. **RBAC (Role-Based Access Control)** — Kasir tidak dapat mengakses rute `/admin/*`. Super Admin dapat mengakses semua rute.
43. **Global Loading Buffer** — Setiap aksi form menampilkan loading spinner dan menonaktifkan tombol submit untuk mencegah double-click.

---

## FORMAT TABEL UAT YANG WAJIB DIIKUTI

Setiap tabel UAT harus memiliki **7 kolom** berikut (persis seperti contoh referensi dari kampus):

| **ID** | **Feature** | **Input Data** | **Test Steps** | **Expected Results** | **Actual Results** | **Status** |
| :---: | :--- | :--- | :--- | :--- | :--- | :---: |

**Aturan pengisian:**
- **ID:** Format `UAT-001`, `UAT-002`, dst. (urut sesuai nomor fitur di atas).
- **Feature:** Nama fitur yang diuji (ringkas, 3-5 kata).
- **Input Data:** Daftar data input yang diperlukan (bernomor, seperti: 1. Data pemohon. 2. Nomor resi.).
- **Test Steps:** Langkah-langkah pengujian bernomor (seperti: 1. Login sebagai kasir. 2. Pilih menu paspor. 3. Klik tambah transaksi. ...).
- **Expected Results:** Hasil yang diharapkan bernomor (seperti: 1. ID Transaksi KBG-... otomatis tergenerate. 2. Data berhasil disimpan.).
- **Actual Results:** **ISI SAMA PERSIS** dengan Expected Results (karena semua fitur sudah lolos uji di production).
- **Status:** **PASS** (untuk semua test case, karena sistem sudah live dan berfungsi).

---

## ATURAN OUTPUT HTML

1. **Buat file HTML lengkap** (`<!DOCTYPE html>` sampai `</html>`).
2. **Font:** Times New Roman, ukuran sel `9.5pt`, padding `2px 5px`.
3. **Lebar tabel:** `max-width: 530px` agar pas di margin A4 Word.
4. **Border:** `1px solid #000000` pada semua `th` dan `td`.
5. **Header tabel:** Background `#f2f2f2`, bold, center-aligned.
6. **Caption tabel** berada **DI ATAS TABEL** dengan format `Tabel <urutan>. Skenario User Acceptance Testing Modul <Nama Modul>` (contoh: `Tabel 1. Skenario User Acceptance Testing Modul Publik`). Penomoran tabel dimulai dari `Tabel 1.` dan seterusnya secara berurutan.
7. **Kelompokkan UAT per modul** (setiap modul = 1 tabel terpisah dengan caption sendiri):
   - Tabel 1. Modul Publik (UAT-001 s/d UAT-007)
   - Tabel 2. Modul Autentikasi (UAT-008 s/d UAT-011)
   - Tabel 3. Modul Dashboard (UAT-012, UAT-013)
   - Tabel 4. Modul Transaksi Paspor (UAT-014 s/d UAT-019)
   - Tabel 5. Modul Status Pengiriman (UAT-020 s/d UAT-023)
   - Tabel 6. Modul Barcode Scanner (UAT-024)
   - Tabel 7. Modul Cek Ongkir & Lacak Resi (UAT-025, UAT-026)
   - Tabel 8. Modul Cetak Struk & Export Excel (UAT-027, UAT-028)
   - Tabel 9. Modul WhatsApp Gateway (UAT-029, UAT-030)
   - Tabel 10. Modul Manajemen Pengguna (UAT-031 s/d UAT-035)
   - Tabel 11. Modul Audit Log & Trash (UAT-036 s/d UAT-039)
   - Tabel 12. Modul Profil Pengguna (UAT-040)
   - Tabel 13. Keamanan Data (UAT-041 s/d UAT-043)
8. **Jangan tampilkan kode CSS/HTML di output chat.** Langsung berikan file HTML yang sudah jadi.

---

Buatkan sekarang file HTML UAT lengkap **43 test case** (UAT-001 sampai UAT-043), dikelompokkan per modul, dengan semua kolom terisi penuh sesuai aturan di atas. Pastikan setiap Test Steps dan Expected Results ditulis secara detail dan bernomor, bukan hanya satu kalimat singkat.

---

## ⬆️ SELESAI COPY SAMPAI SINI ⬆️
