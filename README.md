# 🏢 PORTAL KPRI — IMIGRASI KELAS 1 NON-TPI BOGOR
**Sistem Koperasi Pegawai Republik Indonesia (KPRI) Kantor Imigrasi Kelas I Non-TPI Bogor**

---

## 🚀 STATUS SYSTEM & LIVE DEPLOYMENT

| Parameter | Keterangan |
| :--- | :--- |
| **Status Akhir** | ✅ **PRODUCTION — Live & Fully Operational (100% Siap Digunakan)** |
| **URL Live Web** | **`https://kpriimigrasibogor.my.id`** |
| **IP Server VPS** | `103.247.11.194` |
| **Port SSH VPS** | `22` (Default) |
| **OS / Panel** | Ubuntu 22.04 LTS | aaPanel (Baota) v8.0.4 |
| **Tech Stack** | Laravel 13 | PHP 8.4 (aaPanel) | Nginx 1.30.3 | MySQL 8.0 |
| **Sertifikat SSL** | Let's Encrypt HTTPS (Auto-Renew aktif sampai Oktober 2026) |
| **Repositori Git** | `https://github.com/Dakim777/KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR` |
| **Multi-Device Support** | ✅ Kasir & Admin dapat login bersamaan di berbagai komputer/device tanpa saling ter-logout (`same_site=lax`). |

---

## I. MODUL UTAMA APLIKASI

1. **Modul Kantin (POS & Inventaris)**
   - Kasir POS (Point of Sales) dengan dukungan pemindai barcode (`components/barcode-scanner`).
   - Manajemen Kategori Kantin dan Produk Kantin (metode FIFO inventaris).
   - Pencatatan Pembelian Kantin (Stok Masuk) & Transaksi Penjualan.
   - Cetak Struk dan Export Laporan Excel (Global & Detail).
2. **Modul Paspor (Layanan Pengiriman Paspor & Resi)**
   - Pencatatan permohonan pengiriman paspor (pemohon, daftar paspor, alamat tujuan, biaya kurir/materai/surat kuasa/handling fee).
   - Pengelolaan Status Pengiriman (`transaksi`, `diproses`, `selesai`, `batal`).
   - Struk Resi Pengiriman dan Export Laporan Excel (Dasar & Kompleks) dengan validasi enkripsi data PII.
3. **Modul Manajemen Pengguna & Log Aktivitas**
   - Role-based Access Control (`super_admin`, `kasir_paspor`, dll.).
   - Audit Trail (`spatie/laravel-activitylog`) untuk memantau semua aktivitas perubahan data oleh operator.
   - Trash Management (Soft Deletes restore/force delete).

---

## II. KRONOLOGI & RIWAYAT DEPLOYMENT DARI AWAL HINGGA PRODUCTION

### 1. Tahap Persiapan & Migrasi Awal ke VPS
- **Upload Aplikasi:** Arsip `.zip` project dikirim dari lokal Mac (`/Users/dakim/Herd/portal-koperasi`) dan di-extract ke direktori `/www/wwwroot/kpriimigrasibogor.my.id` di VPS melalui aaPanel.
- **Konfigurasi `.env` Production:** Konfigurasi diatur untuk production (`APP_ENV=production`, `APP_DEBUG=false`), `APP_URL` diarahkan ke HTTPS, serta koneksi database dihubungkan ke MySQL (`sql_kpriimigrasibogor_my_id`).

