# 🤖 CONTEXTING PROMPT UNTUK GEMINI AI — PEMBUATAN BAB V HASIL IMPLEMENTASI
## Sistem Portal KPRI Kantor Imigrasi Kelas I Non-TPI Bogor

Salin seluruh teks di bawah ini (dari `⬇️ MULAI COPY` sampai `⬆️ SELESAI COPY`), lalu paste ke sesi chat baru di Gemini AI. Gemini akan membuatkan isi BAB V lengkap dalam format HTML siap copy-paste ke Microsoft Word.

---

## ⬇️ MULAI COPY DARI SINI ⬇️

---

Kamu adalah seorang penulis laporan PKL (Praktik Kerja Lapangan) yang ahli. Saya butuh kamu menuliskan **BAB V — HASIL IMPLEMENTASI** lengkap untuk laporan PKL saya. Outputnya harus berupa **file HTML** dengan format Times New Roman 12pt, spasi 1.5, rata kiri-kanan (justify), dan **langsung siap di-copy-paste ke Microsoft Word** tanpa perlu formatting ulang.

---

## INFORMASI PROYEK

**Judul Proyek:**
"Rancang Bangun Sistem Informasi Pelacakan Pengiriman Paspor Berbasis Web Menggunakan Live Tracking API dan WhatsApp Gateway pada Koperasi Kantor Imigrasi Kelas I Non-TPI Bogor"

**URL Produksi:** `https://kpriimigrasibogor.my.id`

**Tech Stack:** Laravel 13, MySQL 8.0, PHP 8.4, Nginx, Node.js (WhatsApp Baileys Gateway), BinderByte API (Pelacakan Resi Ekspedisi), Html5QrcodeScanner (Barcode Scanner Webcam)

**Instansi:** Koperasi Karyawan (KPRI) Kantor Imigrasi Kelas I Non-TPI Bogor

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

## STRUKTUR BAB V YANG HARUS DIBUAT

Berdasarkan panduan kampus, BAB V terdiri dari **2 sub-bab utama**:

### 5.1. Hasil Implementasi
> Tuliskan hasil dari proses pengembangan. Tuliskan hasil pengujian dari semua fitur-fitur yang sudah dirancang pada bab IV berdasarkan metodologi yang digunakan selama menyelesaikan dan mengembangkan perangkat lunak.

### 5.2. Evaluasi dan Analisis
> Tuliskan evaluasi dan analisis sederhana, dapat berupa tabel, visualisasi data, atau narasi, berdasarkan hasil implementasi dan pengujian yang didapatkan. Evaluasi dapat menceritakan hasil presentasi terhadap instansi bersangkutan, termasuk stakeholder yang terlibat dan tanggal presentasi.

---

## ARAHAN DOSEN PEMBIMBING — TOPIK MINIMUM YANG WAJIB DIBAHAS DI BAB V

Dosen pembimbing memberikan arahan bahwa **BAB V minimal harus membahas topik-topik berikut**:

1. **Dashboard** — Tampilkan dan jelaskan hasil implementasi halaman dashboard (Super Admin & Kasir Paspor)
2. **Login** — Tampilkan dan jelaskan halaman login beserta mekanisme autentikasi
3. **CRUD** — Tampilkan dan jelaskan fitur Create, Read, Update, Delete transaksi paspor
4. **Tracking** — Tampilkan dan jelaskan fitur pelacakan publik dan pelacakan resi internal
5. **WhatsApp Gateway** — Tampilkan dan jelaskan fitur notifikasi WhatsApp otomatis
6. **Barcode Scanner** — Tampilkan dan jelaskan fitur pemindai barcode/QR resi via webcam
7. **Hasil API** — Tampilkan dan jelaskan hasil integrasi API BinderByte (cek ongkir + lacak resi)
8. **Pengujian Black Box** — Sajikan hasil pengujian Black Box Testing lengkap
9. **Pengujian UAT** — Sajikan hasil pengujian User Acceptance Testing lengkap

---

## DETAIL ISI SETIAP SUB-BAB YANG HARUS DITULIS

### 5.1. Hasil Implementasi

Bab ini menjelaskan hasil dari proses pengembangan sistem secara keseluruhan. Setiap fitur yang telah dirancang pada Bab IV diimplementasikan dan ditampilkan hasilnya. Berikut adalah detail fitur per sub-sub-bab:

#### 5.1.1. Halaman Login
- Jelaskan halaman login yang digunakan oleh kasir paspor dan super admin untuk masuk ke sistem.
- URL: `https://kpriimigrasibogor.my.id/login`
- Fitur: form email + password, checkbox "Remember Me", validasi input.
- Jika email/password salah, muncul pesan error "Email atau password salah."
- Setelah login berhasil, sistem secara otomatis mengarahkan pengguna ke dashboard sesuai role (super_admin → `/admin/dashboard`, kasir_paspor → `/paspor/dashboard`).
- Tambahkan placeholder: **[Gambar 5.1. Halaman Login Sistem]**

#### 5.1.2. Dashboard
- Jelaskan **2 jenis dashboard**:
  - **Dashboard Super Admin** (`/admin/dashboard`): menampilkan total transaksi paspor keseluruhan, transaksi hari ini, transaksi bulan ini, omset bulan ini, dan 10 transaksi terbaru.
  - **Dashboard Kasir Paspor** (`/paspor/dashboard`): menampilkan statistik harian & bulanan (total transaksi hari ini, omset hari ini, total bulan ini, omset bulan ini), grafik chart bar 7 hari terakhir (jumlah pengiriman per hari), dan rekap breakdown status pengiriman bulan ini (diproses, dikirim, selesai, kendala).
- Tambahkan placeholder: **[Gambar 5.2. Dashboard Super Admin]** dan **[Gambar 5.3. Dashboard Kasir Paspor]**

#### 5.1.3. CRUD Transaksi Paspor
Jelaskan 4 operasi CRUD:

**a) Create — Tambah Transaksi Baru (`/paspor/create`)**
- Form input: Nama Penerima, Jumlah Paspor, Daftar Nama Paspor (dinamis, bisa tambah/hapus), Alamat Tujuan Pengiriman, Tanggal Transaksi, No HP Pemohon, Jenis Kurir (dropdown 9 ekspedisi: POS, JNE, TIKI, SiCepat, J&T, AnterAja, Lion Parcel, Ninja Xpress, ID Express), Layanan Kurir, Metode Pembayaran (Cash/Transfer/QRIS), Biaya Kurir, Handling Fee, Biaya Materai, Biaya Surat Kuasa, Estimasi Tanggal Selesai.
- Fitur unggulan: **Cek Ongkir Real-time** — kasir dapat mengecek estimasi biaya pengiriman ke kota tujuan langsung dari form melalui integrasi API BinderByte.
- Setelah berhasil, sistem otomatis meng-generate **ID Transaksi** format `KBG-YYMMDD-XXX` (contoh: `KBG-260722-001`) dan **No Struk** yang reset harian.
- Placeholder: **[Gambar 5.4. Halaman Tambah Transaksi Paspor]**

**b) Read — Lihat Daftar & Detail Transaksi (`/paspor` dan `/paspor/{id}`)**
- Tabel riwayat pengiriman dengan kolom: No Struk, ID Transaksi, Nama Penerima, Jumlah Paspor, Tanggal, Status, Kurir, Total Bayar, Kasir.
- Mendukung fitur pencarian (search by keyword), filter status (transaksi/diproses/selesai/batal), filter tanggal, filter bulan/tahun, dan pagination (20 data per halaman).
- Halaman detail menampilkan seluruh informasi transaksi termasuk data PII yang di-decrypt secara otomatis.
- Placeholder: **[Gambar 5.5. Halaman Daftar Transaksi Paspor]** dan **[Gambar 5.6. Halaman Detail Transaksi]**

**c) Update — Edit Transaksi (`/paspor/{id}/edit`) & Update Status (`PATCH /paspor/{id}/status`)**
- Kasir dapat mengedit seluruh data transaksi yang sudah ada.
- Kasir dapat mengubah status pengiriman melalui dropdown: `transaksi` (Biru) → `diproses` (Kuning) → `selesai` (Hijau) / `batal` (Merah).
- Saat status diubah ke "diproses": tanggal pengiriman otomatis terisi dan biaya modal packaging default Rp 1.000 ter-set otomatis.
- Saat status diubah ke "selesai": tanggal paspor diterima otomatis terisi.
- Status "selesai" hanya dapat diubah secara manual oleh kasir (Operator-Centric, tidak ada auto-sync).
- Placeholder: **[Gambar 5.7. Halaman Edit Transaksi]** dan **[Gambar 5.8. Modal Update Status]**

