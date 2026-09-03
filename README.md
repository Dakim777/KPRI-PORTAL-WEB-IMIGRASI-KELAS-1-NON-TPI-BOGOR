# Passport Delivery Tracking System
### Portal Koperasi — Kantor Imigrasi Kelas I Non-TPI Bogor

A web-based passport shipment tracking and cooperative management system built with **Laravel 13**.  
The public can track their passport delivery in real-time, while internal officers manage shipments, print receipts, and send automated WhatsApp notifications — all in one platform.

> **Developed by:** Informatics Engineering — Institut Teknologi Sumatera (ITERA)  
> **Environment:** Production on Ubuntu 22.04 VPS · Nginx · MySQL 8.0

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 13 |
| Language | PHP 8.3+ |
| Database | MySQL 8.0 |
| Frontend | Blade + Vite |
| Notification Service | Node.js + @whiskeysockets/baileys |
| Courier Tracking API | BinderByte API |
| Production Web Server | Nginx 1.30.3 (Ubuntu 22.04 VPS) |
| Production Panel | aaPanel v8.0.4 |

---

## Requirements

Ensure the following are installed before proceeding:

| Tool | Min. Version | Notes |
|---|---|---|
| PHP | **8.3+** | Extensions needed: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `bcmath`, `xml`, `curl`, `zip` |
| Composer | 2.x | [getcomposer.org](https://getcomposer.org) |
| MySQL | 8.0+ | Via XAMPP, Laragon, Herd, or standalone |
| Node.js | 18.x LTS | [nodejs.org](https://nodejs.org) |
| NPM | 9.x+ | Included with Node.js |
| Git | Any | [git-scm.com](https://git-scm.com) |

**Recommended local environments:**

| OS | Recommended Tool | Download |
|---|---|---|
| **macOS** | Laravel Herd | [herd.laravel.com](https://herd.laravel.com) |
| **Windows** | Laragon | [laragon.org](https://laragon.org) |

---

## Installation — macOS (Laravel Herd)

```bash
# 1. Clone the repository
git clone https://github.com/Dakim777/KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR.git
cd KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR

# 2. Install PHP dependencies
composer install

# 3. Copy the environment template
cp .env.example .env

# 4. Generate application encryption key
php artisan key:generate

# 5. Open .env and configure your local database (see Environment section below)

# 6. Create the database in MySQL, then run migrations + seeders
php artisan migrate --seed

# 7. Link the public storage folder
php artisan storage:link

# 8. Install Node dependencies and build frontend assets
npm install && npm run build

# 9. Serve the application
php artisan serve
```

Open your browser at: **http://localhost:8000**

---

## Installation — Windows (Laragon)

```bash
# 1. Open Laragon Terminal (right-click tray icon → Terminal)

# 2. Navigate to Laragon's www directory
cd C:\laragon\www

# 3. Clone the repository
git clone https://github.com/Dakim777/KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR.git
cd KPRI-PORTAL-WEB-IMIGRASI-KELAS-1-NON-TPI-BOGOR

# 4. Install PHP dependencies
composer install

# 5. Copy the environment template
copy .env.example .env

# 6. Generate application encryption key
php artisan key:generate

# 7. Open .env and configure your local database (see Environment section below)

# 8. Create the database in MySQL (via HeidiSQL / phpMyAdmin / Laragon UI)
#    then run migrations + seeders
php artisan migrate --seed

# 9. Link the public storage folder
php artisan storage:link

# 10. Install Node dependencies and build frontend assets
npm install && npm run build
```

Laragon auto-generates a virtual host. Access the app at:  
**http://kpri-portal-web-imigrasi-kelas-1-non-tpi-bogor.test**

---

## Environment Configuration

After copying `.env.example` to `.env`, update the following variables:

```env
# ─── Application ───────────────────────────────────────────
APP_NAME="Portal Koperasi Imigrasi Bogor"
APP_ENV=local
APP_KEY=                         # Auto-generated: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000    # Adjust to your local URL

# ─── Database (MySQL) ───────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=portal_koperasi      # Create this DB in MySQL first
DB_USERNAME=root
DB_PASSWORD=                     # Leave empty if no password (Herd/Laragon default)

# ─── Courier Tracking API ───────────────────────────────────
# Required for real-time shipment tracking. Register at: https://binderbyte.com
BINDERBYTE_API_KEY=              # REQUIRED

# ─── WhatsApp Gateway (Local Node.js Service) ───────────────
# Only needed if you run the wa-service locally (see WhatsApp section below)
WA_GATEWAY_URL=http://127.0.0.1:3001

# ─── Session ────────────────────────────────────────────────
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_SAME_SITE=lax
```

---

## Default Login Credentials

After running `php artisan migrate --seed`:

| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@koperasi.com` | `password` |
| Kasir Paspor | `paspor@koperasi.com` | `password` |

> ⚠️ Change these credentials immediately in any non-local environment.

---

## WhatsApp Notification Gateway (Optional)

The WhatsApp notification feature uses a local Node.js service built on [@whiskeysockets/baileys](https://github.com/WhiskeySockets/Baileys).

```bash
# Navigate to the wa-service directory
cd wa-service

# Install Node.js dependencies (first time only)
npm install

# Start the WhatsApp gateway service
npm start
```

Once running:
1. Log in as **Kasir Paspor** or **Super Admin**
2. Go to **WhatsApp Gateway** in the sidebar
3. Scan the displayed QR Code with your WhatsApp mobile app

The gateway runs locally on port `3001` and is accessed only by the Laravel application internally.

---

## Project Structure

```
├── app/
│   ├── Casts/           # Custom Eloquent casts (AES-256 PII encryption)
│   ├── Console/         # Artisan scheduled commands
│   ├── Exports/         # Excel report export classes
│   ├── Http/
│   │   ├── Controllers/ # Request handlers (Public, Paspor, Admin, WA)
│   │   └── Middleware/  # Role-based access control guards
│   ├── Models/          # Eloquent ORM models
│   ├── Notifications/   # Laravel notification classes
│   └── Services/        # Business logic (LogistikService, WhatsappService)
├── database/
│   ├── migrations/      # Database schema definitions
│   └── seeders/         # Default account seeder
├── diagrams/            # System design diagrams (.drawio files)
├── public/              # Web root — images, compiled assets
├── resources/
│   └── views/           # Blade templates (public, paspor, admin, components)
├── routes/
│   └── web.php          # All application routes
├── wa-service/          # Node.js WhatsApp Gateway (Baileys)
│   ├── index.js
│   └── package.json
├── .env.example         # Environment variable template
└── README.md
```

---

## Key Features

- 🔍 **Public Passport Tracker** — Search by Receipt Number or Transaction ID without login
- 🚚 **Multi-Courier Real-Time Tracking** — Integrated with BinderByte API (9 couriers: POS, JNE, TIKI, SiCepat, J&T, AnterAja, Lion Parcel, Ninja Xpress, ID Express)
- 🧾 **Kasir Dashboard** — Full shipment lifecycle management, barcode/QR webcam scanner, receipt printing
- 📲 **Automated WhatsApp Notifications** — Status update alerts sent to applicants via local Node.js gateway
- 🔐 **AES-256 Data Encryption** — Phone numbers and delivery addresses encrypted at rest
- 📋 **Audit Trail** — Full activity log via `spatie/laravel-activitylog`
- 🗑️ **Soft Delete / Trash** — Recoverable deletion for all transaction records
- 📊 **Excel Export** — Detailed financial and shipment reports via `maatwebsite/excel`

---

## License

Developed as a Final Project (Skripsi) at **Institut Teknologi Sumatera (ITERA)**, Informatics Engineering Department, in collaboration with the Cooperative of Kantor Imigrasi Kelas I Non-TPI Bogor.

© 2026 — All rights reserved.