### 2. Tahap Debugging Ekosistem PHP & Composer (aaPanel Quirks)
Dalam proses instalasi (`composer install`), ditemukan 3 batasan ketat khas aaPanel yang berhasil diselesaikan:
- **`putenv()` Disabled:** aaPanel memasukkan `putenv` ke daftar `disable_functions` di file `/www/server/php/84/etc/php.ini`, menyebabkan Composer gagal berjalan. **Solusi:** Menghapus `putenv` dari `disable_functions` dan merestart service PHP-FPM 8.4 (`service php-fpm-84 restart`).
- **`ext-fileinfo` Missing di CLI:** Extension `fileinfo` dibutuhkan oleh paket `league/flysystem`, namun tidak ter-load di PHP CLI murni aaPanel. **Solusi:** Menggunakan flag abaikan persyaratan platform (`composer install --optimize-autoloader --no-dev --ignore-platform-req=ext-fileinfo --ignore-platform-req=composer-runtime-api`).
- **`proc_open()` Disabled:** Perintah Artisan post-autoload membutuhkan `proc_open`. **Solusi:** Menghapus `proc_open` dari `disable_functions` di `php.ini` sehingga `php artisan package:discover` berjalan lancar.

### 3. Tahap Setup Database & Optimasi Cache Artisan
- Migrasi database dijalankan dengan sukses melalui perintah `php artisan migrate:fresh --seed` yang membentuk **20 tabel utama** beserta akun administrator default.
- Seluruh cache aplikasi dibangun (`config:cache`, `route:cache`, `view:cache`) dan hak akses storage/cache disesuaikan ke user web (`chmod -R 775 storage bootstrap/cache && chown -R www:www storage bootstrap/cache`).

### 4. Tahap Debugging Web Server (Nginx) & Routing Laravel
- **Mengatasi Bug Systemd aaPanel:** Ditemukan bahwa perintah `systemctl restart nginx` gagal dijalankan karena bug skrip init aaPanel yang mengembalikan exit-code 1 saat Nginx sudah running. **Solusi:** Disepakati aturan standar bahwa manajemen Nginx di server ini **wajib menggunakan perintah `nginx -s reload` atau `nginx -t`**.
- **Perbaikan Nginx 404 Not Found:** Halaman selain root (`/`) mengalami `404 Not Found` karena konfigurasi default vhost belum memiliki rewrite rule Laravel. **Solusi:** Menambahkan blok `try_files $uri $uri/ /index.php?$query_string;` dan `fastcgi_pass unix:/tmp/php-cgi-84.sock;` ke file `/www/server/panel/vhost/rewrite/kpriimigrasibogor.my.id.conf`.

### 5. Tahap Setting Domain DNS & SSL HTTPS
- **Aktivasi Domain:** Mengarahkan A-Record domain `kpriimigrasibogor.my.id` dan `www.kpriimigrasibogor.my.id` ke IP VPS (`103.247.11.194`) di manajemen DNS Rumahweb.
- **Troubleshooting Resolv.conf:** Saat verifikasi propagasi DNS di terminal VPS via `nslookup`, sempat terjadi error `parse of /etc/resolv.conf failed`. **Solusi:** Memperbaiki file `/etc/resolv.conf` dengan menambahkan nameserver publik (`nameserver 8.8.8.8` dan `nameserver 1.1.1.1`).
- **Aktivasi SSL Let's Encrypt:** Setelah DNS terpropagasi, sertifikat SSL diaktifkan melalui menu **Website -> SSL -> Let's Encrypt** di aaPanel. Status berubah menjadi `[Deployed]` dan `APP_URL` diperbarui ke HTTPS.

### 6. Tahap Setup Git Workflow (Sinkronisasi Lokal <-> Server)
Untuk mempermudah maintenance agar tidak perlu upload zip manual saat pembaruan kode:
- Repositori private GitHub dibuat dan dihubungkan.
- Di lokal laptop, file sensitif (`.env`, `.zip`, `.xlsx`, `read_excel.php`, `contexting_vps.md`) dimasukkan ke `.gitignore`.
- Di VPS, folder project diubah menjadi repositori Git (`git config --global --add safe.directory /www/wwwroot/kpriimigrasibogor.my.id`), dihubungkan menggunakan **Personal Access Token (PAT)** dari GitHub, dan disinkronisasikan menggunakan `git reset --hard origin/main`.