**d) Delete — Hapus Transaksi (`DELETE /paspor/{id}`)**
- Hanya role `super_admin` yang diizinkan menghapus transaksi (kasir_paspor mendapat 403 Forbidden).
- Menggunakan mekanisme **Soft Delete** — data tidak langsung hilang, melainkan masuk ke Trash.
- Super Admin dapat me-restore data dari Trash atau menghapus permanen (Force Delete).
- Placeholder: **[Gambar 5.9. Konfirmasi Hapus Transaksi]** dan **[Gambar 5.10. Halaman Trash]**

#### 5.1.4. Pelacakan Paspor (Tracking)
Jelaskan 2 jenis pelacakan:

**a) Pelacakan Publik (Tanpa Login) — Halaman Utama (`/`)**
- Masyarakat pemohon dapat langsung membuka `https://kpriimigrasibogor.my.id` untuk melacak status paspor mereka **tanpa perlu login atau membuat akun**.
- Input pelacakan: **Nomor Resi** atau **ID Transaksi** (contoh: `KBG-260722-001`).
- Mendukung **Smart Normalizer** — input `kbg260722001` (tanpa tanda hubung, huruf kecil) tetap dapat ditemukan.
- Nama penerima ditampilkan dalam bentuk **masked** (contoh: `Budi Santoso` → `Bud*** ***oso`) demi perlindungan data pribadi sesuai UU PDP.
- Jika data tidak ditemukan, sistem menampilkan pesan ramah "Data tidak ditemukan" (Graceful Fallback) tanpa error 500.
- Placeholder: **[Gambar 5.11. Halaman Pelacakan Publik]** dan **[Gambar 5.12. Hasil Pelacakan Publik]**

**b) Pelacakan Internal (Kasir) — Lacak Resi dari Detail Transaksi**
- Kasir dapat melacak posisi paket real-time dari halaman detail transaksi melalui tombol "Lacak Resi".
- Sistem memanggil API BinderByte dan menampilkan timeline kronologis pergerakan paket beserta status terbaru.
- Mendukung **Multi-Courier Auto-Discovery** — jika resi tidak terdeteksi di kurir utama, sistem otomatis memindai ke 8 kurir lainnya sebelum menampilkan pesan Graceful Fallback.
- Placeholder: **[Gambar 5.13. Hasil Lacak Resi Internal]**

#### 5.1.5. WhatsApp Gateway
- Sistem terintegrasi dengan **WhatsApp Gateway** berbasis Node.js (`@whiskeysockets/baileys`) yang berjalan secara self-hosted di server VPS (`http://127.0.0.1:3001`).
- **Notifikasi Otomatis:** Saat kasir mengubah status pengiriman ke "diproses" atau "selesai" dan nomor resi serta nomor HP pemohon tersedia, sistem secara otomatis mengirimkan pesan WhatsApp ke pemohon berisi informasi resi, status terbaru, dan URL direct-track (`https://kpriimigrasibogor.my.id/?q=KBG-260722-001`) yang ketika diklik langsung membuka halaman pelacakan.
- **Kirim Ulang Manual:** Kasir juga dapat mengirim ulang notifikasi melalui tombol "Kirim WA" di halaman detail transaksi.
- **Halaman Manajemen WA Gateway:** Kasir/Admin dapat melihat status koneksi bot, memindai QR Code untuk pairing, mengirim pesan tes, dan logout sesi WhatsApp.
- Placeholder: **[Gambar 5.14. Halaman Manajemen WhatsApp Gateway]** dan **[Gambar 5.15. Contoh Pesan WhatsApp Notifikasi Otomatis]**

#### 5.1.6. Barcode Scanner Webcam
- Pada tabel riwayat pengiriman dan halaman detail, terdapat fitur **modal pemindai barcode/QR** menggunakan library `Html5QrcodeScanner`.
- Kasir cukup mengarahkan kamera webcam komputer ke barcode atau QR Code pada resi fisik → sistem secara otomatis mendeteksi dan mengisi nomor resi ke dalam form tanpa perlu mengetik manual (zero typo).
- Saat resi berhasil terdeteksi, sistem membunyikan suara notifikasi **beep** sebagai konfirmasi.
- Placeholder: **[Gambar 5.16. Modal Barcode Scanner Webcam]**

#### 5.1.7. Hasil Integrasi API (BinderByte)
Jelaskan 2 fitur API:

**a) Cek Ongkir (Estimasi Tarif Pengiriman)**
- Kasir dapat mengecek estimasi tarif ongkir ke kota tujuan dari halaman form tambah transaksi.
- Mendukung **9 ekspedisi**: POS Indonesia, JNE Express, TIKI, SiCepat Ekspres, J&T Express, AnterAja, Lion Parcel, Ninja Xpress, ID Express.
- Sistem menampilkan daftar layanan (Reguler, Kilat, dll) beserta estimasi biaya dan waktu pengiriman.
- Placeholder: **[Gambar 5.17. Hasil Cek Ongkir API]**

