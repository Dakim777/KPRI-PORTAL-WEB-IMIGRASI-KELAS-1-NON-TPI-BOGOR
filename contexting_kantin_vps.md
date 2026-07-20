# 🤖 DOKUMEN CONTEXTING DEPLOYMENT VPS: MODUL KANTIN POS & INVENTARIS
**Khusus untuk AI Assistant (Gemini / Claude / Antigravity) di Project `kantin_kpri`**  
**Target Subdomain:** `https://kantin.kpriimigrasibogor.my.id`  
**IP Server VPS (aaPanel):** `103.247.11.194` | **Port SSH:** `22` (Default)  
**Status Dokumen:** Panduan Eksekusi Deployment Production Subdomain (100% Siap Pakai)

---

## 📌 PANDUAN EKSEKUTIF UNTUK AI AGENT SEBELAH (BACA SEBELUM MEMBIMBING DEVELOPER)
Jika Anda (AI Assistant) berada di window/tab project `kantin_kpri` dan membaca dokumen ini, tugas utama Anda adalah **membimbing developer melakukan deployment aplikasi baru kasir kantin ini ke dalam VPS aaPanel yang sama dengan web utama paspor (`kpriimigrasibogor.my.id`)**.

Karena kita menumpang di VPS aaPanel yang sudah beroperasi stabil, Anda **WAJIB MEMAHAMI SPESIFIKASI & QUIRKS SERVER** berikut agar proses deployment berjalan mulus tanpa error 500 atau kendala dependensi:

### ⚠️ QUIRKS SERVER VPS AAPANEL YANG WAJIB DIKETAHUI AGENT:
1. **Nginx Reload Bug:** **JANGAN PERNAH** menjalankan `systemctl restart nginx` karena akan menghasilkan error `Control process exited with error code` (bug init script aaPanel). Untuk merestart/reload web server, **SELALU GUNAKAN:**
   `nginx -s reload` atau `nginx -t`
2. **Composer `ext-fileinfo` CLI Missing:** Di aaPanel, extension `fileinfo` aktif di PHP-FPM (web) tetapi tidak terdeteksi di PHP CLI murni terminal. Saat menjalankan `composer install` di terminal VPS, **WAJIB GUNAKAN FLAG IGNORE:**
   `composer install --optimize-autoloader --no-dev --ignore-platform-req=ext-fileinfo --ignore-platform-req=composer-runtime-api`
3. **PHP Runtime & Socket:** PHP yang digunakan adalah **PHP 8.4** (`/www/server/php/84/etc/php.ini`). Socket PHP-FPM berada di `/tmp/php-cgi-84.sock`. Fungsi `putenv()` dan `proc_open()` sudah diaktifkan sebelumnya sehingga Laravel Artisan tidak akan terkendala.

---

## I. CHECKLIST & INSTRUKSI DEPLOYMENT (ARAHKAN KE DEVELOPER STEP-BY-STEP)

Berikut adalah 7 langkah urut yang harus Anda jelaskan dan tuntun kepada developer untuk mengonfigurasi VPS dari nol sampai web kantin online HTTPS:

### STEP 1: Setting DNS A Record di Rumahweb (Domain Management)
Minta developer membuka panel management domain di Rumahweb (`kpriimigrasibogor.my.id`), lalu tambahkan 1 baris record DNS baru:
- **Subdomain / Host:** `kantin`
- **Record Type:** `A` (A Record)
- **IP Address / Tujuan:** `103.247.11.194` *(IP VPS aaPanel)*
*(Tunggu sekitar 5–15 menit hingga propagasi DNS aktif, bisa dicek dengan `nslookup kantin.kpriimigrasibogor.my.id`).*

---

### STEP 2: Add Website & Database Baru di aaPanel (`http://103.247.11.194:888`)
Minta developer masuk ke browser aaPanel dan lakukan konfigurasi berikut:
1. Buka menu kiri **`Website`** -> klik tombol hijau **`Add site`**.
2. **Domain:** Isi dengan `kantin.kpriimigrasibogor.my.id`.
3. **Database:** Pilih **MySQL** -> sistem akan otomatis mengusulkan nama database & username (misal: `sql_kantin_kpri`). *Catat Username & Password database yang muncul!*
4. **PHP Version:** Pilih **`PHP-84`**.
5. Klik **Submit**.
*(Setelah Submit, aaPanel otomatis membuat folder kosong di `/www/wwwroot/kantin.kpriimigrasibogor.my.id`).*

---

