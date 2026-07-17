# 🤖 GEMINI AI CONTEXTING DOCUMENT — PORTAL KPRI IMIGRASI BOGOR
**Dokumen Konteks & Riwayat Lengkap untuk AI Assistant (Gemini / Claude / Copilot)**  
**Status Sistem Terkini:** ✅ **PRODUCTION — LIVE & FULLY OPERATIONAL (100% SIAP PAKAI)**  
**Tanggal Update Terakhir:** 18 Juli 2026

---

## 📌 PANDUAN UTAMA UNTUK AI ASSISTANT (BACA SEBELUM BERTINDAK!)
Jika Anda (Gemini / AI Assistant) baru membaca dokumen ini atau melanjutkan sesi baru dengan developer, **PERHATIKAN ATURAN EMAS BERIKUT**:
1. **Sistem sudah berada di lingkungan PRODUCTION.** Website hidup di `https://kpriimigrasibogor.my.id` dan sedang digunakan oleh kasir serta admin di kantor Imigrasi. **JANGAN PERNAH** menyarankan atau menjalankan perintah destruktif seperti `php artisan migrate:fresh` di VPS server!
2. **Ketahui Perbedaan Path Lokal dan Server:**
   - **Lokal Dev (Mac Laptop):** `/Users/dakim/Herd/portal-koperasi`
   - **Production Server (Ubuntu VPS):** `/www/wwwroot/kpriimigrasibogor.my.id`
3. **Pahami Quirks Server (aaPanel + PHP 8.4 + Nginx):**
   - Nginx di server ini **TIDAK BOLEH** direstart dengan `systemctl restart nginx` (karena bug init script aaPanel). Selalu gunakan `nginx -s reload` atau `nginx -t`.
   - Port SSH server adalah **`22` (Default)**.
   - PHP CLI di sistem adalah PHP 8.4, sedangkan PHP-FPM yang dikelola aaPanel menggunakan socket `/tmp/php-cgi-84.sock`.
4. **Alur Update Kode (Git Workflow):**
   - Perubahan dilakukan dan diuji di lokal -> `git add .` -> `git commit -m "..."` -> `git push origin main`.
   - Di VPS server: `git pull origin main` -> `php artisan config:cache` -> `php artisan route:cache` -> `php artisan view:cache` -> (jika ada migrasi baru: `php artisan migrate --force`).

---

## I. SPESIFIKASI & INFORMASI SISTEM PRODUCTION

