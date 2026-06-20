# SILK-Swarakarna

Sistem Informasi Layanan Klinik Swarakarna. Aplikasi web PHP OOP untuk klinik THT spesialis pendengaran dan keseimbangan. UAS Pemrograman Web, Pagi 01, Primakara University.

## Daftar Isi

1. [Ringkasan](#ringkasan)
2. [Alur request](#alur-request)
3. [Struktur proyek](#struktur-proyek)
4. [Quick start](#quick-start)
5. [Tech stack](#tech-stack)
6. [Domain reference](#domain-reference)
7. [Testing](#testing)
8. [Deployment](#deployment)

## Ringkasan

| Aspek | Detail |
|---|---|
| Topik | Sistem Klinik (varian: Klinik THT Swarakarna) |
| Use case | Pendaftaran pasien, manajemen dokter/layanan, pencatatan transaksi pemeriksaan |
| Actor | Admin / Resepsionis klinik (1 role) |
| Master data | 3 (pasien, dokter, layanan) |
| Transaksi | 1 (pemeriksaan, relasi ke 3 master) |
| Tabel DB | 4 |
| Class OOP | 25 (1 Database + 4 Entity + 4 Repository + 4 Query + 4 Presenter + 1 Exception + 1 Validator + 5 Rule + 1 Rule interface) |
| Test | 154 unit/integration, 256 assertions, PHPUnit 10.5 |
| Pattern | Front controller + PDO singleton + Repository/Query (CQRS) + Presenter + Validation rules |
| Output | Web app native PHP, tanpa framework |

## Alur request

Singkatnya:

```
User → Browser → Nginx/Apache → public/index.php (router)
  → includes/bootstrap.php (session + config + autoload)
  → match route → new Class() → method
  → Database::getInstance() → MariaDB
  → views/<page>.php (render HTML + Bootstrap 5)
  → HTML response
```

Detail per layer ada di [docs/architecture.md](docs/architecture.md).

## Struktur proyek

Tree lengkap codebase. Deskripsi singkat per file/folder. Folder `.ddev/` dan `.scratch/` di-gitignore (konfigurasi lokal + issue tracker lokal).

```
silk-swarakarna-uas/
├── public/                             | Document root (web entry)
│   ├── index.php                       | Front controller: route table + POST dispatch + 404 fallback
│   ├── .htaccess                       | Apache rewrite: semua URI → index.php?url=...
│   └── assets/css/app.css              | Override --bs-primary ke navy #1e40af
│
├── src/                                | Domain layer — 25 class OOP, arsitektur CQRS
│   ├── Database.php                    | PDO singleton: query, execute, transaction, lastInsertId
│   ├── Entity/                         | Entity facade: validation + business rules, delegate ke Repo/Query
│   │   ├── Pasien.php                  | Pasien CRUD + generate RM-XXX + FK-safe delete
│   │   ├── Dokter.php                  | Dokter CRUD + search + FK-safe delete
│   │   ├── Layanan.php                 | Layanan CRUD + FK-safe delete + biaya validation
│   │   └── Pemeriksaan.php             | Pemeriksaan CRUD + state machine + FOR UPDATE (race-safe)
│   ├── Repository/                     | Command side: CUD + simple reads per table
│   │   ├── PasienRepository.php        | pasien table: insert, findAll, findById, update, delete, count
│   │   ├── DokterRepository.php        | dokter table (same pattern)
│   │   ├── LayananRepository.php       | layanan table (same pattern)
│   │   └── PemeriksaanRepository.php   | pemeriksaan table + updateStatus + deleteIfNotSelesai
│   ├── Query/                          | Query side: complex reads, JOINs, search, code generation
│   │   ├── PasienQuery.php             | searchByName, findForOptions, generateKodeOtomatis (RM-NNN)
│   │   ├── DokterQuery.php             | searchByName, findDokterForOptions
│   │   ├── LayananQuery.php            | findLayananForOptions
│   │   └── PemeriksaanQuery.php        | generateKodeOtomatis (TRX-YYYYNNN), findAllJoined, findByIdJoined, findStatusForUpdate
│   ├── Presenter/                      | View data formatters (view-ready arrays, paginated)
│   │   ├── PasienPresenter.php         | getListData (paginated), getFormData, getOptions, getCount
│   │   ├── DokterPresenter.php         | getListData (paginated), getFormData, getOptions, getCount
│   │   ├── LayananPresenter.php        | getListData (paginated), getFormData, getOptions + format_rupiah
│   │   └── PemeriksaanPresenter.php    | getListData (JOIN + paginated + filter), getFormData, status badge HTML, FK dropdowns, getDashboardStats, getLatest
│   ├── Exception/
│   │   └── ValidationException.php     | Per-field validation error (getErrors() returns map field → message)
│   └── Validation/                     | Rule engine (mirip Laravel Validator)
│       ├── Validator.php               | Validates $data against $rules; throws ValidationException
│       └── Rule/
│           ├── Rule.php                | Rule interface (1 method: check(value, allData): bool)
│           ├── Required.php            | Field wajib ada
│           ├── MaxLength.php           | Maks karakter
│           ├── DateNotFuture.php       | Tanggal tidak boleh di masa depan
│           ├── PhoneFormat.php         | 10-15 digit angka
│           └── PositiveNumber.php      | Bilangan positif
│
├── includes/                           | Bootstrap + helpers
│   ├── bootstrap.php                   | session_start, error-to-exception, autoload, base_path
│   ├── config.php                      | .env parser: DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL
│   ├── helpers.php                     | format_tanggal, format_rupiah, format_datetime, old_input, has_error, error_for, flash_message, query_param, paginate
│   └── logger.php                      | Error logger (logs/error.log)
│
├── views/                              | Presentation templates (Bootstrap 5.3)
│   ├── layout/
│   │   ├── header.php                  | <head> + sidebar nav + container main open
│   │   └── footer.php                  | </main> + footer + Bootstrap JS CDN
│   ├── partials/
│   │   └── pagination.php              | Bootstrap 5 pagination + "Menampilkan X–Y dari Z" (skip jika 1 halaman)
│   ├── dashboard.php                   | 4 widget summary + tabel 5 pemeriksaan terbaru
│   ├── pasien/                         | CRUD: index (search + pagination), create, edit, delete (confirm)
│   ├── dokter/                         | CRUD: index, create, edit, delete
│   ├── layanan/                        | CRUD: index, create, edit, delete
│   ├── pemeriksaan/                    | index (search + status filter + quick status transition), create, delete (blocked if Selesai), update_status
│   ├── errors/404.php                  | 404 page dengan back-to-dashboard
│   └── _placeholder.php                | Placeholder untuk view belum diimplementasi
│
├── tests/                              | PHPUnit test suite (154 test, 256 assertion)
│   ├── bootstrap.php                   | Test bootstrap (autoload, DB reset, fixtures)
│   ├── EntityTestCase.php              | Base class: createdIds tracking + tearDown cleanup
│   ├── Entity/                         | Entity CRUD + validation + race protection tests
│   ├── Repository/                     | Repository SQL contract tests
│   ├── Query/                          | Query/JOIN/search tests
│   └── Presenter/                      | Presenter integration tests (real DB)
│
├── database/
│   └── silk_swarakarna.sql             | Schema + seed (3 pasien, 2 dokter, 3 layanan, 2 pemeriksaan)
│
├── docs/                               | Dokumentasi
│   ├── architecture.md                 | Request lifecycle + class diagram + CQRS rationale
│   ├── business-logic.md               | Flowchart per fitur (Pasien/Dokter/Layanan/Pemeriksaan)
│   ├── agents/                         | Skill config: domain, issue tracker, triage labels
│   └── designs/                        | Open CoDesign prompts untuk UI/UX design (system-prompt + 10 page briefs)
│
├── DESIGN.md                           | Design system spec (Bootstrap 5.3, navy #1e40af, format Rupiah, status badge)
├── AGENTS.md                           | Agent runtime config + DESIGN.md reference
├── CONTEXT.md                          | Domain glossary (Pasien, Dokter, Layanan, Pemeriksaan, state machine)
├── phpunit.xml                         | PHPUnit config (testsuite dir + coverage filter)
├── composer.json                       | PSR-4 autoload Silk\ → src/, dev dep: phpunit/phpunit ^10.5
├── composer.lock                       | Lock file (zero runtime deps, dev: phpunit only)
├── .env.example                        | Template DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL, APP_DEBUG
├── .gitignore                          | Exclude: vendor, .env, .ddev, .scratch, qa-screenshots, node_modules, *.log, .DS_Store
└── README.md                           | File ini
```

Arsitektur CQRS: Entity (validation/business rules) → Repository (Command: CUD + simple reads) atau Query (Query: complex reads, JOINs, search, generate). Validation pakai Rule engine (interface + 5 concrete) yang lempar `ValidationException` dengan per-field error map — router tangkap dan redirect dengan error flash. Presenter membungkus Entity untuk format data siap-view (tanggal Indonesia, Rupiah, status badge HTML, FK dropdown options) + pagination metadata. View tidak pernah akses Entity/Repository/Query langsung — selalu lewat Presenter.

## Quick start

Pilih salah satu. DDEV paling cepat karena tidak perlu install PHP lokal.

### Mode A: DDEV (Docker)

Prasyarat: [Docker](https://docs.docker.com/get-docker/) + [DDEV](https://ddev.readthedocs.io/en/latest/).

`.ddev/config.yaml` tidak di-commit (setiap dev setup lokal). Jalankan `ddev config` dulu setelah clone.

```bash
git clone git@github.com:TudeOrangBiasa/silk-swarakarna-uas.git
cd silk-swarakarna-uas
ddev config --project-name=silk-swarakarna --docroot=public --php-version=8.2 --mariadb-version=10.11
ddev start
ddev composer install
ddev import-db --file=database/silk_swarakarna.sql
ddev launch
```

Atau `ddev config` interaktif, isi prompt: project name, docroot=`public`, php version, mariadb version.

URL: `https://<project-name>.ddev.site/` (default `https://silk-swarakarna.ddev.site/`).

Kredensial DB otomatis: `db`/`db` di host `db` dalam container, database `silk_swarakarna`. Env di-inject oleh DDEV lewat `web_environment`, jadi tidak perlu edit `.env`.

### Mode B: baremetal (PHP lokal)

Prasyarat: PHP 8.0+ + extension `pdo_mysql`, Composer, MySQL 5.7+ atau MariaDB 10.3+.

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

Prasyarat: akun hosting dengan PHP 8.0+ dan MySQL.

```bash
# 1. Lokal: build artifact (opsional)
composer install --no-dev --optimize-autoloader

# 2. Upload via cPanel File Manager / FTP:
#    - Upload SELURUH isi repo ke /home/user/silk-swarakarna/
#    - Symlink public/ ke /home/user/public_html/
#      atau upload public/ ke public_html/ langsung

# 3. cPanel → MySQL Databases:
#    - Buat database `username_silk_swarakarna`
#    - Buat user + password
#    - Grant ALL

# 4. cPanel → phpMyAdmin:
#    - Import database/silk_swarakarna.sql

# 5. Edit .env di server:
#    DB_HOST=localhost
#    DB_NAME=username_silk_swarakarna
#    DB_USER=username_dbuser
#    DB_PASS=...
#    APP_URL=https://yourdomain.tld
#    APP_DEBUG=false
```

Struktur target di server:

```
/home/user/silk-swarakarna/    {src, includes, views, ...}
/home/user/public_html/         symlink → silk-swarakarna/public/
```

Document root cPanel harus point ke folder `public/`, bukan root repo. Ini penting: kalau root repo yang di-expose, file `.env` dan source code bisa diakses publik.

## Tech stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend | PHP (OOP, PDO) | 8.2 (DDEV) / 8.0+ minimum |
| Database | MariaDB (DDEV) / MySQL | 10.11 / 5.7+ |
| Web server | nginx-fpm (DDEV) / Apache | |
| Frontend | Bootstrap 5.3 + Bootstrap Icons via CDN | 5.3.3 / 1.11.3 |
| Autoload | Composer PSR-4 | 2.x |
| Testing | PHPUnit | 10.5 |
| Dev env | DDEV (Docker) | 1.25+ |
| Version control | Git + GitHub | |

Tidak pakai framework. Sesuai spec UAS: OOP murni, class pisah jelas.

## Domain reference

Glossary lengkap di [CONTEXT.md](CONTEXT.md). Ringkas:

- **Pasien**: orang yang terdaftar di klinik. ID = No Rekam Medis `RM-XXX` (auto-generated, disimpan di kolom `id_pasien`).
- **Dokter**: spesialis THT. ID = `id_dokter` (auto-increment).
- **Layanan**: jenis tes (Audiometri, OAE, BERA, Timpanometri). ID = `id_layanan`. Punya `biaya` (IDR).
- **Pemeriksaan**: transaksi 1 Pasien + 1 Dokter + 1 Layanan pada tanggal tertentu. ID = No Transaksi `TRX-YYYYNNN` (disimpan di kolom `id_periksa`).
- **Status Pemeriksaan**: `Menunggu` → `Sedang Diperiksa` → `Selesai`. Sekali `Selesai`, immutable (audit trail).

## Testing

Test suite PHPUnit 10.5, 154 test / 256 assertion. Layer: Entity, Repository, Query, Presenter. Semua test pakai DB asli (bukan mock) dengan explicit cleanup di `tearDown` via `EntityTestCase` (tracking `createdIds` + FK-order delete).

```bash
# DDEV
ddev exec vendor/bin/phpunit

# baremetal
vendor/bin/phpunit
```

Test cepat: ~0.5 detik (DB sudah seeded). Test otomatis reset data per test, jadi bisa dijalankan paralel aman.

Coverage:

- **Entity (4)**: CRUD + validation rules + race protection (Pemeriksaan `FOR UPDATE`)
- **Repository (4)**: SQL contract — insert/findAll/findById/update/delete + count
- **Query (3)**: search/filter/JOIN/code generation
- **Presenter (4)**: formatRow (tanggal Indonesia, Rupiah, status badge), getListData (pagination), getOptions, getDashboardStats

## Deployment

| Target | Cocok untuk | Panduan |
|---|---|---|
| Local dev (DDEV) | Pengembangan harian, demo ke dosen | [Mode A](#mode-a-ddev-docker) |
| Local dev (baremetal) | Sudah punya PHP/MySQL di host | [Mode B](#mode-b-baremetal-php-lokal) |
| Shared hosting (cPanel) | Submit UAS, demo publik, portfolio | [Mode C](#mode-c-shared-hosting-cpanel--public_html) |
| VPS (Docker) | Scaling / production real | Di luar scope UAS |

## Kontributor

Tim SILK-Swarakarna, Teknik Informatika Pagi 01, Primakara University. 6 mahasiswa, lihat git log untuk breakdown commit.

Lisensi: MIT. Bebas dipakai untuk akademik, portfolio, atau pembelajaran.
