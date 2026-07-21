# 📋 LAPORAN LENGKAP & PANDUAN DEPLOYMENT PRODUCTION VPS
## Portal KPRI — Imigrasi Kelas 1 Non-TPI Bogor

**Tanggal Update Terakhir:** 17 Juli 2026  
**Status Sistem:** ✅ PRODUCTION — Live & Fully Operational (100% Siap Digunakan)  
**URL Live:** `https://kpriimigrasibogor.my.id`  
**Framework / Stack:** Laravel 13 | PHP 8.4 (aaPanel) | Nginx 1.30.3 | MySQL 8.0  
**Lokasi Project Lokal:** `/Users/dakim/Herd/portal-koperasi`  
**Lokasi Project Server:** `/www/wwwroot/kpriimigrasibogor.my.id`

---

## I. RINGKASAN STATUS SISTEM (100% READY)

Saat ini website Portal KPRI Imigrasi Bogor sudah **100% berjalan sempurna dan siap digunakan oleh pengguna (kasir maupun admin)** di berbagai perangkat komputer/laptop sekaligus.

### Bukti Pencapaian Akhir:
1. **Akses HTTPS Aktif:** Website dapat diakses secara aman via `https://kpriimigrasibogor.my.id` dengan sertifikat SSL Let's Encrypt (Auto-Renew aktif sampai Oktober 2026).
2. **Multi-Device Support:** Kasir dan admin dapat login bersamaan di komputer/device berbeda tanpa terjadi konflik sesi (`same_site=lax`, session storage aman).
3. **Semua Modul Berfungsi:**
   - Modul Kantin (POS, Kategori, Produk, Transaksi, Laporan Excel)
   - Modul Paspor (Transaksi, Edit Status, Struk Resi, Export Dasar & Kompleks)
   - Modul Admin Log & Trash
4. **Perbaikan Bug Pagination Paspor:** Bug `500 Server Error` saat membuka halaman 2 pada modul Paspor (disebabkan oleh strict mode MySQL `ONLY_FULL_GROUP_BY` pada fungsi aggregate `COUNT/SUM` yang mewarisi `ORDER BY`) telah **berhasil diselesaikan** menggunakan metode `->reorder()` sebelum query `selectRaw()`.
5. **Git Workflow Aktif:** Sinkronisasi kode antara laptop lokal dan VPS sudah terhubung via repositori private GitHub (`https://github.com/Dakim777/KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR`), mempermudah maintenance dan pembaruan kode di masa depan.

---

## II. KRONOLOGI & RIWAYAT PENGERJAAN DARI AWAL HINGGA PRODUCTION

### 1. Tahap Persiapan & Migrasi ke VPS
- **Upload Aplikasi:** File project Laravel diringkas menjadi arsip `.zip` di komputer lokal (`/Users/dakim/Herd/portal-koperasi`) dan di-upload ke direktori `/www/wwwroot/kpriimigrasibogor.my.id` melalui fitur File Manager aaPanel.
- **Konfigurasi Environment:** File `.env` dikonfigurasi dengan mode production (`APP_ENV=production`, `APP_DEBUG=false`), `APP_URL` diarahkan, serta kredensial database MySQL (`sql_kpriimigrasibogor_my_id`) dimasukkan dengan akurat.

### 2. Tahap Debugging Ekosistem PHP & Composer (aaPanel Quirks)
Dalam proses instalasi dependensi (`composer install`), ditemukan 3 batasan ketat khas aaPanel yang berhasil diatasi:
- **Masalah `putenv()` Disabled:** aaPanel secara default memasukkan `putenv` ke dalam `disable_functions` di file konfigurasi `/www/server/php/84/etc/php.ini`, menyebabkan Composer gagal berjalan. **Solusi:** Menghapus `putenv` dari daftar `disable_functions` dan merestart service PHP-FPM 8.4 (`service php-fpm-84 restart`).
- **Masalah `ext-fileinfo` Missing di CLI:** Extension `fileinfo` dibutuhkan oleh paket `league/flysystem`, namun tidak ter-load di PHP CLI murni aaPanel meski tersedia di FPM runtime. **Solusi:** Menjalankan Composer dengan flag abaikan persyarat platform (`composer install --optimize-autoloader --no-dev --ignore-platform-req=ext-fileinfo --ignore-platform-req=composer-runtime-api`).
- **Masalah `proc_open()` Disabled:** Perintah Artisan dan script post-autoload membutuhkan `proc_open`. **Solusi:** Menghapus `proc_open` dari `disable_functions` di `php.ini` sehingga `php artisan package:discover` dapat berjalan mulus.

