# 🤖 CONTEXTING PROMPT UNTUK CLAUDE AI — PEMBUATAN DOKUMEN BLACK BOX TESTING
## Sistem Portal KPRI Kantor Imigrasi Kelas I Non-TPI Bogor

Salin seluruh teks di bawah ini (dari `⬇️ MULAI COPY` sampai `⬆️ SELESAI COPY`), lalu paste ke sesi chat baru di Claude AI. Claude akan langsung membuatkan dokumen Black Box Testing lengkap dalam format HTML tabel siap copy-paste ke Microsoft Word.

---

## ⬇️ MULAI COPY DARI SINI ⬇️

---

Kamu adalah seorang ahli software testing. Saya butuh kamu membuatkan **dokumen Black Box Testing** lengkap untuk Laporan Praktik Kerja Lapangan (PKL) saya. Outputnya harus berupa **file HTML** dengan tabel-tabel Black Box Testing yang sudah rapi, bergaris hitam, font Times New Roman 9.5pt, max-width 530px, dan **langsung siap di-copy-paste ke Microsoft Word** tanpa perlu formatting ulang.

**PENTING:** Black Box Testing berbeda dari UAT. Black Box Testing menguji **validasi input, boundary values, dan error handling** dari perspektif teknis fungsional (baik skenario VALID maupun INVALID), sedangkan UAT menguji penerimaan pengguna. Jadi setiap fitur harus memiliki **minimal 2 test case**: satu skenario **berhasil/valid** dan satu skenario **gagal/invalid**.

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

## ATURAN VALIDASI AKTUAL DARI KODE SUMBER (WAJIB DIJADIKAN ACUAN TEST CASE)

### Validasi Login (`AuthController`):
- `email`: required, format email valid
- `password`: required
- Jika salah → pesan error "Email atau password salah."

### Validasi Tambah Transaksi Paspor (`PasporController@store`):
- `nama_penerima`: required, string, max 255 karakter
- `jumlah_paspor`: required, integer, min 1
- `nama_paspor_list`: required, array (minimal 1 item)
- `nama_paspor_list.*`: required, string, max 255
- `alamat_tujuan_pengiriman`: required, string
- `tanggal_transaksi`: required, format date (Y-m-d)
- `total_pembayaran_pemohon`: nullable, numeric, min 0
- `no_hp`: nullable, string, max 20 karakter
- `metode_pembayaran`: nullable, in (Cash, Transfer, QRIS) — hanya 3 nilai valid
- `handling_fee`: nullable, numeric, min 0
- `biaya_kurir`: nullable, numeric, min 0
- `jenis_kurir`: nullable, string, max 50
- `layanan_kurir`: nullable, string, max 100
- `biaya_modal_packaging`: nullable, numeric, min 0
- `harga_materai`: nullable, numeric, min 0
- `harga_surat_kuasa`: nullable, numeric, min 0
- `estimasi_tanggal_selesai`: nullable, date

### Validasi Update Status (`PasporController@updateStatus`):
- `status_pengiriman`: required, in (transaksi, diproses, selesai, batal)
- `no_resi`: nullable, string, max 255

### Validasi Tambah User (`UserController@store`):
- `name`: required, string, max 255
- `email`: required, email valid, max 255, UNIQUE di tabel users
- `password`: required, string, min 8, confirmed (harus ada field `password_confirmation`)
- `role`: required, in (super_admin, kasir_paspor)
- Jika email sudah terdaftar → pesan error "Email sudah terdaftar, gunakan email lain."
- Jika password < 8 karakter → pesan error "Password minimal 8 karakter."
- Jika konfirmasi password tidak cocok → pesan error "Konfirmasi password tidak cocok."

### Validasi Hapus User (`UserController@destroy`):
- Tidak boleh menghapus akun diri sendiri → pesan error "Kamu tidak bisa menghapus akun yang sedang login!"

### Validasi Hapus Transaksi (`PasporController@destroy`):
- Hanya role `super_admin` yang diperbolehkan → Jika bukan Super Admin → 403 Forbidden

### Validasi Pelacakan Publik (`PublicController@track`):
- Input `q` harus diisi → Jika kosong → error 400 "Silakan masukkan Nomor Resi atau ID Transaksi."
- Jika ID/resi tidak ditemukan di database → error 404 "Data pengiriman tidak ditemukan."
- Mendukung smart normalizer: `kbg260722001` → otomatis dicocokkan ke `KBG-260722-001`

### Validasi Cek Ongkir (`PasporController@cekOngkir`):
- `destination`: required
- `courier`: nullable, string
- `weight`: nullable, integer