### 7. Tahap Perbaikan Bug Terakhir: Paspor Pagination 500 Error
- **Gejala:** Saat membuka halaman 2 pada tabel data pengiriman paspor, muncul `500 Server Error`.
- **Analisis Log:** `SQLSTATE[42000]: Syntax error or access violation: 1140 Mixing of GROUP columns with no GROUP columns is illegal if there is no GROUP BY clause`.
- **Akar Masalah:** Pada method `index()` di `PasporController.php`, query ringkasan (`$summary = (clone $filteredQuery)->selectRaw('COUNT(*) AS total_pengiriman, SUM(total_pembayaran_pemohon) AS grand_total')->first();`) meng-clone `$filteredQuery` yang sudah memuat `orderBy('tanggal_transaksi', 'desc')->orderBy('id', 'desc')`. MySQL strict mode menolak fungsi aggregate jika disertai `ORDER BY` tanpa `GROUP BY`.
- **Solusi Tepat:** Menambahkan method `->reorder()` untuk membersihkan klausa pengurutan sebelum menjalankan `selectRaw()`:
  ```php
  // Perbaikan di PasporController.php
  $summary = (clone $filteredQuery)->reorder()->selectRaw('
      COUNT(*)                    AS total_pengiriman,
      SUM(total_pembayaran_pemohon) AS grand_total
  ')->first();
  ```
- **Hasil:** Perbaikan di-push ke GitHub dan di-pull di VPS (`git pull origin main && php artisan view:cache`). Pagination berjalan sempurna 100%.

---

## III. REFERENSI CEPAT COMMAND DEBUGGING & MAINTENANCE (CHEAT SHEET)

> **Simpan panduan ini jika suatu hari Anda lupa konfigurasi atau ingin melakukan debugging di VPS.**

### 1. Informasi Folder & File Penting
| Keterangan | Path / Lokasi |
| :--- | :--- |
| **Root Folder Lokal (Mac)** | `/Users/dakim/Herd/portal-koperasi` |
| **Root Folder VPS (Laravel)** | `/www/wwwroot/kpriimigrasibogor.my.id` |
| **Public Web Root (Nginx)** | `/www/wwwroot/kpriimigrasibogor.my.id/public` |
| **File Config Environment** | `/www/wwwroot/kpriimigrasibogor.my.id/.env` |
| **File Config PHP 8.4** | `/www/server/php/84/etc/php.ini` |
| **Socket PHP-FPM 8.4** | `/tmp/php-cgi-84.sock` |
| **Config Vhost Nginx** | `/www/server/panel/vhost/nginx/kpriimigrasibogor.my.id.conf` |
| **Config Rewrite Rules Nginx** | `/www/server/panel/vhost/rewrite/kpriimigrasibogor.my.id.conf` |
| **Log Error Nginx** | `/www/wwwlogs/kpriimigrasibogor.my.id.error.log` |
| **Log Error Laravel** | `/www/wwwroot/kpriimigrasibogor.my.id/storage/logs/laravel.log` |

---

### 2. Command Manajemen Service (Nginx & PHP-FPM)
```bash
# -------------------------------------------------------------------------
# NGINX (PENTING: Gunakan perintah ini, JANGAN gunakan systemctl restart)
# -------------------------------------------------------------------------
# Test syntax config Nginx (cek apakah ada error penulisan sebelum reload)
nginx -t

# Reload konfigurasi Nginx tanpa downtime
nginx -s reload

# -------------------------------------------------------------------------
# PHP-FPM 8.4 (aaPanel Custom Service)
# -------------------------------------------------------------------------
# Restart service PHP-FPM 8.4 (setelah ubah php.ini atau troubleshooting memory)
service php-fpm-84 restart

# Cek status PHP-FPM
service php-fpm-84 status

# Cek apakah socket PHP 8.4 tersedia
ls -la /tmp/php-cgi-84.sock
```

---

### 3. Command Maintenance & Optimasi Laravel (Artisan)
Selalu jalankan perintah ini dari dalam root folder VPS (`cd /www/wwwroot/kpriimigrasibogor.my.id`):

