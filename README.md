# SILK-Swarakarna

> **Sistem Informasi Layanan Klinik Swakararna** — Aplikasi web PHP OOP untuk digitalisasi pencatatan rekam medis dan pendaftaran tes pendengaran di klinik THT (Telinga Hidung Tenggorokan) spesialis pendengaran & keseimbangan.

**UAS Pemrograman Web — Pagi 01 — Primakara University**

---

## Daftar Isi

1. [Ringkasan](#ringkasan)
2. [Entity Relationship Diagram](#entity-relationship-diagram)
3. [Alur Request (Flowchart)](#alur-request-flowchart)
4. [Struktur Proyek](#struktur-proyek)
5. [Quick Start](#quick-start) — 3 mode: **DDEV**, **baremetal**, **shared hosting**
6. [Tech Stack](#tech-stack)
7. [Sprint Roadmap](#sprint-roadmap) — 20 issue, dependency-aware
8. [Domain Reference](#domain-reference)
9. [Deployment](#deployment)
10. [Kontributor](#kontributor)

---

## Ringkasan

| Aspek | Detail |
|---|---|
| **Topik** | Sistem Klinik (varian: Klinik THT Swarakarna) |
| **Use case** | Pendaftaran pasien, manajemen dokter/layanan, pencatatan transaksi pemeriksaan |
| **Actor** | Admin / Resepsionis klinik (1 role) |
| **Master data** | 3 — pasien, dokter, layanan |
| **Transaksi** | 1 — pemeriksaan (relasi ke 3 master) |
| **Tabel DB** | 4 |
| **Class OOP** | 5 — Database + 4 entity |
| **Pattern** | Front controller + PDO singleton + MVC ringan |
| **Output** | Web app native PHP, tanpa framework |

---

## Entity Relationship Diagram

3 master (atas) → 1 transaksi (bawah). Kardinalitas **1:N** dari masing-masing master ke `pemeriksaan`.

![ERD SILK-Swarakarna](docs/diagrams/erd.png)

*File sumber: [docs/diagrams/erd.drawio](docs/diagrams/erd.drawio) — buka di [app.diagrams.net](https://app.diagrams.net) atau draw.io desktop untuk edit.*

**Ringkasan skema:**

| Tabel | Tipe | Primary Key | Foreign Key | Catatan |
|---|---|---|---|---|
| `pasien` | Master | `id_pasien` (VARCHAR, **No Rekam Medis** `RM-XXX`) | — | Kode otomatis |
| `dokter` | Master | `id_dokter` (INT, auto) | — | `no_izin_praktik` UNIQUE |
| `layanan` | Master | `id_layanan` (INT, auto) | — | `biaya` IDR integer |
| `pemeriksaan` | Transaksi | `id_periksa` (VARCHAR, **No Transaksi** `TRX-YYYYNNN`) | `id_pasien`, `id_dokter`, `id_layanan` | `status_pemeriksaan` ENUM |

FK constraint: `ON DELETE RESTRICT` — master yang sudah punya riwayat pemeriksaan tidak bisa dihapus. Setelah `status_pemeriksaan = 'Selesai'`, baris `pemeriksaan` **immutable** untuk audit trail.

---

## Alur Request (Flowchart)

Lifecycle sebuah HTTP request — dari klik di browser sampai HTML response.

![Request Flow](docs/diagrams/flowchart.png)

*File sumber: [docs/diagrams/flowchart.drawio](docs/diagrams/flowchart.drawio).*

**Singkatnya:**

```
User → Browser → Apache/Nginx → public/index.php (router)
  → includes/bootstrap.php (session + config + autoload)
  → match route → instantiate class (Pasien/Dokter/Layanan/Pemeriksaan)
  → Database::getInstance() → MariaDB
  → views/<page>.php (render HTML + Tailwind)
  → HTML response → Browser
```

Detail per layer: lihat [docs/architecture.md](docs/architecture.md).

---

## Struktur Proyek

```
silk-swarakarna-uas/
├── .ddev/                              ← DDEV config (PHP 8.2, MariaDB 10.11)
│
├── public/                             ← Document root (jangan expose src!)
│   ├── index.php                       ← Front controller + router
│   ├── .htaccess                       ← URL rewrite
│   └── assets/css/
│
├── src/                                ← Domain layer (5 class OOP)
│   ├── Database.php                    ← PDO singleton
│   ├── Pasien.php                      ← CRUD + generateKodeOtomatis
│   ├── Dokter.php                      ← CRUD
│   ├── Layanan.php                     ← CRUD
│   └── Pemeriksaan.php                 ← Transaksi + JOIN + status
│
├── includes/                           ← Bootstrap layer
│   ├── bootstrap.php                   ← session + autoload + error handler
│   └── config.php                      ← Env loader (.env)
│
├── views/                              ← Presentation layer
│   ├── layout/{header,footer}.php      ← Shell HTML + Tailwind
│   ├── dashboard.php
│   ├── pasien/  dokter/  layanan/  pemeriksaan/
│
├── database/
│   └── silk_swarakarna.sql             ← Schema + seed (3 pasien, 3 dokter, 4 layanan)
│
├── docs/                               ← Dokumentasi
│   ├── architecture.md                 ← Request lifecycle + class diagram
│   ├── business-logic.md               ← Flowchart per fitur
│   ├── diagrams/                       ← ERD + Flowchart (DrawIO source)
│   └── agents/                         ← Skill config
│
├── .scratch/silk-swarakarna-uas/       ← Issue tracker (local markdown)
│   ├── PRD.md
│   └── issues/01..20-*.md
│
├── CONTEXT.md                          ← Glossary domain
├── AGENTS.md                           ← Agent runtime config
├── composer.json                       ← PSR-4 autoload `Silk\` → `src/`
├── .env.example
├── .gitignore
└── README.md
```

---

## Quick Start

Pilih salah satu mode di bawah. **DDEV direkomendasikan** — paling cepat, tanpa install PHP lokal.

### Mode A — DDEV (Docker)

> Prasyarat: [Docker](https://docs.docker.com/get-docker/) + [DDEV](https://ddev.readthedocs.io/en/latest/).

```bash
git clone git@github.com:TudeOrangBiasa/silk-swarakarna-uas.git
cd silk-swarakarna-uas
ddev start
ddev composer install
ddev import-db --file=database/silk_swarakarna.sql
ddev launch
```

URL: `https://silk-swarakarna-uas.ddev.site/`

Kredensial DB otomatis: `db`/`db` di host `db` dalam container, db `silk_swarakarna`.

### Mode B — Baremetal (PHP lokal)

> Prasyarat: PHP ≥ 8.0 + extension `pdo_mysql`, Composer, MySQL 5.7+ / MariaDB 10.3+.

```bash
git clone git@github.com:TudeOrangBiasa/silk-swarakarna-uas.git
cd silk-swarakarna-uas

# 1. Install deps
composer install

# 2. Konfigurasi DB
cp .env.example .env
# Edit .env: DB_HOST, DB_NAME, DB_USER, DB_PASS

# 3. Import schema + seed
mysql -u root -p < database/silk_swarakarna.sql

# 4. Jalankan
php -S localhost:8000 -t public/
```

URL: `http://localhost:8000/`

### Mode C — Shared Hosting (cPanel / public_html)

> Prasyarat: akun hosting dengan PHP 8.0+ dan MySQL.

```bash
# 1. Lokal: build artifact (opsional, untuk upload sekali jalan)
composer install --no-dev --optimize-autoloader

# 2. Upload ke cPanel File Manager / FTP:
#    - Upload SELURUH isi repo ke public_html/
#    - atau upload ke folder di luar public_html/, symlink public/ ke public_html/

# 3. cPanel → MySQL Databases:
#    - Buat database `username_silk_swarakarna`
#    - Buat user + password
#    - Grant ALL pada database baru

# 4. cPanel → phpMyAdmin:
#    - Import database/silk_swarakarna.sql ke database baru

# 5. Edit .env di server (atau set env var di cPanel):
#    DB_HOST=localhost
#    DB_NAME=username_silk_swarakarna
#    DB_USER=username_dbuser
#    DB_PASS=...
#    APP_URL=https://yourdomain.tld
#    APP_DEBUG=false

# 6. Document root di cPanel harus point ke folder public/, BUKAN root repo.
#    Struktur target:
#    /home/user/silk-swarakarna/    ← {src, includes, views, ...}
#    /home/user/public_html/         ← symlink ke silk-swarakarna/public/
```

Detail deployment shared hosting: lihat [docs/agents/issue-tracker.md](docs/agents/issue-tracker.md) (umum) + panduan `shared-hosting-deployment` skill.

---

## Tech Stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend | PHP (OOP, PDO) | 8.2 (DDEV) / 8.0+ minimum |
| Database | MariaDB (DDEV) / MySQL | 10.11 / 5.7+ |
| Web server | nginx-fpm (DDEV) / Apache | — |
| Frontend | Tailwind CSS via CDN | 3.x |
| Autoload | Composer PSR-4 | 2.x |
| Dev env | DDEV (Docker) | 1.25+ |
| Version control | Git + GitHub | — |

**Tidak ada framework** — pure PHP native dengan class. Sesuai spec UAS "Aplikasi wajib menerapkan OOP murni".

---

## Sprint Roadmap

20 issue, dependency-aware. Detail per issue: [`.scratch/silk-swarakarna-uas/issues/`](.scratch/silk-swarakarna-uas/issues/).

| Wave | Issue | Bisa Paralel | Deskripsi |
|---|---|---|---|
| 1 | 01–05 | 5 orang | Foundation: bootstrap, DB, schema, router, layout |
| 2 | 06–09 | 4 orang | Domain classes: Pasien, Dokter, Layanan, Pemeriksaan |
| 3 | 10–17 | 8 orang | Views: list + form per master, create + list for transaksi |
| 4 | 18–19 | 2 orang | Dashboard widget + delete handlers |
| 5 | 20 | 1 orang | Final integration + README polish |

**Cara baca issue:** buka file `issues/<NN>-<slug>.md`. Tiap file punya:
- `Status: ready-for-agent` (per [`triage-labels.md`](docs/agents/triage-labels.md))
- `Depends:` (issue yang harus selesai dulu)
- `## Files` (file yang harus dibuat/dimodifikasi)
- `## Acceptance` (checklist testable)
- `## Test` (code snippet expected behavior)
- `## Out of scope` (batas-batas biar ga scope creep)

---

## Domain Reference

Glossary lengkap di [CONTEXT.md](CONTEXT.md). Ringkas:

- **Pasien** — orang yang terdaftar di klinik. ID = **No Rekam Medis** `RM-XXX` (auto-generated, disimpan di kolom `id_pasien`).
- **Dokter** — spesialis THT. ID = `id_dokter` (auto-increment).
- **Layanan** — jenis tes (Audiometri, OAE, BERA, Timpanometri). ID = `id_layanan`. Punya `biaya` (IDR).
- **Pemeriksaan** — transaksi: 1 Pasien + 1 Dokter + 1 Layanan pada tanggal tertentu. ID = **No Transaksi** `TRX-YYYYNNN` (auto-generated, disimpan di kolom `id_periksa`).
- **Status Pemeriksaan** — `Menunggu` → `Sedang Diperiksa` → `Selesai`. Sekali `Selesai`, **immutable** (audit trail).

---

## Deployment

| Target | Cocok Untuk | Panduan |
|---|---|---|
| **Local dev (DDEV)** | Pengembangan harian, demo ke dosen | [Mode A di Quick Start](#mode-a--ddev-docker) |
| **Local dev (baremetal)** | Yang sudah punya PHP/MySQL di host | [Mode B di Quick Start](#mode-b--baremetal-php-lokal) |
| **Shared hosting (cPanel)** | Submit tugas UAS, demo publik, portfolio | [Mode C di Quick Start](#mode-c--shared-hosting-cpanel--public_html) |
| **VPS (Docker)** | Scaling / production real | Setup Docker Compose custom (di luar scope UAS) |

**Checklist sebelum submit UAS:**
- [ ] Semua 20 issue selesai (`Status: done` atau di luar workflow)
- [ ] `ddev start` + `ddev launch` jalan tanpa error
- [ ] `ddev mysql -e "SELECT COUNT(*) FROM pemeriksaan;"` return > 0 (ada data demo)
- [ ] `ddev import-db --file=database/silk_swarakarna.sql` bisa diulang dari nol
- [ ] README + ERD + Flowchart terbaca dengan baik
- [ ] Folder `public/` jadi document root (cek `.htaccess` rewrite)

---

## Kontributor

**Tim SILK-Swarakarna — Teknik Informatika Pagi 01, Primakara University**

- Project lead: [@TudeOrangBiasa](https://github.com/TudeOrangBiasa)
- Tim: 6 mahasiswa (lihat git log untuk breakdown commit)

---

## Lisensi

MIT — Bebas digunakan untuk keperluan akademik (UAS), portfolio, atau pembelajaran.

---

## Referensi untuk Dosen Penilai

| Aspek UAS | Lokasi di Repo |
|---|---|
| Topik project + deskripsi | [`.scratch/silk-swarakarna-uas/PRD.md`](.scratch/silk-swarakarna-uas/PRD.md) |
| Analisis kebutuhan sistem | Section 2 PRD |
| Data master + CRUD | Lihat [issues 06–17](.scratch/silk-swarakarna-uas/issues/) (tiap master punya issue sendiri) |
| Data transaksi + relasi | Lihat [issue 09](.scratch/silk-swarakarna-uas/issues/09-pemeriksaan-class.md) + [issue 17](.scratch/silk-swarakarna-uas/issues/17-view-pemeriksaan-list.md) |
| Ketentuan OOP (5 class) | Lihat [`src/`](src/) (Database, Pasien, Dokter, Layanan, Pemeriksaan) |
| Fitur minimal (7 fitur) | Lihat [Sprint Roadmap](#sprint-roadmap) — semua tercakup di 20 issue |
| Fitur tambahan (kode otomatis + status transaksi) | [Issue 06](.scratch/silk-swarakarna-uas/issues/06-pasien-class.md) + [Issue 17](.scratch/silk-swarakarna-uas/issues/17-view-pemeriksaan-list.md) |
| ERD | [docs/diagrams/erd.png](docs/diagrams/erd.png) |
| Flowchart | [docs/diagrams/flowchart.png](docs/diagrams/flowchart.png) |
| Schema database | [database/silk_swarakarna.sql](database/silk_swarakarna.sql) |
| Cara jalanin | [Quick Start](#quick-start) di README ini |
