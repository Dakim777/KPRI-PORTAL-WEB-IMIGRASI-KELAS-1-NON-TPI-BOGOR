# 📋 CONTEXTING AI — PEMBUATAN TERM OF REFERENCE (TOR / KERANGKA ACUAN KERJA)
# Sistem Informasi Pelacakan Pengiriman Paspor — Koperasi Imigrasi Bogor

---

## 🤖 PANDUAN UNTUK AI ASSISTANT (BACA DAHULU SEBELUM MEMBUAT TOR)

Dokumen ini adalah panduan konteks (*contexting*) lengkap yang harus dibaca oleh AI Assistant (Gemini, Claude, atau Copilot) **sebelum** membantu menyusun dokumen TOR (Term of Reference) / Kerangka Acuan Kerja. Gunakan semua informasi yang ada di sini sebagai bahan utama penyusunan TOR.

---

## I. IDENTITAS SISTEM & PROJECT

| Parameter | Detail |
| :--- | :--- |
| **Nama Sistem** | Sistem Informasi Pelacakan Pengiriman Paspor dan Resi Ekspedisi Koperasi Kantor Imigrasi Kelas I Non-TPI Bogor |
| **Nama Singkat** | Portal Koperasi Imigrasi Bogor |
| **URL Sistem (Live)** | `https://kpriimigrasibogor.my.id` |
| **Jenis Sistem** | Aplikasi Web (Web-Based Information System) |
| **Framework Utama** | Laravel 11 (PHP 8.4) |
| **Database** | MySQL 8.0 |
| **Web Server** | Nginx v1.30.3 di atas Ubuntu 22.04 LTS (VPS) |
| **Institusi Pengguna** | Koperasi Karyawan Kantor Imigrasi Kelas I Non-TPI Bogor |
| **Jenis Kelembagaan** | Instansi Pemerintah (Kementerian Hukum dan HAM RI) |
| **Dikembangkan Oleh** | Mahasiswa Informatika, Institut Teknologi Sumatera (ITERA) |
| **Status Saat Ini** | Production — Aktif digunakan di kantor |

---

## II. LATAR BELAKANG SISTEM (BACKGROUND)

Sebelum sistem ini dibuat, proses pengelolaan distribusi paspor ke pemohon dilakukan secara **manual dan tidak terintegrasi**. Kasir koperasi mencatat data pemohon dan resi pengiriman di buku tulis atau spreadsheet lokal yang tidak bisa diakses oleh semua pihak. Masyarakat pemohon paspor tidak mengetahui posisi dan status pengiriman paspornya karena tidak ada mekanisme notifikasi atau pelacakan resmi dari koperasi.

**Masalah utama yang diidentifikasi sebelum sistem dibangun:**
1. Tidak ada sistem pencatatan transaksi pengiriman yang terpusat dan aman.
2. Data pemohon (nama, alamat, nomor HP) dicatat di kertas tanpa enkripsi, berisiko bocor.
3. Pemohon harus datang kembali ke kantor atau menelepon untuk mengetahui status paspornya.
4. Tidak ada laporan keuangan digital yang bisa dicetak/diunduh oleh bendahara koperasi.
5. Proses konfirmasi pengiriman menggunakan WhatsApp pribadi kasir, tidak profesional.

---

## III. TUJUAN PEMBANGUNAN SISTEM (OBJECTIVES)

1. Membangun sistem informasi manajemen logistik paspor yang terpusat, terdigitalisasi, dan aman.
2. Menyediakan portal pelacakan resi publik yang dapat diakses masyarakat tanpa memerlukan akun login.
3. Mengintegrasikan pelacakan resi *real-time* dari 9 ekspedisi nasional menggunakan API BinderByte.
4. Mengotomatisasi pengiriman notifikasi status pengiriman paspor ke nomor HP pemohon melalui WhatsApp.
5. Melindungi data pribadi pemohon (PII) dengan enkripsi AES-256 di tingkat basis data.
6. Menyediakan fitur ekspor laporan keuangan transaksi ke format Excel (.xlsx) bagi bendahara.
7. Membangun sistem audit trail (log aktivitas) untuk merekam seluruh tindakan pengguna di sistem.

---

## IV. RUANG LINGKUP SISTEM (SCOPE OF WORK)