### STEP 3: Setup URL Rewrite Nginx (Laravel Rule)
Di aaPanel, website baru secara default belum memiliki rewrite rule Laravel sehingga rute selain `/` akan menghasilkan `404 Not Found`.
1. Di menu **Website** aaPanel, klik nama domain `kantin.kpriimigrasibogor.my.id`.
2. Di jendela pop-up, pilih menu sebelah kiri **`URL rewrite`**.
3. Pada dropdown template, pilih **`Laravel`**, atau copas manual kode berikut:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```
4. Klik **Save**.

---

### STEP 4: Push Kode ke GitHub & Tarik ke VPS (Git Clone / Fetch)
Minta developer membuat repositori private baru di GitHub khusus web kantin (misal: `https://github.com/Dakim777/KPRI-PORTAL-KANTIN-POS`), lalu push seluruh kode lokal project `kantin_kpri` ke sana.

Setelah itu, arahkan developer membuka **Terminal VPS over SSH** (`ssh root@103.247.11.194 -p 22`) dan ketik perintah berikut:
```bash
# 1. Masuk ke direktori web kantin di server
cd /www/wwwroot/kantin.kpriimigrasibogor.my.id

# 2. Hapus file default bawaan aaPanel (seperti index.html / 404.html)
rm -rf * .htaccess .user.ini

# 3. Inisialisasi Git & hubungkan ke GitHub Repo Kantin
git init
git remote add origin https://github.com/Dakim777/KPRI-PORTAL-KANTIN-POS.git
git fetch --all
git reset --hard origin/main

# 4. Daftarkan folder sebagai safe directory di git
git config --global --add safe.directory /www/wwwroot/kantin.kpriimigrasibogor.my.id
```

---

### STEP 5: Konfigurasi File `.env` & Install Dependensi PHP
Masih di terminal VPS dalam folder `/www/wwwroot/kantin.kpriimigrasibogor.my.id`, buat file `.env` produksi:
```bash
cp .env.example .env
nano .env
```
Sesuaikan isi `.env` dengan kredensial database dari Langkah 2:
```env
APP_NAME="Kantin POS KPRI"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://kantin.kpriimigrasibogor.my.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sql_kantin_kpri
DB_USERNAME=sql_kantin_kpri
DB_PASSWORD=password_db_kantin_dari_aapanel
```
*(Simpan di nano: tekan `Ctrl+O` -> `Enter` -> `Ctrl+X`).*

Lalu jalankan instalasi Composer dan migrasi database:
```bash
# Install vendor tanpa dev dependencies & abaikan check fileinfo CLI
composer install --optimize-autoloader --no-dev --ignore-platform-req=ext-fileinfo --ignore-platform-req=composer-runtime-api

# Generate APP_KEY baru
php artisan key:generate

# Jalankan migrasi & seeder tabel kantin
php artisan migrate --force --seed
```

---

### STEP 6: Rebuild Cache & Set Permission Storage (Sangat Penting!)
Agar aplikasi berjalan dengan performa maksimal dan tidak terjadi error `500 Permission Denied` saat upload gambar atau menulis log:
```bash
cd /www/wwwroot/kantin.kpriimigrasibogor.my.id

# 1. Rebuild semua cache production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Set kepemilikan folder ke user web Nginx (www)
chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 3. Reload Nginx
nginx -s reload
```

---

### STEP 7: Aktivasi SSL Let's Encrypt Gratis di aaPanel
Setelah DNS `kantin.kpriimigrasibogor.my.id` sudah benar-benar mengarah ke IP `103.247.11.194`:
1. Buka kembali aaPanel -> klik nama website `kantin.kpriimigrasibogor.my.id`.
2. Pilih tab menu kiri **`SSL`**.
3. Pilih tab atas **`Let's Encrypt`**.
4. Centang domain `kantin.kpriimigrasibogor.my.id` -> klik tombol hijau **`Apply`**.
5. Setelah sukses, centang/aktifkan toggle **`Force HTTPS`** di pojok kanan atas.

**🎉 SELESAI! Web Kasir Kantin POS kini resmi LIVE di `https://kantin.kpriimigrasibogor.my.id`!**

---

## II. KONEKSI TABLEPLUS OVER SSH (KHUSUS DB KANTIN)
Untuk memantau 7 tabel database kantin secara langsung dari aplikasi TablePlus di Mac laptop developer:
- Buat koneksi baru di TablePlus -> centang **`Over SSH`**.
- **Bagian Atas (MySQL):** Host `127.0.0.1`, Port `3306`, User `sql_kantin_kpri`, Password *(password dari aaPanel)*, Database `sql_kantin_kpri`.
- **Bagian Bawah (SSH Tunnel):** Server `103.247.11.194`, Port `22` (Default), User `root`, Password *(password SSH root VPS)*.
- Klik **Test** -> jika hijau **OK**, klik **Connect**!

---
*Dokumen ini disusun untuk memudahkan sinkronisasi dan deployment antara agen AI di kedua project terpisah dalam ekosistem KPRI Imigrasi Bogor.*