```bash
# Masuk ke direktori project VPS
cd /www/wwwroot/kpriimigrasibogor.my.id

# -------------------------------------------------------------------------
# REBUILD CACHE (Wajib dijalankan setiap kali ada update file dari Git)
# -------------------------------------------------------------------------
php artisan config:cache
php artisan route:cache
php artisan view:cache

# -------------------------------------------------------------------------
# CLEAR SEMUA CACHE (Gunakan jika aplikasi mengalami perilaku aneh/stale)
# -------------------------------------------------------------------------
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# -------------------------------------------------------------------------
# PERMISSIONS & OWNERSHIP (Jika terjadi error Permission Denied / log tidak bisa ditulis)
# -------------------------------------------------------------------------
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache
```

---

### 4. Cara Melihat & Mendebug Error (Troubleshooting Log)
Jika website mengalami `500 Server Error` atau blank putih, lakukan langkah berikut untuk mendiagnosis:

```bash
cd /www/wwwroot/kpriimigrasibogor.my.id

# 1. Lihat 50 baris terakhir dari log error Laravel (paling akurat)
tail -n 50 storage/logs/laravel.log | grep -A 10 "production.ERROR"

# 2. Lihat log error Nginx (jika error dari level web server seperti 502 Bad Gateway)
tail -n 50 /www/wwwlogs/kpriimigrasibogor.my.id.error.log

# 3. Aktifkan Debug Mode Sementara (Untuk melihat error langsung di layar browser)
sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env
php artisan config:cache

# --> Buka browser, perhatikan pesan error di layar, lalu PERBAIKI.

# 4. JANGAN LUPA Matikan Kembali Debug Mode setelah selesai (demi keamanan)
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
php artisan config:cache
```

---

### 5. Command Alur Update Kode via Git (Sehari-hari)
Ketika Anda melakukan perbaikan atau penambahan fitur baru di laptop lokal:

**Di Terminal Laptop Lokal (Mac):**
```bash
cd /Users/dakim/Herd/portal-koperasi

# 1. Status cek
git status

# 2. Add dan Commit perubahan
git add .
git commit -m "feat: deskripsi perubahan atau perbaikan fitur"

# 3. Push ke GitHub
git push origin main
```

**Di Terminal VPS Server (via SSH):**
```bash
cd /www/wwwroot/kpriimigrasibogor.my.id

# 1. Tarik kode terbaru dari GitHub
git pull origin main

# 2. Rebuild cache agar perubahan langsung aktif
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. (OPSIONAL) Jika update memuat migration database baru:
php artisan migrate
```

---

## IV. PANDUAN TUTORIAL SETUP ULANG VPS DARI NOL (STEP-BY-STEP)

> **Tutorial ini adalah panduan lengkap jika sewaktu-waktu server harus di-reset atau Anda memindahkan aplikasi ke VPS aaPanel baru.**

### Langkah 1: Persiapan Server & PHP di aaPanel
1. Install **Nginx 1.30+**, **PHP 8.4**, dan **MySQL 8.0** via App Store di aaPanel.
2. Buka menu **App Store -> PHP-8.4 -> Setting -> Configuration File (`php.ini`)**.
3. Cari parameter `disable_functions`. **Hapus kata `putenv` dan `proc_open`** dari daftar tersebut agar Laravel dan Composer tidak terblokir.
4. Klik **Save** dan pilih **Restart** pada PHP 8.4.