### Modul 1: Portal Pelacakan Publik (Tanpa Login)
- Halaman utama berupa antarmuka pencarian resi publik yang responsif.
- Pencarian menggunakan **Nomor Resi Ekspedisi** atau **ID Transaksi (Format: KBG-YYMMDD-XXX)**.
- Menampilkan riwayat pergerakan paket (tracking history) secara detail dalam Bahasa Indonesia.
- Algoritma Multi-Courier Auto-Discovery: Jika resi tidak ditemukan di kurir A, otomatis dicari ke kurir B-I.
- Proteksi Graceful Fallback: Jika API ekspedisi gagal, sistem menampilkan pesan ramah (tanpa error 500).

### Modul 2: Manajemen Transaksi Paspor (Kasir)
- Input data pemohon paspor: nama pemegang paspor, alamat pengiriman, nomor HP, pilihan ekspedisi.
- Kalkulasi otomatis biaya total: biaya kurir + biaya materai + surat kuasa + handling fee koperasi.
- Pembuatan ID Transaksi unik otomatis dengan format `KBG-YYMMDD-XXX`.
- Manajemen status pengiriman: `transaksi` → `diproses` → `selesai` → `batal`.
- Input nomor resi via **Webcam Barcode/QR Scanner** (tanpa ketik manual, bebas typo).
- Pencetakan struk/kwitansi termal berisi QR Code dan ID Transaksi untuk pemohon.

### Modul 3: Gateway Notifikasi WhatsApp Otomatis
- Pengiriman otomatis pesan WhatsApp ke nomor HP pemohon saat transaksi dibuat.
- Pesan memuat ID Transaksi, detail paket, dan tautan (*direct link*) pelacakan langsung.
- Berbasis Self-Hosted Gateway (`@whiskeysockets/baileys` di Node.js), tanpa biaya langganan vendor.
- Dilindungi oleh Enkripsi End-to-End (E2EE) bawaan WhatsApp.

### Modul 4: Pelaporan & Export Data
- Dasbor statistik dengan grafik rekap transaksi harian, bulanan, dan tahunan.
- Export laporan transaksi paspor ke file Excel (.xlsx) menggunakan Laravel Excel (maatwebsite/excel).
- Laporan memuat detail keuangan lengkap, status pengiriman, dan data PII yang aman.

### Modul 5: Administrasi Sistem (Super Admin)
- Manajemen pengguna dan peran akses (Role-Based Access Control / RBAC): `super_admin` dan `kasir_paspor`.
- Audit Log (Activity Log) menggunakan paket `spatie/laravel-activitylog`: merekam siapa mengubah apa dan kapan.
- Manajemen Tempat Sampah (Soft Delete Trash): transaksi yang dihapus dapat dipulihkan (restore) atau dihapus permanen.

---

## V. TEKNOLOGI & ARSITEKTUR YANG DIGUNAKAN

### A. Arsitektur Sistem
Sistem dibangun menggunakan pola **Internal Microservices Architecture**:
- **Monolithic Core (Laravel/PHP):** Mengelola semua logika bisnis, basis data, dan antarmuka.
- **Background Service/Daemon (Node.js):** Mengelola koneksi WebSocket persisten untuk Gateway WhatsApp. Keduanya berkomunikasi via **Internal REST API** di `http://127.0.0.1:3001`.

### B. Stack Teknologi Lengkap
| Layer | Teknologi |
| :--- | :--- |
| Backend Framework | Laravel 11 (PHP 8.4) |
| Frontend Styling | TailwindCSS |
| Database | MySQL 8.0 |
| ORM | Eloquent (Laravel built-in) |
| Web Server | Nginx v1.30.3 |
| SSL/HTTPS | Let's Encrypt (auto-renew) |
| WA Gateway Engine | `@whiskeysockets/baileys` (Node.js v18+) |
| Tracking API Provider | BinderByte API (9 ekspedisi nasional) |
| Export Excel Library | `maatwebsite/excel` (di atas PhpSpreadsheet) |
| Activity Log Library | `spatie/laravel-activitylog` v5 |
| Barcode/QR Scanner | `Html5QrcodeScanner` (JavaScript Browser API) |
| Enkripsi Database | Laravel Encrypted Casts (AES-256) |
| Hosting/VPS | Ubuntu 22.04 LTS + aaPanel |

---

## VI. PENGGUNA SISTEM (STAKEHOLDERS / AKTOR)

