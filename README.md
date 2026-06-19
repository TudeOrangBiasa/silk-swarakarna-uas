# SILK-Swarakarna

Sistem Informasi Layanan Klinik Swarakarna. Aplikasi web PHP OOP untuk klinik THT spesialis pendengaran dan keseimbangan. UAS Pemrograman Web, Pagi 01, Primakara University.

## Daftar Isi

1. [Ringkasan](#ringkasan)
2. [Alur request](#alur-request)
3. [Struktur proyek](#struktur-proyek)
4. [Quick start](#quick-start)
5. [Tech stack](#tech-stack)
6. [Sprint roadmap](#sprint-roadmap)
7. [Domain reference](#domain-reference)
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
| Class OOP | 17 (1 Database + 4 Entity + 4 Repository + 4 Query + 4 Presenter) |
| Pattern | Front controller + PDO singleton + Repository/Query (CQRS) + Presenter |
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

Tree lengkap codebase. Deskripsi singkat per file/folder.

```
silk-swarakarna-uas/
├── .ddev/                              | DDEV config (PHP 8.2, MariaDB 10.11, nginx-fpm)
│
├── public/                             | Document root (web entry)
│   ├── index.php                       | Front controller: route table + POST dispatch + 404 fallback
│   ├── .htaccess                       | Apache rewrite: semua URI → index.php?url=...
│   └── assets/css/app.css              | Override --bs-primary ke navy #1e40af
│
├── src/                                | Domain layer — 17 class OOP, arsitektur CQRS
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
│   └── Presenter/                      | View data formatters (view-ready arrays)
│       ├── PasienPresenter.php         | getListData, getFormData, getOptions, getCount, formatRow
│       ├── DokterPresenter.php         | getListData, getFormData, getOptions, getCount
│       ├── LayananPresenter.php        | + format_rupiah untuk biaya
│       └── PemeriksaanPresenter.php    | + status badge HTML + 3 FK dropdowns (pasien/dokter/layanan)
│
├── includes/                           | Bootstrap + helpers
│   ├── bootstrap.php                   | session_start, error-to-exception, autoload, base_path
│   ├── config.php                      | .env parser: DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL
│   └── helpers.php                     | format_tanggal, format_rupiah, format_datetime, old_input, has_error, error_for, flash_message, query_param
│
├── views/                              | Presentation templates (Bootstrap 5.3)
│   ├── layout/
│   │   ├── header.php                  | <head> + navbar + container main open
│   │   └── footer.php                  | </main> + footer + Bootstrap JS CDN
│   ├── dashboard.php                   | 4 widget summary + tabel 5 pemeriksaan terbaru
│   ├── errors/404.php                  | 404 page dengan back-to-dashboard
│   └── _placeholder.php                | Placeholder untuk view belum diimplementasi
│
├── database/
│   └── silk_swarakarna.sql             | Schema + seed (3 pasien, 2 dokter, 3 layanan, 2 pemeriksaan)
│
├── docs/                               | Dokumentasi
│   ├── architecture.md                 | Request lifecycle + class diagram + CQRS rationale
│   ├── business-logic.md               | Flowchart per fitur (Pasien/Dokter/Layanan/Pemeriksaan)
│   ├── agents/                         | Skill config: domain, issue tracker, triage labels
│   └── designs/                        | Open CoDesign prompts untuk UI/UX design
│
├── DESIGN.md                           | Design system spec (Bootstrap 5.3, navy #1e40af, format Rupiah, status badge)
├── AGENTS.md                           | Agent runtime config + DESIGN.md reference
├── CONTEXT.md                          | Domain glossary (Pasien, Dokter, Layanan, Pemeriksaan, state machine)
├── composer.json                       | PSR-4 autoload Silk\ → src/
├── composer.lock                       | Lock file (zero external deps, hanya autoloader)
├── .env.example                        | Template DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL, APP_DEBUG
├── .gitignore                          | Exclude: vendor, .env, .ddev, .scratch, node_modules, *.log, .DS_Store
└── README.md                           | File ini
```

Arsitektur CQRS: Entity (validation/business rules) → Repository (Command: CUD + simple reads) atau Query (Query: complex reads, JOINs, search, generate). Presenter membungkus Entity untuk format data siap-view (tanggal Indonesia, Rupiah, status badge HTML, FK dropdown options). View tidak pernah akses Entity/Repository/Query langsung — selalu lewat Presenter.

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
| Dev env | DDEV (Docker) | 1.25+ |
| Version control | Git + GitHub | |

Tidak pakai framework. Sesuai spec UAS: OOP murni, class pisah jelas.

## Sprint roadmap

20 issue, dependency-aware. Detail per issue di [`.scratch/silk-swarakarna-uas/issues/`](.scratch/silk-swarakarna-uas/issues/).

| Wave | Issue | Bisa paralel | Deskripsi |
|---|---|---|---|
| 1 | 01–05 | 5 orang | Foundation: bootstrap, DB, schema, router, layout |
| 2 | 06–09 | 4 orang | Domain classes: Pasien, Dokter, Layanan, Pemeriksaan |
| 3 | 10–17 | 8 orang | Views: list + form per master, create + list untuk transaksi |
| 4 | 18–19 | 2 orang | Dashboard widget + delete handlers |
| 5 | 20 | 1 orang | Final integration + README polish |

Tiap file issue punya:
- `Status: ready-for-agent` (lihat [triage-labels.md](docs/agents/triage-labels.md))
- `Depends:` (issue yang harus selesai dulu)
- `## Files` (file yang harus dibuat)
- `## Acceptance` (checklist testable)
- `## Test` (code snippet)
- `## Out of scope` (batas-batas biar tidak scope creep)

## Domain reference

Glossary lengkap di [CONTEXT.md](CONTEXT.md). Ringkas:

- **Pasien**: orang yang terdaftar di klinik. ID = No Rekam Medis `RM-XXX` (auto-generated, disimpan di kolom `id_pasien`).
- **Dokter**: spesialis THT. ID = `id_dokter` (auto-increment).
- **Layanan**: jenis tes (Audiometri, OAE, BERA, Timpanometri). ID = `id_layanan`. Punya `biaya` (IDR).
- **Pemeriksaan**: transaksi 1 Pasien + 1 Dokter + 1 Layanan pada tanggal tertentu. ID = No Transaksi `TRX-YYYYNNN` (disimpan di kolom `id_periksa`).
- **Status Pemeriksaan**: `Menunggu` → `Sedang Diperiksa` → `Selesai`. Sekali `Selesai`, immutable (audit trail).

## Deployment

| Target | Cocok untuk | Panduan |
|---|---|---|
| Local dev (DDEV) | Pengembangan harian, demo ke dosen | [Mode A](#mode-a-ddev-docker) |
| Local dev (baremetal) | Sudah punya PHP/MySQL di host | [Mode B](#mode-b-baremetal-php-lokal) |
| Shared hosting (cPanel) | Submit UAS, demo publik, portfolio | [Mode C](#mode-c-shared-hosting-cpanel--public_html) |
| VPS (Docker) | Scaling / production real | Di luar scope UAS |

Checklist sebelum submit UAS:

- [ ] Semua 20 issue selesai
- [ ] `ddev start` + `ddev launch` jalan tanpa error
- [ ] `ddev mysql -e "SELECT COUNT(*) FROM pemeriksaan;"` return > 0
- [ ] `ddev import-db --file=database/silk_swarakarna.sql` bisa diulang dari nol
- [ ] README terbaca
- [ ] `public/` jadi document root (cek `.htaccess` rewrite)

## Kontributor

Tim SILK-Swarakarna, Teknik Informatika Pagi 01, Primakara University. 6 mahasiswa, lihat git log untuk breakdown commit.

Lisensi: MIT. Bebas dipakai untuk akademik, portfolio, atau pembelajaran.
