# GAMBARAN UMUM APLIKASI WEB
## PORTAL KOPERASI KANTOR IMIGRASI KELAS I NON-TPI BOGOR

---

> **FILE DRAW.IO DIAGRAM:**
> File visual diagram arsitektur sistem ini berada dalam folder yang sama: `gambaran_umum.drawio`.
> Silakan buka file `gambaran_umum.drawio` menggunakan [Draw.io (https://app.diagrams.net/)](https://app.diagrams.net/) atau aplikasi Draw.io Desktop untuk melihat dan mengunduh diagram visual resolusi tinggi.

---

## 1. APA ITU SISTEM INI?
Aplikasi web ini adalah portal resmi Koperasi Kantor Imigrasi Kelas I Non-TPI Bogor yang dibuat untuk mengelola dan melacak proses pengiriman paspor dari kantor imigrasi ke rumah pemohon secara otomatis dan terintegrasi.

Melalui sistem ini, petugas koperasi (kasir) dapat mencatat pengiriman paspor dengan cepat, dan masyarakat pemohon paspor dapat memantau keberadaan paspor mereka secara langsung (live tracking) melalui internet tanpa harus datang kembali ke kantor imigrasi.

---

## 2. MASALAH YANG DISLESAIKAN
Sebelum sistem ini dibuat, pengiriman paspor masih dikelola secara manual:
- Pencatatan masih menggunakan buku atau tabel Excel biasa.
- Konfirmasi ke pemohon masih diketik manual lewat WhatsApp pribadi petugas.
- Pemohon tidak bisa melacak sendiri posisi paspornya.

Dengan adanya aplikasi web ini:
- Pencatatan dilakukan terpusat dalam satu sistem web.
- Notifikasi WhatsApp dikirimkan secara otomatis ke HP pemohon saat transaksi dibuat.
- Pemohon bisa melacak status pengiriman sendiri di website kapan saja tanpa perlu buat akun atau login.
- Laporan transaksi dan keuangan dapat langsung diunduh ke Excel oleh bendahara koperasi.

---

## 3. ALUR KERJA APLIKASI (CARA KERJA RINGKAS)

1. **Kasir Menginput Data:**
   Kasir membuka menu input paspor, memasukkan nama pemohon, alamat, nomor HP, dan memilih layanan kurir. Kasir juga bisa langsung scan barcode resi fisik menggunakan kamera webcam laptop tanpa perlu mengetik nomor resi secara manual.

2. **Sistem Membuatkan Kode Unik & Kirim WhatsApp:**
   Sistem otomatis membuatkan ID Transaksi unik (contoh: `KBG-260731-001`) dan langsung mengunci/mengenkripsi data pribadi pemohon agar aman. Pada saat yang sama, sistem otomatis mengirimkan pesan WhatsApp ke pemohon berisi bukti transaksi dan link lacak.

3. **Pemohon Melacak Paspor:**
   Pemohon tinggal membuka alamat website utama, memasukkan ID Transaksi atau nomor resi, lalu klik "Lacak". Sistem akan menampilkan posisi paket paspor dari kurir (POS, JNE, SiCepat, dll.) secara real-time.

4. **Rekap Laporan:**
   Admin atau Bendahara dapat melihat grafik transaksi harian dan mengunduh laporan keuangan ke file Excel untuk kebutuhan rekap bulanan.

---

## 4. PENGGUNA SISTEM (AKTOR)

Sistem ini memiliki 3 kelompok pengguna:

1. **Masyarakat / Pemohon Paspor (Publik)**
   - Tidak perlu login/buat akun.
   - Hanya perlu membuka web untuk melacak status dan posisi paket paspor.

2. **Kasir Paspor**
   - Perlu login.
   - Bertugas menginput transaksi paspor baru, scan resi, mengupdate status pengiriman, dan mencetak struk fisik.

3. **Super Admin**
   - Perlu login (hak akses penuh).
   - Memiliki semua fungsi kasir, ditambah kelola akun kasir, melihat riwayat aktivitas (audit log), mengelola data yang dihapus (trash), dan mengunduh laporan Excel.
