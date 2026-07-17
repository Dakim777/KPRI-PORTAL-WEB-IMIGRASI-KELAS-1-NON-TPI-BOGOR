# Laporan Status Deployment VPS — KPRI Imigrasi Bogor
**Tanggal:** 17 Juli 2026  
**Dikerjakan oleh:** Agent sebelumnya  
**Dilanjutkan oleh:** Agent baru

> ✅ **STATUS TERBARU:** Web sudah BISA DIAKSES via IP `http://103.247.11.194` dan login sudah berfungsi. Tugas agent baru adalah **mengatur nama domain** agar bisa diakses via `https://kpriimigrasibogor.my.id`.

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

## ✅ Masalah Sebelumnya (Sudah Selesai)

- Nginx 404 → **Fixed** dengan mengisi rewrite rules Laravel di `/www/server/panel/vhost/rewrite/kpriimigrasibogor.my.id.conf`
- Web sudah bisa diakses via `http://103.247.11.194`
- Login sudah berfungsi

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
