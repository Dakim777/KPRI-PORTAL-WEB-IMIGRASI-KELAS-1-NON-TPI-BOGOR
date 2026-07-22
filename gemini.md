# 🤖 GEMINI AI CONTEXTING DOCUMENT — PORTAL KPRI IMIGRASI BOGOR (FOKUS PELACAKAN PASPOR & EKSPEDISI)
**Dokumen Konteks & Riwayat Lengkap untuk AI Assistant (Gemini / Claude / Copilot)**  
**Status Sistem Terkini:** ✅ **PRODUCTION — LIVE & FULLY OPERATIONAL (100% SIAP PAKAI - FOKUS PELACAKAN PASPOR)**  
**Tanggal Update Terakhir:** 22 Juli 2026 (Update Arsitektur Pelacakan Publik, Enkripsi PII, Scanner, WA Gateway & Global Buffer Animation)

---

## 📌 PANDUAN UTAMA UNTUK AI ASSISTANT (BACA SEBELUM BERTINDAK!)
Jika Anda (Gemini / AI Assistant) baru membaca dokumen ini atau melanjutkan sesi baru dengan developer, **PERHATIKAN ATURAN EMAS BERIKUT**:
1. **Fokus Project (Judul Skripsi & Arsitektur):** Website utama di `https://kpriimigrasibogor.my.id` (**portal-koperasi**) sekarang **SEPENUHNYA BERFOKUS PADA SISTEM PELACAKAN PENGIRIMAN PASPOR, RESI EKSPEDISI & AUDIT OPERASIONAL KOPERASI**. Modul Kantin telah dipisahkan ke web/project mandiri (`kantin_kpri`) sesuai arahan dosen pembimbing skripsi agar arsitektur sistem terfokus dan modular. **JANGAN PERNAH** mencari atau mereferensikan rute/model Kantin di repository ini karena sudah dihapus total.
2. **Sistem sudah berada di lingkungan PRODUCTION.** Website hidup di `https://kpriimigrasibogor.my.id` dan sedang digunakan oleh masyarakat pemohon paspor, kasir paspor, serta admin di kantor Imigrasi. **JANGAN PERNAH** menyarankan atau menjalankan perintah destruktif seperti `php artisan migrate:fresh` di VPS server!
3. **Ketahui Perbedaan Path Lokal dan Server:**
   - **Lokal Dev (Mac Laptop):** `/Users/dakim/Herd/portal-koperasi`
   - **Production Server (Ubuntu VPS):** `/www/wwwroot/kpriimigrasibogor.my.id`
4. **Pahami Quirks Server (aaPanel + PHP 8.4 + Nginx):**
   - Nginx di server ini **TIDAK BOLEH** direstart dengan `systemctl restart nginx` (karena bug init script aaPanel). Selalu gunakan `nginx -s reload` atau `nginx -t`.
   - Port SSH server adalah **`22` (Default)**.
   - PHP CLI di sistem adalah PHP 8.4, sedangkan PHP-FPM yang dikelola aaPanel menggunakan socket `/tmp/php-cgi-84.sock`.
   - **PENTING (Tombol ClearNow aaPanel):** Jika tombol *ClearNow / Clean Cache* di aaPanel ditekan, aaPanel terkadang menjalankan pembersihan dengan user `root`. Hal ini dapat menyebabkan folder `storage` atau `bootstrap/cache` berstatus `root:root` sehingga memicu *500 Internal Server Error* (Permission Denied). Jika ini terjadi, langsung jalankan `chmod -R 775 storage bootstrap/cache && chown -R www:www storage bootstrap/cache`.
5. **Alur Update Kode (Git Workflow):**
   - Perubahan dilakukan dan diuji di lokal -> `git add .` -> `git commit -m "..."` -> `git push origin main`.
   - Di VPS server: `git pull origin main` -> `php artisan config:cache` -> `php artisan route:cache` -> `php artisan view:cache` -> `chmod -R 775 storage bootstrap/cache && chown -R www:www storage bootstrap/cache` -> (jika ada migrasi baru: `php artisan migrate --force`).

---

## I. SPESIFIKASI & INFORMASI SISTEM PRODUCTION