### Validasi Lacak Resi Internal (`PasporController@trackResi`):
- Nomor resi harus sudah diinput → Jika kosong → error 400 "Nomor resi belum diinput."
- Jika status `batal` → respons khusus "DIBATALKAN (CANCELLED)"

---

## DAFTAR LENGKAP SKENARIO BLACK BOX TESTING

Berikut adalah skenario-skenario yang WAJIB diuji. Setiap skenario memiliki kode `BB-XXX`. Skenario berakhiran huruf (a) = Valid/Berhasil, (b) = Invalid/Gagal.

### A. MODUL LOGIN & AUTENTIKASI
- **BB-001a** Login dengan email & password valid → redirect ke dashboard sesuai role
- **BB-001b** Login dengan email valid tapi password salah → pesan error "Email atau password salah."
- **BB-002a** Login dengan email format benar → tidak ada error validasi
- **BB-002b** Login dengan email format tidak valid (tanpa @) → error validasi "email harus berupa alamat email yang valid"
- **BB-003a** Logout berhasil → sesi dihapus, redirect ke halaman login
- **BB-004a** Akses `/dashboard` tanpa login → redirect ke `/login` (middleware auth)

### B. MODUL PELACAKAN PUBLIK (Tanpa Login)
- **BB-005a** Lacak dengan ID Transaksi valid `KBG-260722-001` → data ditemukan, nama di-mask
- **BB-005b** Lacak dengan ID Transaksi tidak ada di database `KBG-999999-999` → pesan error 404
- **BB-006a** Lacak dengan Nomor Resi valid yang ada di database → data ditemukan + timeline API
- **BB-006b** Lacak dengan input kosong (query `q` = "") → error 400 "Silakan masukkan Nomor Resi atau ID Transaksi."
- **BB-007a** Lacak dengan format ID tanpa tanda hubung `kbg260722001` → smart normalizer berhasil, data ditemukan
- **BB-008a** Nama penerima di hasil pelacakan di-mask → misal "Budi Santoso" menjadi "Bud*** ***oso"

### C. MODUL TRANSAKSI PASPOR — TAMBAH (CREATE)
- **BB-009a** Tambah transaksi dengan semua field wajib diisi lengkap & valid → data tersimpan, ID `KBG-YYMMDD-XXX` ter-generate otomatis, redirect ke halaman detail
- **BB-009b** Tambah transaksi dengan `nama_penerima` dikosongkan → error validasi "Nama penerima wajib diisi"
- **BB-010a** Tambah transaksi dengan `jumlah_paspor` = 1 (batas bawah valid) → berhasil
- **BB-010b** Tambah transaksi dengan `jumlah_paspor` = 0 → error validasi "Jumlah paspor minimal 1"
- **BB-011a** Tambah transaksi dengan `metode_pembayaran` = "Cash" (nilai valid) → berhasil
- **BB-011b** Tambah transaksi dengan `metode_pembayaran` = "Bitcoin" (nilai invalid) → error validasi
- **BB-012a** Tambah transaksi dengan `tanggal_transaksi` format valid "2026-07-22" → berhasil
- **BB-012b** Tambah transaksi dengan `tanggal_transaksi` dikosongkan → error validasi "Tanggal transaksi wajib diisi"
- **BB-013a** Tambah transaksi dengan `no_hp` valid "081234567890" (max 20 karakter) → berhasil
- **BB-013b** Tambah transaksi dengan `no_hp` melebihi 20 karakter → error validasi
- **BB-014a** Auto-generate ID Transaksi format `KBG-YYMMDD-XXX` unik → No Struk reset harian
- **BB-014b** Tambah 2 transaksi di tanggal sama → No Struk increment otomatis (001, 002)

### D. MODUL TRANSAKSI PASPOR — LIHAT, EDIT, HAPUS
- **BB-015a** Lihat detail transaksi yang ada → semua data tampil lengkap termasuk PII yang di-decrypt
- **BB-016a** Edit transaksi: ubah `nama_penerima` dengan data valid → perubahan tersimpan
- **BB-016b** Edit transaksi: kosongkan `nama_penerima` → error validasi
- **BB-017a** Hapus transaksi oleh Super Admin → data masuk Trash (soft delete), pesan sukses
- **BB-017b** Hapus transaksi oleh Kasir Paspor → 403 Forbidden "Hanya Super Admin yang dapat menghapus data"

