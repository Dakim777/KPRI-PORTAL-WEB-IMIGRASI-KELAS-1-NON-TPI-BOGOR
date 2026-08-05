# USE CASE DIAGRAM DAN SPESIFIKASI USE CASE
## KOPERASI KANTOR IMIGRASI KELAS I NON-TPI BOGOR

---

> **FILE DRAW.IO DIAGRAM:**
> File diagram Use Case ini berada dalam folder yang sama: `use_case_diagram.drawio`.
> Silakan buka file `use_case_diagram.drawio` menggunakan [Draw.io (https://app.diagrams.net/)](https://app.diagrams.net/) untuk mendapatkan diagram visual dengan Stickman Actor dan Oval/Ellipse Use Case standar UML.

---

## 1. DESKRIPSI AKTOR SISTEM

| Aktor | Posisi | Deskripsi |
| :--- | :--- | :--- |
| **Pemohon (Publik)** | Aktor Kiri | Masyarakat yang melacak resi paspor tanpa login. |
| **Kasir Paspor** | Aktor Kiri | Petugas koperasi yang mencatat dan mengelola pengiriman paspor. |
| **Super Admin** | Aktor Kanan | Administrator penuh: kelola user, audit log, laporan Excel. |

---

## 2. DAFTAR USE CASE SISTEM

1. **UC-01: Lacak Status Paspor:** Pemohon memasukkan resi/ID transaksi untuk melacak paspor.
2. **UC-02: Cek Timeline Resi:** Pemohon melihat riwayat perjalanan ekspedisi secara live.
3. **UC-03: Login System:** Otentikasi hak akses bagi Kasir dan Super Admin.
4. **UC-04: Input Transaksi Paspor:** Kasir mencatat data pemohon paspor dan pilihan kurir.
5. **UC-05: Scan Barcode Resi:** Pemindaian barcode resi fisik via kamera webcam.
6. **UC-06: Kelola Status Pengiriman:** Memperbarui status transaksi (transaksi, diproses, selesai, batal).
7. **UC-07: Cetak Struk Kwitansi:** Mencetak bukti transaksi berformat fisik/thermal.
8. **UC-08: Cek Status WA Gateway:** Memantau koneksi WhatsApp microservice (Baileys daemon).
9. **UC-09: Kelola Pengguna:** Super Admin menambah/mengedit akun kasir dan hak akses.
10. **UC-10: Lihat Audit Trail:** Meninjau catatan aktivitas sistem (Spatie Activity Log).
11. **UC-11: Kelola Tempat Sampah:** Mengatur transaksi yang dihapus sementara (Soft Delete/Trash).
12. **UC-12: Export Laporan Excel:** Mengunduh rekapitulasi data keuangan transaksi ke file `.xlsx`.
13. **UC-13: Kirim Notifikasi WA (Internal System):** Pemicuan pesan otomatis ke WA pemohon.
14. **UC-14: Fetch BinderByte API (External Service):** Pengambilan data resi live dari 9 kurir ekspedisi.

---

## 3. SPESIFIKASI USE CASE (NARRATIVE SPECIFICATIONS)

### A. UC-01: Lacak Status Paspor Publik
- **Aktor:** Pemohon (Masyarakat/Publik)
- **Pre-Condition:** Pemohon membuka halaman utama website (`/`).
- **Post-Condition:** Status pengiriman dan timeline resi live ditampilkan.
- **Alur Utama:**
  1. Pemohon memasukkan ID Transaksi (`KBG-YYMMDD-XXX`) atau Nomor Resi di kolom pencarian.
  2. Pemohon menekan tombol "Lacak Paspor".
  3. Sistem mencari data di database lokal.
  4. Sistem mengambil data tracking live via BinderByte API.
  5. Sistem menampilkan timeline pengiriman lengkap.

### B. UC-04: Input Transaksi Paspor Baru
- **Aktor:** Kasir Paspor, Super Admin
- **Pre-Condition:** Petugas sudah login ke sistem.
- **Post-Condition:** Data transaksi tersimpan (status: transaksi), PII dienkripsi AES-256, notifikasi WA terkirim otomatis.
- **Alur Utama:**
  1. Petugas membuka menu "Tambah Paspor".
  2. Petugas mengisi nama penerima, jumlah paspor, alamat, nomor HP, dan rincian biaya.
  3. Petugas menekan tombol "Simpan Transaksi".
  4. Sistem membuat ID Transaksi unik (`KBG-YYMMDD-XXX`) dan mengenkripsi data PII.
  5. Sistem memicu pesan WhatsApp otomatis ke nomor HP pemohon.
  6. Sistem mengarahkan ke halaman struk/detail transaksi.

### C. UC-05: Scan Barcode Resi via Webcam
- **Aktor:** Kasir Paspor, Super Admin
- **Pre-Condition:** Petugas membuka modal edit resi pada transaksi.
- **Post-Condition:** Nomor resi terisi otomatis tanpa ketik manual.
- **Alur Utama:**
  1. Petugas menekan tombol "Scan Barcode Resi".
  2. Kamera webcam aktif menampilkan area pemindaian.
  3. Petugas mengarahkan barcode/QR resi fisik ke kamera.
  4. Sistem mendeteksi kode, membunyikan beep sukses, dan mengisi form resi.
  5. Petugas menyimpan perubahan.

### D. UC-12: Export Laporan Transaksi ke Excel
- **Aktor:** Super Admin
- **Pre-Condition:** Super Admin berada di halaman laporan/dashboard.
- **Post-Condition:** File `.xlsx` terunduh berisi data transaksi yang sudah terdekripsi.
- **Alur Utama:**
  1. Super Admin menentukan filter tanggal periode laporan.
  2. Super Admin menekan tombol "Export Excel".
  3. Sistem memproses data via pustaka Laravel Excel (`maatwebsite/excel`).
  4. File `.xlsx` tergenerasi dan otomatis terunduh di browser.
