# Laporan Status Deployment VPS — KPRI Imigrasi Bogor
**Tanggal Update:** 17 Juli 2026  
**Status:** ✅ PRODUCTION — Live & Fully Operational

> 🌐 **Web:** `https://kpriimigrasibogor.my.id` — AKTIF  
> 🔒 **SSL:** Let's Encrypt, auto-renew  
> 📦 **Git:** GitHub → VPS workflow aktif  
> 🐛 **Bug terakhir fixed:** Pagination paspor 500 error (MySQL GROUP BY)

---

## 🖥️ Informasi Server

| Item | Detail |
|------|--------|
| **IP VPS** | `103.247.11.194` |
| **OS** | Ubuntu 22.04 (aaPanel) |
| **PHP** | 8.4 (aaPanel custom build) |
| **Web Server** | Nginx (dikelola aaPanel) |
| **Database** | MySQL (via aaPanel) |
| **Panel** | aaPanel (Baota) |
| **App directory** | `/www/wwwroot/kpriimigrasibogor.my.id` |
| **Public root** | `/www/wwwroot/kpriimigrasibogor.my.id/public` |
| **Domain** | `kpriimigrasibogor.my.id` |

---

## ✅ Yang Sudah Selesai

| Step | Status |
|------|--------|
| Upload file aplikasi ke VPS | ✅ DONE |
| Konfigurasi `.env` di server | ✅ DONE |
| `composer install` | ✅ DONE (dengan `--ignore-platform-req=ext-fileinfo --ignore-platform-req=composer-runtime-api`) |
| `php artisan package:discover` | ✅ DONE |
| `php artisan migrate:fresh --seed` | ✅ DONE (20 tabel berhasil) |
| `php artisan config:cache` | ✅ DONE |
| `php artisan route:cache` | ✅ DONE |
| `php artisan view:cache` | ✅ DONE |
| `chmod -R 775 storage bootstrap/cache` | ✅ DONE |
| `chown -R www:www storage bootstrap/cache` | ✅ DONE |
| PHP-FPM 8.4 berjalan | ✅ DONE (socket: `/tmp/php-cgi-84.sock`) |
| Nginx berjalan (via `nginx -s reload`) | ✅ DONE |
| Firewall port 80 & 443 | ✅ DONE (UFW allow) |
| Laravel app merespons | ✅ DONE (curl test 302 → /login) |

---

## ✅ Semua Masalah Sudah Selesai

| Masalah | Solusi |
|---------|--------|
| Composer error `putenv` | Hapus `putenv` dari `disable_functions` di php.ini |
| Composer error `ext-fileinfo` | Pakai flag `--ignore-platform-req=ext-fileinfo` |
| Composer error `proc_open` | Hapus `proc_open` dari `disable_functions` |
| Nginx 404 | Isi rewrite rules Laravel di `/www/server/panel/vhost/rewrite/kpriimigrasibogor.my.id.conf` |
| DNS tidak terkonfigurasi | Tambah A record di Rumahweb DNS Management |
| SSL belum aktif | Setup via aaPanel → Website → SSL → Let's Encrypt |
| **Pagination paspor 500 error** | **Fix: tambah `.reorder()` sebelum `.selectRaw()` di PasporController** |

---

## 🐛 Bug Fix: Paspor Pagination 500 Error

**File:** `app/Http/Controllers/PasporController.php` — method `index()`

**Error:** `SQLSTATE[42000]: Mixing of GROUP columns with no GROUP BY clause`

**Root cause:** Query aggregate `COUNT(*)/SUM()` mewarisi `ORDER BY` dari `buildQuery()`. MySQL strict mode (ONLY_FULL_GROUP_BY) menolak aggregate + ORDER BY tanpa GROUP BY.

**Fix yang diterapkan:**
```php
// SEBELUM (error):
$summary = (clone $filteredQuery)->selectRaw('...')->first();

// SESUDAH (fixed):
$summary = (clone $filteredQuery)->reorder()->selectRaw('...')->first();
```

`->reorder()` menghapus semua ORDER BY clause sebelum query aggregate dijalankan.

---

## 📦 Git Workflow (Sudah Setup)

**Repo GitHub:** `https://github.com/Dakim777/KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR`

### Alur Update Kode:

**Di laptop (setelah edit kode):**
```bash
git add .
git commit -m "deskripsi perubahan"
git push
```

**Di server (apply update):**
```bash
cd /www/wwwroot/kpriimigrasibogor.my.id
git pull origin main
php artisan config:cache
php artisan route:cache
php artisan view:cache
# Jika ada migration baru:
php artisan migrate
```

---


## 🎯 Tugas Agent Baru: Setup Domain & SSL

Web sudah jalan via IP. Sekarang perlu dikonfigurasi agar bisa diakses via domain `kpriimigrasibogor.my.id` dengan HTTPS.

### Step A — Setting DNS Domain

Masuk ke panel domain (Niagahoster / IDCloudHost / Cloudflare / dll), tambahkan:

| Type | Name | Value | TTL |
|------|------|-------|-----|
| A | `@` (atau `kpriimigrasibogor.my.id`) | `103.247.11.194` | 300 |
| A | `www` | `103.247.11.194` | 300 |

Setelah DNS propagate (5-60 menit), test dengan:
```bash
nslookup kpriimigrasibogor.my.id
# Harus muncul: Address: 103.247.11.194
```

### Step B — Setup SSL via aaPanel

1. Login aaPanel → **Website** → klik site `kpriimigrasibogor.my.id`
2. Pilih tab **SSL**
3. Pilih **Let's Encrypt**
4. Centang `kpriimigrasibogor.my.id` dan `www.kpriimigrasibogor.my.id`
5. Klik **Apply**

Jika DNS sudah propagate, SSL akan otomatis ter-issue.

### Step C — Update APP_URL di .env server

Setelah SSL aktif, update file `.env` di server:

```bash
nano /www/wwwroot/kpriimigrasibogor.my.id/.env
```

Ubah baris:
```
APP_URL=http://103.247.11.194
```
menjadi:
```
APP_URL=https://kpriimigrasibogor.my.id
```

Kemudian clear cache:
```bash
cd /www/wwwroot/kpriimigrasibogor.my.id
php artisan config:cache
php artisan route:cache
```

### Step D — Hapus IP dari server_name (opsional, setelah domain aktif)

```bash
nano /www/server/panel/vhost/nginx/kpriimigrasibogor.my.id.conf
```

Kembalikan baris server_name ke:
```nginx
server_name kpriimigrasibogor.my.id www.kpriimigrasibogor.my.id;
```

Lalu:
```bash
nginx -s reload
```

---

## 🔧 Yang Harus Dilakukan Agent Baru

Lihat section **"Tugas Agent Baru: Setup Domain & SSL"** di atas.

---

## ⚠️ Catatan Penting

### Quirks PHP di server ini

- **PHP CLI** yang dipakai: `/usr/bin/php` (PHP 8.4.21 system)
- **PHP-FPM** yang dipakai: `/www/server/php/84/` (aaPanel custom)
- **Socket PHP-FPM 8.4**: `/tmp/php-cgi-84.sock`
- **php.ini** path: `/www/server/php/84/etc/php.ini`
- **Restart PHP-FPM**: `service php-fpm-84 restart` (bukan systemctl!)

### Nginx management

- **Nginx tidak bisa di-restart via systemd** (bug skrip init aaPanel)
- Gunakan: `nginx -s reload` untuk reload config
- Gunakan: `nginx -t` untuk test config syntax
- Config vhost: `/www/server/panel/vhost/nginx/kpriimigrasibogor.my.id.conf`
- Config rewrite: `/www/server/panel/vhost/rewrite/kpriimigrasibogor.my.id.conf`
- Log error Nginx: `/www/wwwlogs/kpriimigrasibogor.my.id.error.log`

### `disable_functions` di php.ini

Sudah dihapus: `putenv`, `proc_open`  
Masih disabled: `passthru,exec,system,chroot,chgrp,chown,shell_exec,popen,pcntl_exec,...`

### Composer

- Composer versi 2.10.2 ✅
- Harus pakai flag: `--ignore-platform-req=ext-fileinfo --ignore-platform-req=composer-runtime-api`
- `ext-fileinfo` tidak tersedia di CLI tapi ada di FPM runtime

### Nginx service (systemd bug)

aaPanel Nginx menggunakan init script yang return exit-code 1 saat Nginx sudah running.  
Solusi: gunakan `nginx -s reload` / `nginx -s stop` langsung, **jangan** `systemctl restart nginx`.

### `.env` file di server

Sudah ada di `/www/wwwroot/kpriimigrasibogor.my.id/.env` dengan:
- `APP_KEY` sudah di-generate
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://kpriimigrasibogor.my.id`
- Database credentials sudah dikonfigurasi

---

## 📋 Setelah Web Bisa Dibuka

Setelah halaman login muncul, lanjutkan:

1. **Setup SSL** (HTTPS) via aaPanel → Website → SSL → Let's Encrypt
2. **Update APP_URL** di `.env` menjadi `https://kpriimigrasibogor.my.id` setelah SSL aktif
3. **Setting DNS** domain ke IP `103.247.11.194` (jika belum):
   - A record: `@` → `103.247.11.194`
   - A record: `www` → `103.247.11.194`

---

## 🔑 Kredensial Login Default (dari seeder)

Cek file seeder di:  
`/www/wwwroot/kpriimigrasibogor.my.id/database/seeders/`

Biasanya ada `UserSeeder.php` yang berisi email & password default admin.

---

## Project Info

- **Nama project:** Portal KPRI Imigrasi Bogor
- **Framework:** Laravel 13
- **Local dev path:** `/Users/dakim/Herd/portal-koperasi`
- **Repo/workspace:** `/Users/dakim/Herd/portal-koperasi`