**b) Lacak Resi (Live Tracking)**
- Sistem menampilkan timeline pergerakan paket secara kronologis dari API BinderByte.
- Informasi yang ditampilkan: AWB (nomor resi), kurir, status terkini, riwayat pergerakan (tanggal, lokasi, keterangan).
- Jika resi belum terindeks di server ekspedisi, sistem menampilkan **Graceful Fallback Box** ("Belum Terindeks di POS / Ekspedisi") tanpa error.
- Placeholder: **[Gambar 5.18. Timeline Lacak Resi API BinderByte]**

#### 5.1.8. Cetak Struk dan Export Laporan
**a) Cetak Struk/Kwitansi**
- Kasir dapat mencetak bukti resi pengiriman bertampilan formal thermal/A4 yang berisi: ID Transaksi `KBG-...` di posisi paling atas, barcode 1D, URL pelacakan, nama penerima, daftar nama paspor, alamat tujuan, kurir & layanan, rincian biaya lengkap, dan nama kasir yang memproses.
- Placeholder: **[Gambar 5.19. Tampilan Cetak Struk Kwitansi]**

**b) Export Laporan Excel**
- Hanya Super Admin yang dapat mengunduh rekap transaksi dalam format `.xlsx`.
- Mendukung filter tanggal (dari - sampai) untuk mengunduh data pada periode tertentu.
- File Excel berisi rincian finansial lengkap (biaya kurir, handling fee, materai, surat kuasa, total bayar) dengan data PII yang di-decrypt secara aman saat export.
- Placeholder: **[Gambar 5.20. Halaman Export Laporan Excel]**

#### 5.1.9. Pengujian Black Box Testing

Tuliskan paragraf pengantar:
"Pengujian Black Box Testing dilaksanakan untuk memverifikasi fungsionalitas sistem dari perspektif pengguna akhir tanpa memperhatikan struktur kode internal. Metode ini berfokus pada validasi input-output, penanganan kesalahan (error handling), serta batasan nilai (boundary value analysis) pada setiap modul sistem. Setiap fitur diuji dengan minimal dua skenario: skenario data valid yang diharapkan berhasil, dan skenario data invalid yang diharapkan ditolak oleh sistem dengan pesan kesalahan yang informatif."

Kemudian jelaskan bahwa pengujian dilakukan terhadap **11 modul** dengan total **60 skenario test case** (valid + invalid). Cantumkan ringkasan modul yang diuji dalam bentuk tabel:

| No | Modul yang Diuji | Jumlah Skenario Valid | Jumlah Skenario Invalid | Total |
|:---:|:---|:---:|:---:|:---:|
| 1 | Login & Autentikasi | 4 | 2 | 6 |
| 2 | Pelacakan Publik | 4 | 2 | 6 |
| 3 | Transaksi Paspor — Tambah | 6 | 6 | 12 |
| 4 | Transaksi Paspor — Lihat, Edit, Hapus | 3 | 2 | 5 |
| 5 | Pencarian & Filter | 4 | 1 | 5 |
| 6 | Update Status Pengiriman | 4 | 1 | 5 |
| 7 | Manajemen Pengguna | 4 | 4 | 8 |
| 8 | Cek Ongkir & Lacak Resi | 3 | 2 | 5 |
| 9 | Cetak Struk & Export Laporan | 2 | 1 | 3 |
| 10 | Trash / Soft Delete | 2 | 0 | 2 |
| 11 | Keamanan & Otorisasi | 2 | 1 | 3 |
| | **TOTAL** | **38** | **22** | **60** |

Catatan: **Seluruh 60 skenario berstatus PASS (100% lulus)**. Tabel detail pengujian per modul dilampirkan pada bagian lampiran.

**PENTING:** Cukup tampilkan tabel ringkasan di atas saja. Tabel detail lengkap Black Box Testing (per modul, 7 kolom: ID, Skenario Pengujian, Data Uji, Langkah Pengujian, Hasil yang Diharapkan, Hasil Aktual, Status) sudah disiapkan di dokumen terpisah dan akan disertakan sebagai lampiran.

#### 5.1.10. Pengujian User Acceptance Testing (UAT)