### 3. Tahap Setup Database & Optimasi Artisan
- Setelah environment siap, migrasi database dan penyemaian data awal dijalankan dengan sukses melalui perintah `php artisan migrate:fresh --seed` yang membentuk **20 tabel utama** beserta akun administrator default.
- Seluruh cache aplikasi dibangun (`config:cache`, `route:cache`, `view:cache`) serta hak akses direktori storage dan cache disesuaikan ke user web (`chmod -R 775 storage bootstrap/cache && chown -R www:www storage bootstrap/cache`).

### 4. Tahap Debugging Web Server (Nginx) & Routing Laravel
- **Mengatasi Bug Systemd aaPanel:** Ditemukan bahwa perintah `systemctl restart nginx` gagal dijalankan karena bug pada skrip init aaPanel yang mengembalikan exit-code 1 saat Nginx sudah running. **Solusi:** Disepakati aturan standar bahwa manajemen Nginx di server ini **wajib menggunakan perintah `nginx -s reload` atau `nginx -t`**.
- **Perbaikan Nginx 404 Not Found:** Halaman selain rute root (`/`) mengalami `404 Not Found` karena konfigurasi default vhost belum memiliki rewrite rule Laravel. **Solusi:** Mengubah file `/www/server/panel/vhost/rewrite/kpriimigrasibogor.my.id.conf` dengan menambahkan blok `try_files $uri $uri/ /index.php?$query_string;` dan konfigurasi `fastcgi_pass unix:/tmp/php-cgi-84.sock;`.

### 5. Tahap Setting Domain DNS & SSL HTTPS
- **Aktivasi Domain:** Mengarahkan domain `kpriimigrasibogor.my.id` dan `www.kpriimigrasibogor.my.id` ke IP VPS (`103.247.11.194`) melalui manajemen A Record di Rumahweb.
- **Troubleshooting Resolv.conf:** Saat verifikasi propagasi DNS di terminal VPS via `nslookup`, sempat terjadi error `parse of /etc/resolv.conf failed`. **Solusi:** Memperbaiki file `/etc/resolv.conf` dengan menambahkan nameserver publik (`nameserver 8.8.8.8` dan `nameserver 1.1.1.1`).
- **Aktivasi SSL:** Setelah DNS berhasil terpropagasi (`nslookup` mengembalikan IP VPS), sertifikat SSL Let's Encrypt diaktifkan melalui menu **Website -> SSL -> Let's Encrypt** di aaPanel. `APP_URL` pada `.env` diperbarui menjadi `https://kpriimigrasibogor.my.id`.

### 6. Tahap Setup Git Workflow (Sinkronisasi Lokal <-> Server)
Untuk mempermudah maintenance agar tidak perlu upload file manual saat perbaruan kode:
- Repositori private GitHub dibuat dan dihubungkan.
- Di lokal laptop, file-file sensitif/tidak perlu (`.env`, `.zip`, `.xlsx`, `read_excel.php`, `contexting_vps.md`) dimasukkan ke `.gitignore` dan di-untrack.
- Di VPS, folder project diubah menjadi repositori Git (`git config --global --add safe.directory /www/wwwroot/kpriimigrasibogor.my.id`), dihubungkan menggunakan **Personal Access Token (PAT)** dari GitHub, dan disinkronisasikan menggunakan `git reset --hard origin/main`.

### 7. Tahap Perbaikan Bug Terakhir: Paspor Pagination 500 Error
- **Gejala:** Saat membuka halaman 2 (dan seterusnya) pada tabel data pengiriman paspor, muncul `500 Server Error`.
- **Analisis Log:** `SQLSTATE[42000]: Syntax error or access violation: 1140 Mixing of GROUP columns (MIN(),MAX(),COUNT(),...) with no GROUP columns is illegal if there is no GROUP BY clause`.
- **Akar Masalah:** Pada method `index()` di `PasporController.php`, query untuk menghitung ringkasan (`$summary = (clone $filteredQuery)->selectRaw('COUNT(*) AS total_pengiriman, SUM(total_pembayaran_pemohon) AS grand_total')->first();`) meng-clone `$filteredQuery` yang di dalamnya sudah memuat `orderBy('tanggal_transaksi', 'desc')->orderBy('id', 'desc')`. MySQL strict mode menolak fungsi aggregate jika disertai `ORDER BY` tanpa `GROUP BY`.
- **Solusi Tepat:** Menambahkan method `->reorder()` untuk membersihkan klausa pengurutan sebelum menjalankan `selectRaw()`:
  ```php
  // Perbaikan di PasporController.php
  $summary = (clone $filteredQuery)->reorder()->selectRaw('
      COUNT(*)                    AS total_pengiriman,
      SUM(total_pembayaran_pemohon) AS grand_total
  ')->first();
  ```
- **Hasil:** Perbaikan di-push dari lokal dan di-pull di VPS (`git pull origin main && php artisan view:cache`). Pagination berjalan lancar 100%.

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
*Laporan ini disusun secara komprehensif sebagai dokumentasi permanen sistem Portal KPRI Imigrasi Kelas 1 Non-TPI Bogor.*
