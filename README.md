# SILK-Swarakarna

Sistem Informasi Layanan Klinik Swarakarna. Aplikasi web PHP OOP untuk klinik THT. UAS Pemrograman Web, Pagi 01, Primakara University.

## Stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend | PHP OOP, PDO, no framework | 8.2 (DDEV) / 8.0+ minimum |
| Database | MariaDB (DDEV) / MySQL | 10.11 / 5.7+ |
| Web server | nginx-fpm (DDEV) / Apache | |
| Frontend | Bootstrap 5.3 + Bootstrap Icons via CDN | 5.3.3 / 1.11.3 |
| Autoload | Composer PSR-4 | 2.x |
| Testing | PHPUnit | 10.5 |
| Dev env | DDEV (Docker) | 1.25+ |

## Quick start

Pilih salah satu. DDEV paling cepat.

### Mode A: DDEV (Docker)

Prasyarat: Docker + DDEV.

```bash
git clone git@github.com:TudeOrangBiasa/silk-swarakarna-uas.git
cd silk-swarakarna-uas
ddev config --project-name=silk-swarakarna --docroot=public --php-version=8.2 --mariadb-version=10.11
ddev start
ddev composer install
ddev import-db --file=database/silk_swarakarna.sql
ddev launch
```

Bisa juga `ddev config` interaktif. URL default `https://silk-swarakarna.ddev.site/`.

Kredensial DB otomatis: `db`/`db` host `db` dalam container. Database `silk_swarakarna`. DDEV inject env lewat `web_environment`, tidak perlu edit `.env`.

### Mode B: baremetal (PHP lokal)

Prasyarat: PHP 8.0+ + `pdo_mysql`, Composer, MySQL 5.7+ / MariaDB 10.3+.

```bash
git clone git@github.com:TudeOrangBiasa/silk-swarakarna-uas.git
cd silk-swarakarna-uas
composer install
cp .env.example .env
# edit .env: DB_HOST, DB_NAME, DB_USER, DB_PASS
mysql -u root -p < database/silk_swarakarna.sql
php -S localhost:8000 -t public/
```

URL: `http://localhost:8000/`

### Mode C: shared hosting (cPanel / public_html)

Prasyarat: akun hosting PHP 8.0+ dan MySQL.

```bash
# 1. Lokal: build artifact
composer install --no-dev --optimize-autoloader

# 2. Upload via cPanel File Manager / FTP
#    Upload isi repo ke /home/user/silk-swarakarna/
#    Symlink public/ ke /home/user/public_html/
#    atau upload public/ ke public_html/ langsung

# 3. cPanel MySQL Databases
#    Buat database username_silk_swarakarna
#    Buat user + password, Grant ALL

# 4. cPanel phpMyAdmin
#    Import database/silk_swarakarna.sql

# 5. Edit .env di server
#    DB_HOST=localhost
#    DB_NAME=username_silk_swarakarna
#    DB_USER=username_dbuser
#    DB_PASS=...
#    APP_URL=https://yourdomain.tld
#    APP_DEBUG=false
```

Struktur target:

```
/home/user/silk-swarakarna/    {src, includes, views, ...}
/home/user/public_html/         symlink -> silk-swarakarna/public/
```

Document root harus point ke folder `public/`, bukan root repo. Root repo yang di-expose membuat `.env` dan source code bisa diakses publik.

## Struktur proyek