### Langkah 2: Pembuatan Website & Konfigurasi Nginx
1. Buka menu **Website -> Add site**.
2. Masukkan nama domain: `kpriimigrasibogor.my.id` dan `www.kpriimigrasibogor.my.id`.
3. Pilih PHP version: **8.4**. Klik **Submit**.
4. Klik nama website `kpriimigrasibogor.my.id` di tabel website untuk membuka **Site Modification**:
   - Pilih menu **URL rewrite**.
   - Masukkan konfigurasi rewrite khusus Laravel berikut dan klik **Save**:
     ```nginx
     location / {
         try_files $uri $uri/ /index.php?$query_string;
     }

     location ~ \.php$ {
         fastcgi_pass unix:/tmp/php-cgi-84.sock;
         fastcgi_index index.php;
         include fastcgi_params;
         fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
         fastcgi_param PATH_INFO $fastcgi_path_info;
     }
     ```
   - Pilih menu **Site directory**. Pastikan **Running directory** diarahkan ke `/public`. Klik **Save**.

### Langkah 3: Deploy Kode via Git & Install Dependensi
Buka terminal SSH server dan jalankan:
```bash
# 1. Masuk ke direktori web
cd /www/wwwroot/kpriimigrasibogor.my.id

# 2. Daftarkan direktori sebagai aman di Git
git config --global --add safe.directory /www/wwwroot/kpriimigrasibogor.my.id

# 3. Inisialisasi git dan hubungkan ke repo (gunakan Personal Access Token GitHub)
git init
git remote add origin https://Dakim777:TOKEN_PAT_GITHUB@github.com/Dakim777/KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR.git
git fetch origin
git reset --hard origin/main

# 4. Install dependensi Composer (gunakan flag ignore agar tidak terhenti karena ext-fileinfo)
composer install --optimize-autoloader --no-dev \
  --ignore-platform-req=ext-fileinfo \
  --ignore-platform-req=composer-runtime-api
```

### Langkah 4: Konfigurasi Environment & Database
1. Buat database baru di aaPanel via menu **Databases -> Add database** (catat nama DB, user, dan password).
2. Di terminal server, buat atau sesuaikan file `.env`:
   ```bash
   cp .env.production .env
   nano .env
   ```
3. Sesuaikan isi `.env`:
   ```env
   APP_NAME=KPRI
   APP_ENV=production
   APP_KEY=
   APP_DEBUG=false
   APP_URL=https://kpriimigrasibogor.my.id

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database_anda
   DB_USERNAME=username_db_anda
   DB_PASSWORD=password_db_anda
   ```