| Parameter | Detail / Nilai |
| :--- | :--- |
| **Nama Project / Judul** | **Sistem Pelacakan Pengiriman Paspor & Resi — Portal KPRI Kantor Imigrasi Kelas I Non-TPI Bogor** |
| **URL Live Web (HTTPS)** | **`https://kpriimigrasibogor.my.id`** (Let's Encrypt SSL, auto-renew aktif sampai Oktober 2026) |
| **IP Server VPS** | `103.247.11.194` |
| **Port SSH VPS** | `22` (Default) |
| **Sistem Operasi Server** | Ubuntu 22.04 LTS (64-bit) |
| **Control Panel VPS** | aaPanel (Baota Linux Panel) v8.0.4 |
| **Web Server** | Nginx v1.30.3 |
| **Runtime PHP** | PHP 8.4.21 (aaPanel custom build) |
| **Database Engine** | MySQL 8.0 (`ONLY_FULL_GROUP_BY` strict mode diperhatikan pada query aggregate) |
| **Nama Database Live** | `sql_kpriimigrasibogor_my_id` |
| **Repositori GitHub (Private)** | `https://github.com/Dakim777/KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR` |
| **Domain Registrar / DNS** | Rumahweb (A Record `@` & `www` diarahkan ke `103.247.11.194`) |
| **Identitas & Branding Web** | Logo resmi **Ditjen Imigrasi** & atribusi pengembang **Developed by Informatika ITERA** |

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

## II. ARSITEKTUR & MODUL UTAMA APLIKASI (FOKUS PELACAKAN PASPOR)

Aplikasi dibangun menggunakan **Laravel 13** dengan arsitektur modular yang difokuskan pada 3 pilar utama:

### 1. Pelacakan Paspor Publik & Cek Resi Ekspedisi (`/` - Public Gateway)
- **Halaman Utama Publik Tanpa Login:** Ketika masyarakat membuka `https://kpriimigrasibogor.my.id`, mereka langsung disambut oleh halaman pelacakan paspor publik yang profesional (dilengkapi logo resmi **Ditjen Imigrasi** dan atribusi **Informatika ITERA**).
- **Multi-Parameter Tracking:** Masyarakat dapat melacak paspor cukup dengan memasukkan **Nomor Resi (misal: `POS...`)**, **Nama Pemegang Paspor**, atau **ID Transaksi**.
- **Integrasi Ekspedisi Real-time 9 Kurir (BinderByte API):** Sistem mendukung pengecekan tarif ongkir dan pelacakan resi (*live tracking*) untuk **9 ekspedisi besar** di Indonesia: **POS Indonesia, JNE Express, TIKI, SiCepat Ekspres, J&T Express, AnterAja, Lion Parcel, Ninja Xpress, dan ID Express**.
- **Multi-Courier Auto-Discovery & Graceful Fallback:** Sistem dilengkapi mekanisme pelacakan pintar. Jika nomor resi diinput dan tidak terdeteksi pada kurir awal pilihan, sistem akan secara otomatis memindai/mencari resi tersebut ke seluruh 8 kurir ekspedisi lainnya sebelum menampilkan pesan *Graceful Fallback Box* (*"Belum Terindeks di POS / Ekspedisi"*), sehingga sistem tidak pernah crash (bebas error 500/404).

### 2. Modul Paspor & Logistik Operasional (Petugas Kasir Paspor)
- **Pencatatan Permohonan Dinamis & Pilihan Ekspedisi Lengkap:** Memproses data pemohon paspor, daftar nama pemegang paspor, alamat tujuan, pemilihan **Ekspedisi Pilihan (`jenis_kurir`) dan Jenis Layanan (`layanan_kurir`)**, serta kalkulasi otomatis biaya (kurir + materai + surat kuasa + handling fee koperasi).
- **Keamanan Data Pribadi (PII Encryption AES-256):** Kolom sensitif seperti **Nomor HP Pemohon (`no_hp_pemohon`)** dan **Alamat Pengiriman (`alamat_pengiriman`)** disimpan dalam bentuk terenkripsi penuh di tingkat database menggunakan fitur `encrypted` cast Laravel (AES-256 via `APP_KEY`). Data pribadi dijamin aman sekalipun database dibuka mentah.
- **Input Resi Cepat via Webcam Barcode/QR Scanner:** Pada tabel riwayat pengiriman, tersedia fitur modal dengan kamera pemindai 1D Barcode & QR Code (`Html5QrcodeScanner`) yang dilengkapi suara notifikasi (*beep*) saat resi berhasil terdeteksi. Kasir dapat mengisi atau memperbarui nomor resi seketika tanpa ketik manual (bebas typo).
- **Manajemen Status Pengiriman Warna:** Status alur transaksi diatur rapi: `transaksi` (Biru) -> `diproses` (Kuning) -> `selesai` (Hijau) -> `batal` (Merah).
- **Pencetakan Resi & Struk Barcode:** Kasir dapat mencetak bukti resi pengiriman berformat thermal/A4 yang dilengkapi nomor resi unik dan barcode 1D/QR.
- **Export Laporan Paspor:** Export data transaksi ke Excel dalam 2 format: **Laporan Dasar** dan **Laporan Kompleks** (berisi rincian finansial lengkap + status PII yang di-decrypt secara aman saat export).

### 3. Modul Manajemen Pengguna, Audit Log, Trash & Global Buffer
- **Role-Based Access Control (RBAC):** Role pengguna saat ini terfokus menjadi `super_admin` dan `kasir_paspor` (role `kasir_kantin` telah dihapus seiring pemisahan modul).
- **Audit Trail (`spatie/laravel-activitylog`):** Mencatat setiap aktivitas operasional secara komprehensif (siapa yang mengubah status paspor, siapa yang membatalkan pengiriman, serta log nilai sebelum dan sesudah perubahan data).
- **Soft Deletes (Trash):** Semua transaksi paspor yang dihapus masuk ke tempat sampah sementara (`TrashController`) dengan opsi restore atau force delete bagi `super_admin`.
- **Global Loading Buffer Animation (`loading-buffer`):** Setiap aksi input, penyimpanan formulir, atau pencarian data (di halaman Admin, Kasir, Login, maupun Pelacakan Publik) diproteksi oleh **Global Glassmorphic Dual-Ring Orbital Loader (`window.KPRILoader`)**. Sistem secara otomatis menonaktifkan (*disable*) tombol submit agar tidak terjadi klik ganda (*double-submit*) oleh operator pada koneksi lambat.
- **Multi-Device Session Support:** Menggunakan konfigurasi cookie `same_site=lax` dan penyimpanan sesi terenkripsi sehingga kasir paspor dan admin dapat login bersamaan di perangkat/komputer berbeda tanpa saling menabrak atau ter-logout.

---

## III. KRONOLOGI LENGKAP PENGEMBANGAN DARI AWAL HINGGA PRODUCTION

### Fase 1 — Migrasi Awal dari Lokal ke VPS Server
- Aplikasi dikembangkan secara lokal di Mac (`/Users/dakim/Herd/portal-koperasi`).
- Pada awalnya, file project di-archive menjadi `.zip` dan di-upload ke direktori `/www/wwwroot/kpriimigrasibogor.my.id` via File Manager aaPanel.
- File `.env` produksi disiapkan dengan parameter `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL=https://kpriimigrasibogor.my.id`.

### Fase 2 — Debugging Ekosistem PHP & Composer (aaPanel Quirks)
Saat menjalankan instalasi dependensi (`composer install`) di terminal VPS, ditemukan 3 batasan keamanan default dari aaPanel yang berhasil diselesaikan:
1. **`putenv()` Disabled:** aaPanel memasukkan `putenv` ke daftar `disable_functions` di file `/www/server/php/84/etc/php.ini`, menyebabkan Composer fatal error.  
   👉 *Solusi:* Menghapus kata `putenv` dari `disable_functions` di `php.ini` dan merestart PHP-FPM (`service php-fpm-84 restart`).
2. **`ext-fileinfo` Missing di CLI:** Extension `fileinfo` dibutuhkan oleh paket `league/flysystem` (filesystem Laravel). Di aaPanel, extension ini aktif di PHP-FPM (web) tapi tidak terdeteksi di PHP CLI murni.  
   👉 *Solusi:* Menggunakan flag ignore saat instalasi Composer (`composer install --optimize-autoloader --no-dev --ignore-platform-req=ext-fileinfo --ignore-platform-req=composer-runtime-api`).
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
   👉 *Solusi:* Mengisi file `/www/server/panel/vhost/rewrite/kpriimigrasibogor.my.id.conf` dengan blok `try_files $uri $uri/ /index.php?$query_string;` dan `fastcgi_pass unix:/tmp/php-cgi-84.sock;`.

### Fase 5 — Konfigurasi DNS & SSL Let's Encrypt
- A Record untuk `@` (`kpriimigrasibogor.my.id`) dan `www` (`www.kpriimigrasibogor.my.id`) diarahkan ke IP `103.247.11.194` melalui panel DNS Management Rumahweb.
- Setelah verifikasi DNS berhasil (`nslookup kpriimigrasibogor.my.id`), sertifikat SSL diaktifkan melalui menu **Website -> SSL -> Let's Encrypt** di aaPanel. Status berubah menjadi `[Deployed]` dan `APP_URL` diperbarui ke HTTPS.

### Fase 6 — Setup Git Workflow & Sinkronisasi Repositori
Untuk meninggalkan metode manual upload `.zip`, disetup alur kerja Git yang rapi:
- Repositori private dibuat di GitHub (`https://github.com/Dakim777/KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR`).
- File sensitif/tidak perlu diabaikan di `.gitignore` (`.env`, `*.zip`, `*.xlsx`, `read_excel.php`, `contexting_vps.md`).
- Di terminal server, direktori web didaftarkan sebagai aman (`git config --global --add safe.directory /www/wwwroot/kpriimigrasibogor.my.id`), dihubungkan ke GitHub menggunakan **Personal Access Token (PAT)**, dan disinkronisasikan menggunakan `git reset --hard origin/main`.

### Fase 7 — Bug Fix Terakhir: Paspor Pagination 500 Server Error
- **Gejala:** Saat pengguna menekan tombol halaman 2 pada tabel pagination di modul Paspor, aplikasi mengalami `500 Internal Server Error`.
- **Analisis Log Laravel:** `SQLSTATE[42000]: Syntax error or access violation: 1140 Mixing of GROUP columns with no GROUP columns is illegal if there is no GROUP BY clause`.
- **Root Cause:** Pada `PasporController.php` method `index()`, query untuk mengambil ringkasan total (`$summary = (clone $filteredQuery)->selectRaw('COUNT(*) AS total_pengiriman, SUM(total_pembayaran_pemohon) AS grand_total')->first();`) meng-clone `$filteredQuery` yang di dalamnya sudah memiliki klausa `orderBy('tanggal_transaksi', 'desc')->orderBy('id', 'desc')`. Pada MySQL 8.0 dengan mode ketat `ONLY_FULL_GROUP_BY`, menjalankan fungsi aggregate (`COUNT/SUM`) bersamaan dengan `ORDER BY` tanpa `GROUP BY` akan ditolak oleh database.
- **Solusi Tepat:** Menambahkan method `->reorder()` untuk membersihkan klausa pengurutan sebelum menjalankan `selectRaw()`. Pagination berjalan sempurna 100%.

### Fase 8 — Koneksi Database Grafis via TablePlus Over SSH Tunnel
Untuk memudahkan pemantauan dan pengelolaan 20 tabel database secara manual oleh developer tanpa membuka port MySQL ke publik:
- Disetup koneksi **TablePlus Over SSH (SSH Tunneling)** dari Mac lokal langsung ke server.
- **Konfigurasi SSH Tunnel:** Host `103.247.11.194`, Port `22` (Default), User `root`.
- **Konfigurasi MySQL:** Host `127.0.0.1`, Port `3306`, User `sql_kpriimigrasibogor_my_id`, Password (dari aaPanel), Database `sql_kpriimigrasibogor_my_id`.

### Fase 9 — Pemisahan Modul Kantin & Pemfokusan Sistem Pelacakan Paspor (Arsitektur Skripsi)
Sesuai arahan dosen pembimbing skripsi agar sistem memiliki fokus yang tajam dan modularitas yang tinggi:
- **Modul Kantin Dipisahkan:** Sistem POS, inventaris barang, restock, serta pelaporan kantin telah dipisahkan dari `portal-koperasi` menjadi aplikasi web mandiri (`kantin_kpri`).
- **Pembersihan Total Repository Utama:** Seluruh file model, controller, eksport excel, blade views, dan rute yang berkaitan dengan Kantin (`KantinController`, `ProdukKantin`, `TransaksiKantin`, direktori `resources/views/kantin/`, serta rute `/kantin/*`) telah **dihapus seutuhnya** dari codebase ini.
- **Pembersihan Role & Dashboard:** Role `kasir_kantin` dihapus dari RBAC. Dashboard Super Admin dan sistem Trash kini dimodifikasi khusus untuk menampilkan statistik dan riwayat pelacakan paspor secara eksklusif.

### Fase 10 — Transformasi menjadi Portal Pelacakan Paspor Publik & Integrasi API Ekspedisi (BinderByte + Graceful Fallback)
- **Gerbang Utama (`/`):** Halaman awal web diubah menjadi portal pelacakan publik profesional dengan pencarian resi, nama, dan paspor tanpa login.
- **Integrasi BinderByte API & Graceful Fallback:** Menghubungkan pelacakan ke API eksternal POS Indonesia. Diberikan proteksi *Graceful Fallback* sehingga apabila paket resi baru diketik kasir dan belum masuk ke server POS Indonesia, web tetap menampilkan antarmuka ramah tanpa error 500.

### Fase 11 — Optimasi Keamanan Data PII (AES-256) & Input Cepat Barcode Scanner Webcam
- **Enkripsi PII:** Menerapkan `encrypted` cast Laravel pada model `TransaksiPaspor.php` untuk kolom `no_hp_pemohon` dan `alamat_pengiriman`.
- **Scanner Webcam Resi:** Menambahkan pemindai kamera live (`Html5QrcodeScanner`) pada modal edit resi agar kasir dapat memindai barcode/QR resi POS langsung dari kamera komputer dengan bunyi *beep* sukses.

### Fase 12 — Standardisasi UI/UX (Watermark Informatika ITERA & Ditjen Imigrasi) & Proteksi Global Loading Buffer
- **Identitas & Branding Formal:** Menyematkan lambang resmi **Ditjen Imigrasi** serta penulisan lengkap **Developed by Informatika ITERA** (beserta logonya) di footer halaman pelacakan publik, *sidebar* menu petugas, dan *badge* login.
- **Global Loading Buffer Component:** Membangun komponen global (`components/loading-buffer.blade.php`) berdesain *dual-ring orbital spinner* dengan *glassmorphism backdrop* yang otomatis menonaktifkan tombol submit saat proses simpan/lacak berlangsung untuk mencegah *double-click*.

### Fase 13 — Audit Keamanan & Legalitas WhatsApp Gateway (`@whiskeysockets/baileys`)
Melakukan audit dan kepastian hukum/keamanan terhadap modul notifikasi WhatsApp lokal (`wa-service`):
- **Legalitas & Status Resmi:** `wa-service` menggunakan library open-source `@whiskeysockets/baileys` yang bersifat *unofficial WhatsApp Web emulation*. Sangat legal dan lumrah digunakan untuk percakapan transaksional koperasi (mengirim struk/resi paspor).
- **Keamanan Data:** Berjalan secara *self-hosted* di dalam server VPS (`http://127.0.0.1:3001`), dilindungi enkripsi *End-to-End* (E2EE) WhatsApp, dan data HP di database terproteksi AES-256. Data tidak pernah bocor atau dikirim ke vendor pihak ketiga mana pun.

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
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache
```

### 2. Clear Semua Cache & Perbaikan Permission (Saat Terjadi Stale / 500 Error)
Jika terjadi *500 Internal Server Error* setelah menekan tombol *ClearNow* di aaPanel atau setelah menjalankan cache CLI:
```bash
cd /www/wwwroot/kpriimigrasibogor.my.id
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache
```

### 3. Manajemen Service Nginx & PHP-FPM 8.4
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

### 4. Manajemen Layanan WhatsApp Gateway Lokal (`wa-service`)
```bash
cd /Users/dakim/Herd/portal-koperasi/wa-service # (di lokal Mac)
# atau jika di VPS: cd /www/wwwroot/kpriimigrasibogor.my.id/wa-service

# Menjalankan service secara langsung (cek QR code di terminal)
node index.js
```

### 5. Cara Menganalisis Log Error Laravel & Nginx
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
*(Catatan: Bisa juga diterapkan hanya pada rute `/login`, `/admin/*`, `/paspor/*`, sementara rute publik pelacakan resi paspor (`/`) biarkan `allow all;`).*

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