```
silk-swarakarna-uas/
├── public/                          | Document root (web entry)
│   ├── index.php                    | Front controller: route table, POST dispatch, auth gate, CSRF
│   ├── .htaccess                    | Apache rewrite: semua URI ke index.php?url=...
│   └── assets/css/app.css           | Design system: teal #0e7490, sidebar dark slate-900, 342 baris
│
├── src/                             | Domain layer, arsitektur CQRS (25+ class)
│   ├── Database.php                 | PDO singleton: getInstance, query, execute, transaction
│   ├── Entity/                      | Facade: validasi + business rules, delegasi ke Repo/Query
│   │   ├── Pasien.php               | CRUD + generate RM-XXX + 5 field demografis + FK-safe delete
│   │   ├── Dokter.php               | CRUD + search + FK-safe delete
│   │   ├── Layanan.php              | CRUD + FK-safe delete + biaya validation
│   │   └── Pemeriksaan.php          | CRUD + state machine + FOR UPDATE race-safe
│   ├── Repository/                  | Command side: CUD + simple reads per table
│   │   └── {Pasien,Dokter,Layanan,Pemeriksaan}Repository.php
│   ├── Query/                       | Query side: complex reads, JOINs, search, kode generator
│   │   └── {Pasien,Dokter,Layanan,Pemeriksaan}Query.php
│   ├── Presenter/                   | View data formatters (paginated, format Rupiah, badge)
│   │   └── {Pasien,Dokter,Layanan,Pemeriksaan}Presenter.php
│   ├── Exception/
│   │   └── ValidationException.php  | Per-field error map (field => message)
│   └── Validation/                  | Rule engine, mirip Laravel Validator
│       ├── Validator.php            | Validasi $data vs $rules, throw ValidationException
│       └── Rule/
│           ├── Rule.php             | Interface: 1 method validate(value): ?string
│           ├── Required.php         | Field wajib
│           ├── MaxLength.php        | Maks karakter
│           ├── DateNotFuture.php    | Tanggal maks hari ini
│           ├── PhoneFormat.php      | 10-15 digit angka
│           ├── Enum.php             | Must be in allowed list
│           └── PositiveNumber.php   | Bilangan positif
│
├── includes/
│   ├── bootstrap.php                | session_start, CSP + XCTO + Referrer-Policy, autoload, base_path
│   ├── config.php                   | .env parser: DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL
│   ├── helpers.php                  | format_tanggal, format_rupiah, old_input, flash_message, paginate
│   ├── auth.php                     | login/logout, is_logged_in, csrf_token/field/verify, hash_equals
│   └── logger.php                   | Error logger (logs/error.log)
│
├── views/                           | Template Bootstrap 5.3, Bahasa Indonesia
│   ├── layout/
│   │   ├── header.php               | <head>, sidebar dark, topbar, skip-to-content
│   │   ├── footer.php               | </main>, command palette, sidebar collapse JS, Bootstrap JS
│   │   ├── _sidebar.php             | Nav dark sidebar: Utama, Master Data, Transaksi, profile + logout
│   │   └── _topbar.php              | Sticky: toggle, breadcrumb auto, search button (cmd+K)
│   ├── partials/
│   │   ├── _flash.php               | Flash message (success/error), dismissible
│   │   ├── _sidebar_section.php     | Sidebar section reusable (label + item list)
│   │   ├── _command_palette.php     | cmd+K modal: 5 nav items, filter, Enter navigates
│   │   └── pagination.php           | Bootstrap 5 pagination + info text
│   ├── auth/
│   │   └── login.php                | Login form standalone
│   ├── dashboard.php                | 4 widget + tabel pemeriksaan terbaru
│   ├── pasien/                      | CRUD: index (search + pagination), create, edit, delete (confirm)
│   ├── dokter/                      | CRUD: index, create, edit, delete
│   ├── layanan/                     | CRUD: index, create, edit, delete
│   ├── pemeriksaan/                 | Index (search, filter status, quick action), create, delete, update_status
│   ├── errors/404.php               | 404 page
│   └── _placeholder.php
│
├── tests/                           | PHPUnit 10.5 (176 test, 303 assertion)
│   ├── bootstrap.php                | Autoload, DB reset, fixtures
│   ├── EntityTestCase.php           | Base: createdIds tracking + tearDown cleanup
│   ├── Auth/                        | Auth flow: login, logout, CSRF, session
│   ├── Entity/                      | CRUD + validation + race protection tests
│   ├── Repository/                  | SQL contract tests
│   ├── Query/                       | JOIN/search/kode generator tests
│   └── Presenter/                   | Format data, pagination, dashboard stats tests (real DB)
│
├── database/
│   └── silk_swarakarna.sql          | Schema + seed: 4 tabel, trigger, 8 pasien, 2 dokter, 3 layanan
│
├── docs/
│   ├── architecture.md              | Request lifecycle, CQRS, class diagram
│   └── business-logic.md            | Flowchart per fitur
│
├── DESIGN.md                        | Design system spec
├── phpunit.xml
├── composer.json
├── .env.example
├── .gitignore
└── README.md
```

## Arsitektur

CQRS ringan. Entity (validasi + business rules) delegasi ke Repository (CUD + read sederhana) atau Query (JOIN, search, kode generator). Presenter format data siap-view. View hanya terima data dari Presenter.

Detail di [docs/architecture.md](docs/architecture.md).

## Business flow

3 master data (Pasien, Dokter, Layanan) + 1 transaksi (Pemeriksaan). Status pemeriksaan: Menunggu -> Sedang Diperiksa -> Selesai. Bisa skip langsung ke Selesai atau revert ke Menunggu.

Detail di [docs/business-logic.md](docs/business-logic.md).

## Demo

1 user admin: `admin` / `admin123`. Login via `/login`. Semua POST dilindungi CSRF.

## Testing

```bash
# DDEV
ddev exec vendor/bin/phpunit

# baremetal
vendor/bin/phpunit
```

176 test, 303 assertion. Semua test pakai DB asli (bukan mock). Cleanup otomatis per test via EntityTestCase. Waktu eksekusi ~0.5 detik.

Coverage:
- Entity (4): CRUD, validasi, race protection FOR UPDATE
- Repository (4): insert, findAll, findById, update, delete, count
- Query (3): search, filter, JOIN, kode generator
- Presenter (4): formatRow, pagination, getDashboardStats, getOptions
- Auth (1): login, logout, CSRF, session

## Demo credentials

Username: `admin`
Password: `admin123`

## Deployment

| Target | Panduan |
|---|---|
| Local dev (DDEV) | Mode A |
| Local dev (baremetal) | Mode B |
| Shared hosting (cPanel) | Mode C |
| VPS (Docker) | Di luar scope UAS |

## Lisensi

MIT. Bebas dipakai untuk akademik, portfolio, pembelajaran.

Tim SILK-Swarakarna, Teknik Informatika Pagi 01, Primakara University. 6 mahasiswa.