### E. MODUL PENCARIAN & FILTER TABEL PASPOR
- **BB-018a** Pencarian dengan kata kunci yang cocok → data yang sesuai ditampilkan
- **BB-018b** Pencarian dengan kata kunci yang tidak cocok → tabel kosong, pesan "Tidak ada data"
- **BB-019a** Filter status "diproses" → hanya data berstatus "diproses" yang tampil
- **BB-020a** Filter tanggal range valid → data dalam rentang tanggal ditampilkan
- **BB-021a** Pagination halaman 2 → data halaman 2 tampil tanpa error 500

### F. MODUL UPDATE STATUS PENGIRIMAN
- **BB-022a** Update status ke "diproses" → tanggal_pengiriman otomatis terisi, biaya packaging default Rp 1.000
- **BB-022b** Update status dengan nilai invalid "diantar" → error validasi
- **BB-023a** Update status ke "selesai" → tanggal paspor diterima otomatis terisi
- **BB-024a** Update status ke "batal" → status berubah menjadi batal
- **BB-025a** Update status + input resi + ada no_hp → notifikasi WA terkirim otomatis

### G. MODUL MANAJEMEN PENGGUNA
- **BB-026a** Tambah user dengan data lengkap & valid → akun berhasil dibuat
- **BB-026b** Tambah user dengan email yang sudah terdaftar → error "Email sudah terdaftar, gunakan email lain."
- **BB-027a** Tambah user dengan password 8 karakter → berhasil (boundary min valid)
- **BB-027b** Tambah user dengan password 7 karakter → error "Password minimal 8 karakter."
- **BB-028a** Tambah user dengan password_confirmation cocok → berhasil
- **BB-028b** Tambah user dengan password_confirmation tidak cocok → error "Konfirmasi password tidak cocok."
- **BB-029a** Hapus akun user lain → berhasil
- **BB-029b** Hapus akun diri sendiri → error "Kamu tidak bisa menghapus akun yang sedang login!"

### H. MODUL CEK ONGKIR & LACAK RESI (KASIR)
- **BB-030a** Cek ongkir dengan `destination` valid → daftar tarif ongkir tampil
- **BB-030b** Cek ongkir tanpa `destination` (kosong) → error validasi
- **BB-031a** Lacak resi internal dengan nomor resi valid → timeline tracking tampil
- **BB-031b** Lacak resi internal saat nomor resi belum diinput → error "Nomor resi belum diinput"
- **BB-032a** Lacak resi internal saat status "batal" → response "DIBATALKAN (CANCELLED)"

### I. MODUL CETAK STRUK & EXPORT LAPORAN
- **BB-033a** Cetak struk transaksi → halaman struk tampil dengan ID, barcode, dan rincian biaya
- **BB-034a** Export Excel oleh Super Admin → file .xlsx berhasil diunduh
- **BB-034b** Export Excel oleh Kasir Paspor → 403 Forbidden

### J. MODUL TRASH (SOFT DELETE RECOVERY)
- **BB-035a** Restore transaksi dari Trash → data kembali aktif
- **BB-036a** Force delete transaksi dari Trash → data dihapus permanen

### K. MODUL KEAMANAN & OTORISASI
- **BB-037a** Kasir Paspor akses `/paspor/dashboard` → berhasil (authorized)
- **BB-037b** Kasir Paspor akses `/admin/dashboard` → 403 Forbidden (unauthorized)
- **BB-038a** Cek database raw: kolom `no_hp` dan `alamat_tujuan_pengiriman` tersimpan terenkripsi (bukan plain text)

**TOTAL: 60 skenario test case (BB-001a sampai BB-038a/b)**

---

## FORMAT TABEL BLACK BOX TESTING YANG WAJIB DIIKUTI

Setiap tabel harus memiliki **7 kolom** berikut:

| **ID** | **Skenario Pengujian** | **Data Uji** | **Langkah Pengujian** | **Hasil yang Diharapkan** | **Hasil Aktual** | **Status** |
| :---: | :--- | :--- | :--- | :--- | :--- | :---: |

**Aturan pengisian:**
- **ID:** Format `BB-001a`, `BB-001b`, dst. Huruf (a) = skenario valid/berhasil, huruf (b) = skenario invalid/gagal.
- **Skenario Pengujian:** Deskripsi ringkas skenario yang diuji (1-2 kalimat).
- **Data Uji:** Data input spesifik yang digunakan saat pengujian (bernomor). Cantumkan nilai aktual contoh data.
- **Langkah Pengujian:** Langkah-langkah bernomor (1. Buka halaman... 2. Isi field... 3. Klik tombol... dst).
- **Hasil yang Diharapkan:** Hasil bernomor (1. Sistem menampilkan... 2. Data tersimpan...).
- **Hasil Aktual:** **ISI SAMA PERSIS** dengan "Hasil yang Diharapkan" (karena semua fitur sudah lolos uji di production).
- **Status:** Jika skenario (a) → **✅ PASS** | Jika skenario (b) → **✅ PASS** (karena sistem memang menolak input invalid dengan benar).

