# SILK-Swarakarna

**Sistem Informasi Layanan Klinik Swarakarna** — Aplikasi web berbasis PHP OOP untuk mendigitalisasi pencatatan rekam medis dan pendaftaran tes pendengaran di klinik THT (Telinga Hidung Tenggorokan) spesialis pendengaran dan keseimbangan.

## Tech Stack

| Layer        | Teknologi                            |
|-------------|---------------------------------------|
| Backend     | PHP 8.0+ (OOP, PDO, MySQLi)          |
| Database    | MySQL 5.7+ / MariaDB 10.3+           |
| Frontend    | Tailwind CSS (via CDN)                |
| Autoload    | Composer PSR-4 (`Silk\` → `src/`)    |

## Prerequisites

- PHP >= 8.0 (CLI + PDO MySQL extension)
- MySQL 5.7+ or MariaDB 10.3+
- Composer (latest)
- A web browser

## Setup

```bash
# 1. Clone project
git clone <repo-url> silk-swarakarna
cd silk-swarakarna

# 2. Install dependencies (generates vendor/ + autoloader)
composer install

# 3. Copy environment config and edit database credentials
cp .env.example .env
nano .env

# 4. Import database schema + seed data
mysql -u root -p < database/silk_swarakarna.sql

# 5. Start development server
composer serve
```

Buka `http://localhost:8000` di browser.

## Folder Structure

```
silk-swarakarna/
├── public/                          ← Document root
│   ├── index.php                    ← Front controller + router
│   ├── .htaccess                    ← URL rewrite
│   └── assets/
│       ├── css/
│       └── js/
├── src/                             ← OOP domain classes
│   ├── Database.php                 ← PDO singleton
│   ├── Pasien.php                   ← CRUD pasien
│   ├── Dokter.php                   ← CRUD dokter
│   ├── Layanan.php                  ← CRUD layanan
│   └── Pemeriksaan.php              ← Transaksi + JOIN + status
├── includes/
│   ├── bootstrap.php                ← Autoload + session + errors
│   └── config.php                   ← DB constants
├── views/
│   ├── layout/
│   │   ├── header.php               ← HTML boilerplate + navbar
│   │   └── footer.php               ← Footer
│   ├── dashboard.php
│   ├── pasien/
│   ├── dokter/
│   ├── layanan/
│   └── pemeriksaan/
├── database/
│   └── silk_swarakarna.sql          ← Schema + seed
├── .env                             ← GITIGNORED
├── .env.example
├── .gitignore
├── README.md
└── composer.json
```

## Roadmap

Fitur dikerjakan dalam issue tracker:
`.scratch/silk-swarakarna-uas/issues/`

1. **Issue #01-03** — Foundation scaffold (selesai)
2. **Issue #04** — Router + request dispatch
3. **Issue #05** — Class Database (PDO singleton)
4. **Issue #06-09** — CRUD Pasien (class + views)
5. **Issue #10-13** — CRUD Dokter (class + views)
6. **Issue #14-16** — CRUD Layanan (class + views)
7. **Issue #17-19** — Transaksi Pemeriksaan (class + views)
8. **Issue #20** — Dashboard widget
9. **Issue #21** — Final integration + testing

## Team

_— Tim SILK-Swarakarna —_