4. Generate key dan jalankan migrasi database:
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```

### Langkah 5: Finalisasi Optimasi & Aktivasi SSL
1. Set permission dan build cache produksi:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www:www storage bootstrap/cache
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
2. Pastikan DNS A Record domain (`@` dan `www`) sudah diarahkan ke IP VPS (`103.247.11.194`) di panel Rumahweb.
3. Di aaPanel, klik website `kpriimigrasibogor.my.id` -> tab **SSL** -> pilih **Let's Encrypt** -> centang semua domain -> klik **Apply**.
4. Website siap digunakan 100%!

---
*Dokumentasi ini disusun oleh Tim Pengembang & AI Assistant Antigravity (Google DeepMind).*

---

## V. STRUKTUR FOLDER PROJECT (PANDUAN BIMBINGAN)

> Penjelasan singkat, padat, dan jelas setiap folder & file penting dalam project Laravel ini.

```
portal-koperasi/              ← Root folder project (lokal & VPS)
├── app/                      ← INTI LOGIKA APLIKASI (PHP)
│   ├── Http/
│   │   ├── Controllers/      ← "Otak" logika per fitur aplikasi
│   │   │   ├── AuthController.php           → Login & Logout
│   │   │   ├── DashboardController.php      → Halaman ringkasan data
│   │   │   ├── KantinController.php         → POS Kasir & transaksi penjualan
│   │   │   ├── KategoriKantinController.php → CRUD kategori produk kantin
│   │   │   ├── ProdukKantinController.php   → CRUD produk, barcode, harga jual
│   │   │   ├── PembelianController.php      → Stok masuk / restock barang
│   │   │   ├── PasporController.php         → Transaksi pengiriman paspor
│   │   │   ├── UserController.php           → Manajemen akun pengguna & role
│   │   │   ├── ActivityLogController.php    → Audit trail / log aktivitas
│   │   │   └── TrashController.php          → Tempat sampah & restore data
│   │   └── Middleware/       → Filter keamanan (cek login, cek role pengguna)
│   ├── Models/               ← Representasi tabel database ke dalam kode PHP
│   │   ├── User.php                 → Tabel pengguna & role (super_admin, kasir)
│   │   ├── TransaksiPaspor.php      → Transaksi paspor (+ enkripsi data PII sensitif)
│   │   ├── TransaksiKantin.php      → Transaksi penjualan kantin
│   │   ├── ProdukKantin.php         → Produk (nama, harga, barcode, stok)
│   │   ├── KategoriKantin.php       → Kategori produk kantin
│   │   ├── PembelianKantin.php      → Pembelian / restock stok masuk
│   │   ├── DetailKantin.php         → Detail item per transaksi penjualan
│   │   └── DetailPembelian.php      → Detail item per pembelian/restock
│   └── Exports/              ← Class untuk export data ke file Excel (.xlsx)
│       ├── LaporanPenjualanExport.php       → Laporan penjualan kantin (multi-sheet)
│       ├── LaporanPenjualanGlobalSheet.php  → Sheet ringkasan total penjualan
│       ├── LaporanPenjualanDetailSheet.php  → Sheet detail per transaksi
│       ├── LaporanPembelianExport.php       → Laporan pembelian / restock
│       ├── LaporanPasporExport.php          → Laporan pengiriman paspor (dasar)
│       └── LaporanPasporKompleksExport.php  → Laporan paspor lengkap (kompleks)
│
├── config/                   ← FILE KONFIGURASI FRAMEWORK
│   └── *.php                 → Setiap file mengatur 1 aspek (session, db, cache, dll.)
│
├── database/                 ← SEMUA YANG BERKAITAN DENGAN DATABASE
│   ├── migrations/           → 20 file blueprint skema tabel (dijalankan urut)
│   │   ├── ..._create_users_table.php                  → Tabel pengguna & role
│   │   ├── ..._create_paspor_table.php                 → Tabel transaksi paspor
│   │   ├── ..._create_produk_kantins_table.php         → Tabel produk kantin
│   │   ├── ..._create_kantin_transactions_table.php    → Tabel penjualan kantin
│   │   ├── ..._encrypt_pii_columns_...php              → Enkripsi kolom HP & alamat
│   │   ├── ..._create_kategori_kantins_table.php       → Tabel kategori produk
│   │   ├── ..._create_activity_log_table.php           → Tabel audit log aktivitas
│   │   ├── ..._add_deleted_at_to_models.php            → Aktifkan fitur Trash
│   │   ├── ..._create_pembelian_kantins_...php         → Tabel restock & barcode
│   │   ├── ..._restructure_transaksi_paspors_table.php → Restruktur tabel paspor
│   │   ├── ..._add_fifo_columns_to_kantin_tables.php   → Sistem stok FIFO kantin
│   │   └── ..._add_metode_bayar_...php                 → Kolom metode pembayaran
│   └── seeders/
│       └── DatabaseSeeder.php → Data awal: akun admin default & data contoh
│
├── public/                   ← FOLDER YANG DIBUKA KE INTERNET (Document Root Nginx)
│   └── index.php             → Pintu masuk utama semua request web dari browser
│
├── resources/                ← TAMPILAN & ASET FRONT-END
│   ├── views/                → Semua halaman tampilan (Blade Template HTML)
│   │   ├── layouts/          → Template induk (header, sidebar, footer bersama)
│   │   ├── components/       → Komponen reusable (barcode scanner, modal, dll.)
│   │   ├── auth/             → Halaman login
│   │   ├── dashboard/        → Halaman ringkasan data kantin & paspor
│   │   ├── kantin/           → Halaman POS, produk, kategori, laporan kantin
│   │   ├── paspor/           → Halaman transaksi, edit status, cetak resi paspor
│   │   └── admin/            → Manajemen user, log aktivitas, trash
│   ├── css/                  → File CSS custom (styling tambahan)
│   └── js/                   → File JavaScript custom (logika front-end)
│
├── routes/                   ← PETA SEMUA URL / ALAMAT HALAMAN WEB
│   └── web.php               → Mendefinisikan semua URL, controller yang
│                               menanganinya, & middleware keamanan (role-based)
│
├── storage/                  ← PENYIMPANAN FILE YANG DIGENERATE SISTEM
│   └── logs/laravel.log      → Log error Laravel (selalu cek ini saat debug!)
│
├── .env                      ← FILE RAHASIA KONFIGURASI ENVIRONMENT
│                               (DB credentials, APP_KEY — TIDAK di-commit ke Git!)
├── composer.json             ← Daftar library PHP yang digunakan
└── README.md                 ← Dokumentasi project ini (file yang sedang Anda baca)
```

### Alur Kerja Request (Dari Klik Browser Hingga Data Tampil):
```
Browser User (klik tombol / buka URL)
        │
        ▼