| Aktor | Hak Akses | Kegiatan Utama |
| :--- | :--- | :--- |
| **Masyarakat Umum (Pemohon Paspor)** | Publik (Tanpa Login) | Melacak status resi dan posisi paket paspor |
| **Kasir Paspor (kasir_paspor)** | Login Terbatas | Input transaksi, scan barcode, cetak struk, cek status WA Gateway |
| **Super Admin (super_admin)** | Login Penuh | Kelola semua transaksi, audit log, manajemen user, tempat sampah |
| **Bendahara Koperasi** | (via Admin) | Mengunduh laporan Excel bulanan untuk rekonsiliasi keuangan |

---

## VII. KEUNGGULAN & DIFERENSIASI SISTEM

1. **Zero Vendor Lock-In Notification:** Tidak bergantung pada API komersial (Wablas, Fonnte, Twilio). Notifikasi WhatsApp gratis selamanya berbasis Self-Hosted Baileys.
2. **Zero Collision Tracking:** Format ID Transaksi `KBG-YYMMDD-XXX` yang unik setiap hari, mustahil bertabrakan (terduplikat).
3. **Multi-Courier Intelligence:** Satu nomor resi dapat dikenal secara otomatis dari 9 kurir berbeda tanpa kasir perlu memilih manual.
4. **PII Security at Database Level:** Nomor HP dan alamat pemohon terenkripsi AES-256 di database, tidak dapat dibaca walaupun server diretas secara mentah.
5. **Optimized for Low-Spec Office PCs:** Antarmuka dioptimalkan tanpa efek visual berat (tanpa `backdrop-blur`), memastikan performa lancar di PC kantor dengan GPU terintegrasi.

---

## VIII. PANDUAN PEMBUATAN TOR UNTUK AI ASSISTANT

Gunakan semua informasi di atas untuk menyusun sebuah dokumen **Kerangka Acuan Kerja (Term of Reference / TOR)** yang lengkap, formal, dan akademis.

### Struktur TOR yang Harus Dibuat:
Berikut adalah struktur TOR standar yang sering digunakan dalam proyek sistem informasi instansi/pemerintahan dan skripsi informatika:

```
1.  PENDAHULUAN / LATAR BELAKANG
    - Kondisi sebelum sistem ada (permasalahan)
    - Alasan digitalisasi dibutuhkan

2.  MAKSUD DAN TUJUAN
    - Maksud umum pembangunan sistem
    - Tujuan spesifik yang ingin dicapai (berdasarkan Section III di atas)

3.  RUANG LINGKUP PEKERJAAN (SCOPE OF WORK)
    - Daftar modul/fitur yang dibangun (berdasarkan Section IV di atas)
    - Batasan sistem (apa yang TIDAK termasuk)

4.  SASARAN DAN TARGET PENGGUNA
    - Siapa yang menjadi target penerima manfaat (berdasarkan Section VI)

5.  METODOLOGI PENGEMBANGAN
    - Metode pengembangan yang digunakan (misalnya: Waterfall / Agile / Prototype)
    - Tools dan teknologi yang digunakan (berdasarkan Section V)

6.  OUTPUT / DELIVERABLES (KELUARAN)
    - Produk akhir yang dihasilkan (misalnya: Website live, Laporan Skripsi, Dokumentasi Sistem)

7.  JADWAL PELAKSANAAN (TIMELINE)
    - Perkiraan tahapan pengerjaan dari awal hingga akhir

8.  PENUTUP
    - Harapan dan komitmen pengembang
```

### Instruksi Gaya Penulisan untuk AI:
- Gunakan bahasa yang **formal, akademis, dan teknis** sesuai standar dokumen resmi pemerintah.
- Hindari bahasa santai atau percakapan sehari-hari.
- Setiap poin harus **spesifik dan dapat diverifikasi** (menggunakan nama teknologi asli, angka, dan nama modul yang nyata).
- **JANGAN** menyebut "KPRI" — gunakan "Koperasi" saja.
- Pastikan seluruh nama fitur dan modul selaras dengan yang tertulis di dokumen contexting ini.

---
*Dokumen TOR.md ini adalah panduan konteks (prompting context) untuk AI Assistant. Gunakan sebagai bahan dasar penyusunan Kerangka Acuan Kerja (KAK/TOR) resmi sistem.*