| Parameter | Detail / Nilai |
| :--- | :--- |
| **Nama Project** | Portal KPRI Kantor Imigrasi Kelas I Non-TPI Bogor |
| **URL Live Web (HTTPS)** | **`https://kpriimigrasibogor.my.id`** (Let's Encrypt SSL, auto-renew aktif sampai Oktober 2026) |
| **IP Server VPS** | `103.247.11.194` |
| **Port SSH VPS** | `22` (Default) |
| **Sistem Operasi Server** | Ubuntu 22.04 LTS (64-bit) |
| **Control Panel VPS** | aaPanel (Baota Linux Panel) v8.0.4 |
| **Web Server** | Nginx v1.30.3 |
| **Runtime PHP** | PHP 8.4.21 (aaPanel custom build) |
| **Database Engine** | MySQL 8.0 |
| **Nama Database Live** | `sql_kpriimigrasibogor_my_id` |
| **Repositori GitHub (Private)** | `https://github.com/Dakim777/KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR` |
| **Domain Registrar / DNS** | Rumahweb (A Record `@` & `www` diarahkan ke `103.247.11.194`) |

### PATH LOKASI KRUSIAL DI SERVER VPS:
- **Root Project Laravel:** `/www/wwwroot/kpriimigrasibogor.my.id`
- **Document Root Nginx (Public):** `/www/wwwroot/kpriimigrasibogor.my.id/public`
- **File Environment (`.env`):** `/www/wwwroot/kpriimigrasibogor.my.id/.env`
- **Konfigurasi `php.ini` PHP 8.4:** `/www/server/php/84/etc/php.ini`
- **Socket PHP-FPM 8.4:** `/tmp/php-cgi-84.sock`
- **Konfigurasi Vhost Nginx:** `/www/server/panel/vhost/nginx/kpriimigrasibogor.my.id.conf`
- **Konfigurasi Rewrite Rules Nginx:** `/www/server/panel/vhost/rewrite/kpriimigrasibogor.my.id.conf`
- **Log Error Nginx:** `/www/wwwlogs/kpriimigrasibogor.my.id.error.log`
- **Log Error Laravel:** `/www/wwwroot/kpriimigrasibogor.my.id/storage/logs/laravel.log`

---

## II. ARSITEKTUR & MODUL UTAMA APLIKASI

Aplikasi dibangun menggunakan **Laravel 13** dengan arsitektur modular yang terdiri dari 3 pilar utama:

### 1. Modul Kantin (Point of Sales & Inventaris)
- **POS Kasir Cepat:** Dilengkapi integrasi pemindai barcode (`components/barcode-scanner`), penghitungan total otomatis, dan pencetakan struk resi kasir.
- **Manajemen Inventaris:** Kategori produk, katalog produk kantin, dan pencatatan transaksi pembelian kantin (restock barang masuk dengan sistem manajemen stok FIFO).
- **Laporan Keuangan Kantin:** Export data penjualan dan pendapatan dalam format Excel (Laporan Penjualan Global & Detail Sheet).

### 2. Modul Paspor (Layanan Pengiriman Paspor & Resi)
- **Pencatatan Permohonan:** Memproses data pemohon paspor, daftar nama pemegang paspor, alamat lengkap tujuan pengiriman, jenis kurir, serta kalkulasi otomatis biaya (kurir + materai + surat kuasa + handling fee koperasi).
- **Pelacakan & Status Pengiriman:** Alur status transaksi dari `transaksi` -> `diproses` -> `selesai` -> `batal`.
- **Keamanan Data Pribadi (PII Encryption):** Kolom sensitif seperti nomor HP pemohon dan alamat pengiriman dienkripsi di tingkat database (`encrypted:array` / `encrypted` cast di model `TransaksiPaspor.php`).
- **Export & Laporan Paspor:** Export data transaksi paspor ke Excel (Laporan Paspor Dasar & Laporan Paspor Kompleks).

### 3. Modul Manajemen Pengguna, Audit Log & Trash
- **Role-Based Access Control:** Role pengguna terbagi menjadi `super_admin`, `kasir_paspor`, dan role kantin untuk memisahkan wewenang.
- **Audit Trail (`spatie/laravel-activitylog`):** Mencatat setiap aktivitas operasional (siapa yang mengubah harga produk, siapa yang membatalkan pengiriman paspor, kapan dan apa nilai sebelum/sesudah diubah).
- **Soft Deletes (Trash):** Semua transaksi penting tidak langsung terhapus permanen melainkan masuk ke tempat sampah sementara (`TrashController`) dengan opsi restore atau force delete bagi `super_admin`.
- **Multi-Device Session Support:** Menggunakan konfigurasi cookie `same_site=lax` dan penyimpanan sesi terenkripsi sehingga kasir A dan kasir B dapat login bersamaan di perangkat/komputer berbeda tanpa saling menabrak atau ter-logout.

---

## III. KRONOLOGI LENGKAP PENGEMBANGAN DARI AWAL HINGGA PRODUCTION

### Fase 1 — Migrasi Awal dari Lokal ke VPS Server
- Aplikasi dikembangkan secara lokal di Mac (`/Users/dakim/Herd/portal-koperasi`).
- Pada awalnya, file project di-archive menjadi `.zip` dan di-upload ke direktori `/www/wwwroot/kpriimigrasibogor.my.id` via File Manager aaPanel.
- File `.env` produksi disiapkan:
  ```env
  APP_NAME=KPRI
  APP_ENV=production
  APP_KEY=base64:0gVWh5r6vwk4zJqXFPae8hd45m+aCXGCvtqXDnxxcaI=
  APP_DEBUG=false
  APP_URL=https://kpriimigrasibogor.my.id
  ```

### Fase 2 — Debugging Ekosistem PHP & Composer (aaPanel Quirks)
Saat menjalankan instalasi dependensi (`composer install`) di terminal VPS, ditemukan 3 batasan keamanan default dari aaPanel yang berhasil diselesaikan:
1. **`putenv()` Disabled:** aaPanel memasukkan `putenv` ke daftar `disable_functions` di file `/www/server/php/84/etc/php.ini`, menyebabkan Composer fatal error.  
   👉 *Solusi:* Menghapus kata `putenv` dari `disable_functions` di `php.ini` dan merestart PHP-FPM (`service php-fpm-84 restart`).
2. **`ext-fileinfo` Missing di CLI:** Extension `fileinfo` dibutuhkan oleh paket `league/flysystem` (filesystem Laravel). Di aaPanel, extension ini aktif di PHP-FPM (web) tapi tidak terdeteksi di PHP CLI murni.  
   👉 *Solusi:* Menggunakan flag ignore saat instalasi Composer:  
   `composer install --optimize-autoloader --no-dev --ignore-platform-req=ext-fileinfo --ignore-platform-req=composer-runtime-api`
3. **`proc_open()` Disabled:** Script post-autoload Laravel Artisan (`package:discover`) membutuhkan `proc_open`.  
   👉 *Solusi:* Menghapus `proc_open` dari `disable_functions` di `php.ini`, lalu menjalankan `php artisan package:discover` secara manual.

### Fase 3 — Setup Database & Cache Artisan
- Database MySQL bernama `sql_kpriimigrasibogor_my_id` dibuat via aaPanel.
- Dijalankan perintah migrasi awal beserta seeder: `php artisan migrate:fresh --seed`. Sebanyak **20 tabel utama** berhasil dibentuk dan akun administrator default dibuat.
- Seluruh cache konfigurasi, rute, dan view dibangun untuk performa produksi (`config:cache`, `route:cache`, `view:cache`).
- Kepemilikan direktori storage disesuaikan ke user web: `chmod -R 775 storage bootstrap/cache && chown -R www:www storage bootstrap/cache`.

### Fase 4 — Debugging Nginx (Systemd Bug & 404 Rewrite Rules)
1. **Bug Systemd aaPanel:** Ditemukan bahwa perintah `systemctl restart nginx` mengembalikan pesan error `Control process exited with error code`. Ini terjadi karena bug pada skrip init aaPanel yang mengembalikan exit-code 1 saat Nginx sudah running.  
   👉 *Aturan Permanen:* Untuk reload Nginx, **wajib menggunakan `nginx -s reload` atau `nginx -t`**.
2. **Nginx 404 Not Found:** Saat mencoba membuka halaman login atau sub-rute lainnya, Nginx mengembalikan `404 Not Found`. Hal ini karena vhost default belum memiliki rewrite rule Laravel.  
   👉 *Solusi:* Mengisi file `/www/server/panel/vhost/rewrite/kpriimigrasibogor.my.id.conf` dengan aturan berikut:
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

### Fase 5 — Konfigurasi DNS & SSL Let's Encrypt
- A Record untuk `@` (`kpriimigrasibogor.my.id`) dan `www` (`www.kpriimigrasibogor.my.id`) diarahkan ke IP `103.247.11.194` melalui panel DNS Management Rumahweb.
- Setelah verifikasi DNS berhasil (`nslookup kpriimigrasibogor.my.id`), sertifikat SSL diaktifkan melalui menu **Website -> SSL -> Let's Encrypt** di aaPanel. `APP_URL` di `.env` diperbarui menjadi `https://kpriimigrasibogor.my.id`.

### Fase 6 — Setup Git Workflow & Sinkronisasi Repositori
Untuk meninggalkan metode manual upload `.zip`, disetup alur kerja Git yang rapi:
- Repositori private dibuat di GitHub (`https://github.com/Dakim777/KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR`).
- File sensitif/tidak perlu diabaikan di `.gitignore` (`.env`, `*.zip`, `*.xlsx`, `read_excel.php`, `contexting_vps.md`).
- Di terminal server, direktori web didaftarkan sebagai aman (`git config --global --add safe.directory /www/wwwroot/kpriimigrasibogor.my.id`), dihubungkan ke GitHub menggunakan **Personal Access Token (PAT)**, dan disinkronisasikan (`git fetch origin && git reset --hard origin/main`).

### Fase 7 — Bug Fix Terakhir: Paspor Pagination 500 Server Error
- **Gejala:** Saat pengguna menekan tombol halaman 2 pada tabel pagination di modul Paspor, aplikasi mengalami `500 Internal Server Error`.
- **Analisis Log Laravel:** `SQLSTATE[42000]: Syntax error or access violation: 1140 Mixing of GROUP columns (MIN(),MAX(),COUNT(),...) with no GROUP columns is illegal if there is no GROUP BY clause`.
- **Root Cause:** Pada `PasporController.php` method `index()`, query untuk mengambil ringkasan total (`$summary = (clone $filteredQuery)->selectRaw('COUNT(*) AS total_pengiriman, SUM(total_pembayaran_pemohon) AS grand_total')->first();`) meng-clone `$filteredQuery` yang di dalamnya sudah memiliki klausa `orderBy('tanggal_transaksi', 'desc')->orderBy('id', 'desc')`. Pada MySQL 8.0 dengan mode ketat `ONLY_FULL_GROUP_BY`, menjalankan fungsi aggregate (`COUNT/SUM`) bersamaan dengan `ORDER BY` tanpa `GROUP BY` akan ditolak oleh database.
- **Solusi yang Diterapkan:** Menambahkan method `->reorder()` untuk menghapus semua klausa pengurutan (`ORDER BY`) pada clone query sebelum fungsi `selectRaw()` dijalankan:
  ```php
  // Perbaikan di PasporController.php (Sekarang 100% lancar):
  $summary = (clone $filteredQuery)->reorder()->selectRaw('
      COUNT(*)                    AS total_pengiriman,
      SUM(total_pembayaran_pemohon) AS grand_total
  ')->first();
  ```
- **Hasil:** Perbaikan di-commit dan di-push dari lokal (`docs: add comprehensive production deployment report...`), lalu di-pull di VPS (`git pull origin main && php artisan view:cache`). Pagination bekerja sempurna tanpa error.

### Fase 8 — Koneksi Database Grafis via TablePlus Over SSH Tunnel
Untuk memudahkan pemantauan dan pengelolaan 20 tabel database secara manual oleh developer tanpa membuka port MySQL ke publik:
- Disetup koneksi **TablePlus Over SSH (SSH Tunneling)** dari Mac lokal langsung ke server.
- **Konfigurasi SSH Tunnel:** Host `103.247.11.194`, Port `22` (Default), User `root`.
- **Konfigurasi MySQL:** Host `127.0.0.1`, Port `3306`, User `sql_kpriimigrasibogor_my_id`, Password (dari aaPanel), Database `sql_kpriimigrasibogor_my_id`.

---

## IV. CHEAT SHEET COMMAND & TROUBLESHOOTING UTAMA

> AI Assistant / Developer: Gunakan daftar perintah di bawah ini saat melakukan maintenance atau debugging di VPS server.

### 1. Rebuild Cache setelah Update Git (Wajib!)
```bash
cd /www/wwwroot/kpriimigrasibogor.my.id
git pull origin main
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Clear Semua Cache (Jika terjadi perilaku stale / aneh)
```bash
cd /www/wwwroot/kpriimigrasibogor.my.id
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 3. Perbaikan Ownership & Permission Folder Storage
```bash
cd /www/wwwroot/kpriimigrasibogor.my.id
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache
```

### 4. Manajemen Service Nginx & PHP-FPM 8.4
```bash
# Test konfigurasi Nginx
nginx -t

# Reload Nginx (TANPA downtime, GUNAKAN INI)
nginx -s reload

# Restart PHP-FPM 8.4 (setelah ubah php.ini)
service php-fpm-84 restart

# Cek socket PHP 8.4
ls -la /tmp/php-cgi-84.sock
```

### 5. Cara Menganalisis Log Error (Troubleshooting 500 Error)
```bash
cd /www/wwwroot/kpriimigrasibogor.my.id

# Lihat 50 baris terakhir log error Laravel (Paling akurat)
tail -n 50 storage/logs/laravel.log | grep -A 10 "production.ERROR"

# Lihat log error web server Nginx
tail -n 50 /www/wwwlogs/kpriimigrasibogor.my.id.error.log

# Aktifkan Debug Mode sementara di browser (INGAT MATIKAN SETELAHNYA!)
sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env
php artisan config:cache
# ... cek di browser ...
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
php artisan config:cache
```

---

## V. CATATAN PENGEMBANGAN MASA DEPAN (FUTURE ARCHITECTURE NOTES)

### 1. Rencana Pembatasan Akses Jaringan (IP Whitelisting / Zero Trust)
User sempat mendiskusikan keinginan agar di masa depan website ini **hanya bisa diakses oleh komputer di kantor atau perangkat tertentu**. Jika fitur ini diminta, AI Assistant dapat menyarankan/menerapkan salah satu dari 2 metode berikut:

#### Metode A: IP Whitelisting di Nginx (`kpriimigrasibogor.my.id.conf`)
Jika internet Kantor Imigrasi memiliki **IP Publik Statis** (contoh: `203.190.15.88`), cukup tambahkan direktif berikut di dalam blok `server { ... }` file konfigurasi vhost Nginx:
```nginx
# Izinkan IP Kantor Imigrasi Bogor & IP Server
allow 203.190.15.88;
allow 103.247.11.194;

# Tolak semua IP luar lainnya
deny all;
```
*(Catatan: Bisa juga diterapkan hanya pada rute `/login`, `/kantin/*`, `/paspor/*`, sementara rute publik pelacakan resi paspor biarkan `allow all;`).*

#### Metode B: Cloudflare Zero Trust (Cloudflare Access)
Jika IP kantor dinamis, cara terbaik adalah meletakkan domain `kpriimigrasibogor.my.id` di bawah Cloudflare dan mengaktifkan **Cloudflare Access**, sehingga kasir/admin harus memverifikasi email kantor atau OTP sebelum masuk ke aplikasi Laravel.

### 2. Aturan Menambah Migration Database Baru di Production
Jika fitur baru menambahkan tabel atau kolom baru di database:
1. Buat migration baru di lokal (`php artisan make:migration ...`).
2. Commit & push ke GitHub.
3. Di VPS server, setelah `git pull origin main`, jalankan:
   `php artisan migrate --force`  
   **⛔ HARAM HUKUMNYA MENJALANKAN `php artisan migrate:fresh` DI VPS KARENA AKAN MENGHAPUS SELURUH DATA TRANSAKSI ASLI!**

---
*Dokumen ini adalah acuan resmi sistem Portal KPRI Imigrasi Bogor untuk AI Assistant dan tim pengembang.*