Tuliskan paragraf pengantar:
"User Acceptance Testing (UAT) dilaksanakan untuk memastikan bahwa sistem yang dikembangkan telah memenuhi kebutuhan dan ekspektasi pengguna akhir. Pengujian ini melibatkan pengguna langsung dari instansi, yaitu kasir paspor dan administrator koperasi Kantor Imigrasi Kelas I Non-TPI Bogor. Setiap fitur diuji berdasarkan skenario penggunaan nyata sehari-hari di lingkungan operasional kantor."

Kemudian jelaskan bahwa pengujian dilakukan terhadap **13 modul** dengan total **43 test case**. Cantumkan ringkasan modul:

| No | Modul yang Diuji | Jumlah Test Case |
|:---:|:---|:---:|
| 1 | Modul Publik | 7 |
| 2 | Modul Autentikasi | 4 |
| 3 | Modul Dashboard | 2 |
| 4 | Modul Transaksi Paspor | 6 |
| 5 | Modul Status Pengiriman | 4 |
| 6 | Modul Barcode Scanner | 1 |
| 7 | Modul Cek Ongkir & Lacak Resi | 2 |
| 8 | Modul Cetak Struk & Export Excel | 2 |
| 9 | Modul WhatsApp Gateway | 2 |
| 10 | Modul Manajemen Pengguna | 5 |
| 11 | Modul Audit Log & Trash | 4 |
| 12 | Modul Profil Pengguna | 1 |
| 13 | Keamanan Data | 3 |
| | **TOTAL** | **43** |

Catatan: **Seluruh 43 test case berstatus PASS (100% lulus)**. Tabel detail UAT per modul dilampirkan pada bagian lampiran.

**PENTING:** Sama seperti Black Box, cukup tampilkan tabel ringkasan. Tabel detail lengkap UAT (7 kolom: ID, Feature, Input Data, Test Steps, Expected Results, Actual Results, Status) sudah disiapkan terpisah.

---

### 5.2. Evaluasi dan Analisis

Sub-bab ini berisi evaluasi dan analisis terhadap hasil implementasi dan pengujian. Tulis sub-sub-bab berikut:

#### 5.2.1. Analisis Hasil Pengujian
- Buat narasi analitis bahwa dari total **60 skenario Black Box Testing** dan **43 skenario UAT**, seluruhnya berstatus **PASS (100%)**.
- Artinya sistem telah berhasil memenuhi seluruh kebutuhan fungsional yang dirancang pada Bab IV dan kedelapan tujuan yang ditetapkan dalam TOR.
- Buat tabel pemetaan antara **8 Tujuan TOR** dengan **fitur yang mengimplementasikannya** dan **status verifikasi**:

| No | Tujuan (dari TOR) | Fitur yang Mengimplementasikan | Status |
|:---:|:---|:---|:---:|
| 1 | Sentralisasi data transaksi koperasi | Dashboard, CRUD Transaksi Paspor, Pencarian & Filter, Pagination | ✅ Tercapai |
| 2 | Pelacakan publik tanpa login | Halaman Pelacakan Publik (`/`), Smart Normalizer, Masking PII | ✅ Tercapai |
| 3 | Integrasi API BinderByte multi-kurir | Cek Ongkir, Lacak Resi Live Tracking, Multi-Courier Auto-Discovery | ✅ Tercapai |
| 4 | Notifikasi WhatsApp otomatis | WhatsApp Gateway (Baileys), Notifikasi Otomatis saat Update Status | ✅ Tercapai |
| 5 | Enkripsi data pribadi pemohon | AES-256 Encrypted Cast pada `no_hp`, `alamat_tujuan_pengiriman` | ✅ Tercapai |
| 6 | Export rekap transaksi Excel | Export Laporan Kompleks `.xlsx` dengan filter tanggal | ✅ Tercapai |
| 7 | Mencatat jejak aktivitas pengguna (Audit Trail) | Audit Log (`spatie/laravel-activitylog`), Soft Deletes & Trash Management | ✅ Tercapai |
| 8 | Pengalaman pengembangan hingga produksi | Deployment VPS Ubuntu 22.04, aaPanel, Nginx, HTTPS Let's Encrypt | ✅ Tercapai |