Nginx Web Server ──► Meneruskan ke public/index.php
        │
        ▼
routes/web.php ──► Mencocokkan URL → arahkan ke Controller yang tepat
        │
        ▼
Middleware ──► Cek: Sudah login? Role-nya punya izin akses halaman ini?
        │
        ▼
Controller ──► Menjalankan logika bisnis (hitung, validasi, proses)
        │
        ▼
Model ──► Membaca / Menyimpan data ke database MySQL
        │
        ▼
View (Blade) ──► Merender tampilan HTML final dengan data dari database
        │
        ▼
Respons HTML dikirim balik ke Browser User ✅
```

---

## VI. DIAGRAM ALUR SISTEM (FLOWCHART TERBARU — FOKUS PELACAKAN PASPOR)

```mermaid
flowchart TD
    %% ── STYLING ──────────────────────────────────────────
    classDef role       fill:#0D1F3C,color:#fff,stroke:#fff;
    classDef aksi       fill:#EEF2F7,color:#0D1F3C,stroke:#0D1F3C;
    classDef db         fill:#fff,stroke:#486581,stroke-width:2px;
    classDef warning    fill:#fee2e2,color:#991b1b,stroke:#ef4444;
    classDef start      fill:#d1fae5,color:#065f46,stroke:#059669;
    classDef security   fill:#fef08a,color:#854d0e,stroke:#ca8a04;
    classDef newfeature fill:#dbeafe,color:#1e3a8a,stroke:#3b82f6;
    classDef publicflow fill:#f0fdf4,color:#166534,stroke:#16a34a,stroke-width:2px;

    %% ── PUBLIC GATEWAY & TRACKING ────────────────────────
    subgraph Public_Tracking ["PELACAKAN PASPOR PUBLIK & CEK RESI EKSPEDISI"]
        P_START(["🌐 Buka Portal KPRI\nkpriimigrasibogor.my.id"]) --> P_HOME["Halaman Pelacakan Paspor Publik\n(Logo Ditjen Imigrasi & Informatika ITERA)"]
        P_HOME --> P_SEARCH["Input Nomor Resi / Nama / No Paspor"]
        P_SEARCH --> P_RESULT["Tampilkan Status Live Koperasi"]
        P_RESULT --> P_API{"Cek API Ekspedisi\n(BinderByte / POS)"}
        P_API -- "Data Ditemukan" --> P_MANIFEST["Riwayat Perjalanan Resi POS Indonesia"]
        P_API -- "Belum Terindeks / Timeout" --> P_FALLBACK["Graceful Fallback:\nBelum Terindeks di POS / Dalam Pengemasan"]
        P_HOME --> |"Klik Login Petugas"| B
    end

    %% ── ALUR UTAMA & AUTENTIKASI ─────────────────────────
    subgraph Autentikasi ["LOGIN & ROLE PENGGUNA"]
        B["Halaman Login Petugas\n(Multi-Device Support)"] --> C{"Cek Autentikasi?"}
        C -- "Gagal" --> B
        C -- "Berhasil" --> D{"Pemisahan Hak Akses\nRole-Based Access Control"}
    end

    %% ── MODUL SUPER ADMIN ────────────────────────────────
    subgraph Modul_Admin ["MODUL SUPER ADMIN"]
        D -- "super_admin" --> E["Dashboard Admin Paspor"]
        E --> E1["Kelola Akun Pengguna\n(Tambah/Edit/Hapus)"]
        E --> E2["Audit Trail\n(Log Aktivitas & Riwayat Perubahan)"]
        E --> E3["Soft Deletes (Trash)\nRiwayat Transaksi Tetap Terjaga"]
        E --> E4["Monitoring Omset & Statistik\nPengiriman Paspor"]
        
        class E role;
        class E1,E4 aksi;
        class E2,E3 security;
    end

    %% ── KONEKSI ROLE SUPER ADMIN KE MODUL PASPOR ─────────
    E -.-> |"Akses Penuh (Full Role)"| G

    %% ── MODUL PASPOR ─────────────────────────────────────
    subgraph Modul_Paspor ["MODUL PENGIRIMAN & PELACAKAN PASPOR"]
        D -- "kasir_paspor" --> G["Dashboard Paspor"]
        
        G --> G1["Pendaftaran Permohonan Paspor"]
        G1 --> G1_A["Input Dinamis:\nHandling Fee, Materai, SK"]
        G1_A --> G11["Enkripsi Data PII (AES-256)"]
        
        G --> G2["Daftar Riwayat Pengiriman"]
        G2 --> G2_A["Pencarian by Nama/Paspor/Resi"]
        G2 --> G21["Update Status Warna:\nBiru (Transaksi) → Kuning (Diproses) → Hijau (Selesai) / Merah (Batal)"]
        G2 --> G22["Edit Transaksi / Input Resi Baru"]
        G22 --> |"Webcam Scanner 1D & QR Code\n(Cepat & Akurat)"| G2_C["Input No Resi Otomatis (+ Suara Beep)"]
        G2 --> G2_B["Cetak Kwitansi / Struk Resi\n(Dilengkapi Barcode Unik & No Resi)"]
        
        G --> G_EXP["Export Laporan Paspor"]
        G_EXP --> G_EXP1["Laporan Dasar (Excel)"]
        G_EXP --> G_EXP2["Laporan Kompleks (Excel)\n(Data Lengkap + Rincian Biaya)"]
        
        class G role;
        class G1,G2,G21 aksi;
        class G11,G22 security;
        class G1_A,G2_A,G2_B,G2_C,G_EXP,G_EXP1,G_EXP2 newfeature;
    end

    %% ── DATABASE & CORE SYSTEM ───────────────────────────
    subgraph Core_Database ["DATABASE MYSQL & SISTEM INTI"]
        DB[("Database Utama Koperasi\n(Tabel Transaksi & Audit)")]
        
        E1 --> DB
        E3 --> DB
        G11 --> DB
        G21 --> DB
        
        DB --> |"Aksi CRUD"| E2
        DB --> |"Jadwal Cron Harian (07:00)"| Cron{"Cek Estimasi Paspor\n(paspor:check-estimasi)"}
        
        Cron -- "H-0 Selesai / Siap Kirim" --> NP["Notifikasi Lonceng Paspor & Sinkronisasi BinderByte"]
        Cron -- "Belum Jatuh Tempo / Selesai" --> NX(["Aman"])

        class DB db;
        class NP warning;
        class NX start;
        class Cron newfeature;
    end

    class P_START,NX start;
    class P_HOME,P_SEARCH,P_RESULT,P_API,P_MANIFEST,P_FALLBACK publicflow;
```