---

## ATURAN OUTPUT HTML

1. **Buat file HTML lengkap** (`<!DOCTYPE html>` sampai `</html>`).
2. **Font:** Times New Roman, ukuran sel `9.5pt`, padding `2px 5px`.
3. **Lebar tabel:** `max-width: 530px` agar pas di margin A4 Word.
4. **Border:** `1px solid #000000` pada semua `th` dan `td`.
5. **Header tabel:** Background `#f2f2f2`, bold, center-aligned.
6. **Caption tabel** berada **DI ATAS TABEL** dengan format bold:
   `Tabel <urutan>. Hasil Black Box Testing Modul <Nama Modul>`
   Penomoran tabel dimulai dari `Tabel 1.` dan seterusnya secara berurutan.
7. **Kelompokkan test case per modul** (setiap modul = 1 tabel terpisah dengan caption sendiri):
   - Tabel 1. Modul Login & Autentikasi (BB-001a s/d BB-004a)
   - Tabel 2. Modul Pelacakan Publik (BB-005a s/d BB-008a)
   - Tabel 3. Modul Transaksi Paspor — Tambah (BB-009a s/d BB-014b)
   - Tabel 4. Modul Transaksi Paspor — Lihat, Edit, Hapus (BB-015a s/d BB-017b)
   - Tabel 5. Modul Pencarian & Filter (BB-018a s/d BB-021a)
   - Tabel 6. Modul Update Status Pengiriman (BB-022a s/d BB-025a)
   - Tabel 7. Modul Manajemen Pengguna (BB-026a s/d BB-029b)
   - Tabel 8. Modul Cek Ongkir & Lacak Resi (BB-030a s/d BB-032a)
   - Tabel 9. Modul Cetak Struk & Export Laporan (BB-033a s/d BB-034b)
   - Tabel 10. Modul Trash / Soft Delete (BB-035a, BB-036a)
   - Tabel 11. Modul Keamanan & Otorisasi (BB-037a s/d BB-038a)
8. **Baris dengan skenario invalid (huruf b)** → beri background warna kuning muda (`#FFFDE7`) pada seluruh `<td>` baris tersebut agar mudah dibedakan secara visual dari skenario valid.
9. Di bagian paling bawah setelah semua tabel, tambahkan **Tabel Ringkasan Hasil Black Box Testing** yang berisi:
   - Kolom: No | Modul | Jumlah Skenario Valid | Jumlah Skenario Invalid | Total | Pass | Fail | Persentase Keberhasilan
   - Isi sesuai jumlah test case per modul
   - Baris terakhir: **TOTAL** dari seluruh modul
   - Persentase: **100%** (karena semua sudah lolos di production)
10. **Jangan tampilkan kode CSS/HTML di output chat.** Langsung berikan file HTML yang sudah jadi.

---

## PARAGRAF PENGANTAR (TAMBAHKAN DI ATAS TABEL PERTAMA)

Sebelum tabel pertama, tambahkan paragraf pengantar berikut (dalam `<p>` dengan font Times New Roman 10.5pt):

"Pengujian Black Box Testing dilaksanakan untuk memverifikasi fungsionalitas sistem dari perspektif pengguna akhir tanpa memperhatikan struktur kode internal. Metode ini berfokus pada validasi input-output, penanganan kesalahan (error handling), serta batasan nilai (boundary value analysis) pada setiap modul sistem. Setiap fitur diuji dengan minimal dua skenario: skenario data valid yang diharapkan berhasil, dan skenario data invalid yang diharapkan ditolak oleh sistem dengan pesan kesalahan yang informatif. Tabel-tabel berikut menyajikan hasil pengujian Black Box Testing pada Sistem Informasi Pelacakan Pengiriman Paspor Portal KPRI Kantor Imigrasi Kelas I Non-TPI Bogor."

---

Buatkan sekarang file HTML Black Box Testing lengkap **60 skenario test case** (BB-001a sampai BB-038a), dikelompokkan per modul dalam **11 tabel** (Tabel 1 s/d Tabel 11) + 1 tabel ringkasan, dengan semua kolom terisi penuh sesuai aturan di atas. Pastikan setiap Langkah Pengujian dan Hasil yang Diharapkan ditulis secara detail dan bernomor, bukan hanya satu kalimat singkat.

---

## ⬆️ SELESAI COPY SAMPAI SINI ⬆️