#### 5.2.2. Evaluasi Kinerja Sistem
Buat narasi evaluasi kinerja sistem di lingkungan produksi:
- Sistem telah di-deploy di VPS Ubuntu 22.04 dengan domain `https://kpriimigrasibogor.my.id` dan sertifikat SSL Let's Encrypt.
- Sistem mampu diakses secara stabil oleh kasir di kantor Imigrasi Bogor menggunakan komputer kantor berspesifikasi standar (tanpa GPU terdedikasi).
- Antarmuka telah dioptimasi untuk performa ringan — efek glassmorphism/backdrop-blur yang berat sudah dihapus dan diganti solid background agar tidak menyebabkan stuttering pada PC kantor.
- Global Loading Buffer melindungi setiap aksi form dari double-click pada koneksi lambat.
- Multi-Device Session memungkinkan kasir dan admin login bersamaan di perangkat berbeda tanpa saling logout.

#### 5.2.3. Evaluasi Keamanan Data
Buat narasi evaluasi keamanan:
- Data pribadi pemohon (nomor HP dan alamat pengiriman) disimpan dalam bentuk terenkripsi AES-256 di database MySQL, bukan plain text.
- Role-Based Access Control (RBAC) membatasi akses: `kasir_paspor` hanya mengakses modul paspor, `super_admin` mengakses seluruh sistem.
- Pencarian publik hanya menerima Nomor Resi dan ID Transaksi — pencarian berdasarkan nama telah dicabut untuk mencegah collision data pribadi (zero collision).
- WhatsApp Gateway berjalan self-hosted di VPS, data tidak dikirim ke vendor pihak ketiga.

#### 5.2.4. Hasil Presentasi kepada Instansi
Buat narasi umum (JANGAN isi tanggal spesifik, biarkan placeholder):
- Sistem telah dipresentasikan kepada pihak Koperasi Kantor Imigrasi Kelas I Non-TPI Bogor pada **[tanggal presentasi]**.
- Stakeholder yang hadir: **[nama dan jabatan stakeholder]**.
- Respons dan umpan balik dari instansi: sistem dinilai **sangat membantu** dalam mendigitalisasi pencatatan pengiriman paspor dan memberikan transparansi kepada masyarakat pemohon.
- Placeholder: **[Gambar 5.21. Dokumentasi Presentasi kepada Instansi]**

---

## ATURAN OUTPUT HTML

1. **Buat file HTML lengkap** (`<!DOCTYPE html>` sampai `</html>`).
2. **Font body:** Times New Roman, ukuran `12pt`, line-height `1.5` (spasi 1.5), text-align `justify`.
3. **Heading BAB:** `14pt`, bold, center-aligned, uppercase.
4. **Heading Sub-bab 5.1 / 5.2:** `12pt`, bold, left-aligned, numbered.
5. **Heading Sub-sub-bab 5.1.1 dst:** `12pt`, bold, left-aligned, numbered.
6. **Indentasi paragraf:** Setiap paragraf baru memiliki `text-indent: 1.27cm` (tab pertama).
7. **Tabel:** Border `1px solid #000`, font `10pt`, padding `4px 6px`, max-width `530px`, caption di atas tabel, header `#f2f2f2`.
8. **Placeholder gambar:** Tulis dalam format `[Gambar 5.X. Deskripsi Gambar]` dalam paragraf tersendiri, center-aligned, italic. JANGAN sisipkan gambar asli, cukup placeholder teks.
9. **Penomoran gambar:** Dimulai dari `Gambar 5.1` dan seterusnya secara berurutan.
10. **Penomoran tabel:** Dimulai dari `Tabel 5.1` dan seterusnya secara berurutan.
11. **Jangan tampilkan kode CSS/HTML di output chat.** Langsung berikan file HTML yang sudah jadi.
12. **Bahasa:** Seluruh teks ditulis dalam **Bahasa Indonesia formal/akademis** yang sesuai standar penulisan laporan PKL.
13. **Panjang minimal:** Setiap sub-sub-bab minimal 2-3 paragraf narasi deskriptif (jangan hanya 1 kalimat).
14. **PENTING — Sinkronisasi:** Pastikan semua istilah, nama modul, jumlah test case, dan penomoran yang disebutkan di BAB V ini **konsisten 100%** dengan dokumen Black Box Testing (60 skenario, 11 modul) dan UAT (43 test case, 13 modul) yang sudah dibuat sebelumnya.

---

Buatkan sekarang file HTML **BAB V — HASIL IMPLEMENTASI** lengkap dengan struktur 5.1 (10 sub-sub-bab: 5.1.1 s/d 5.1.10) dan 5.2 (4 sub-sub-bab: 5.2.1 s/d 5.2.4), ditulis dalam Bahasa Indonesia akademis yang detail dan komprehensif, sesuai semua aturan di atas.

---

## ⬆️ SELESAI COPY SAMPAI SINI ⬆️
