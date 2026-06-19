# SILK-Swarakarna

**Sistem Informasi Layanan Klinik Swarakarna** — Aplikasi web PHP OOP untuk mendigitalisasi pencatatan rekam medis dan pendaftaran tes pendengaran di klinik THT (Telinga Hidung Tenggorokan) spesialis pendengaran dan keseimbangan.

UAS Pemrograman Web — Pagi 01 — Primakara University.

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP 8.2 (OOP, PDO, MySQL) |
| Database | MariaDB 10.11 (via DDEV) |
| Frontend | Tailwind CSS (via CDN) |
| Autoload | Composer PSR-4 (`Silk\` → `src/`) |
| Dev env | DDEV (Docker-based, no local PHP needed) |

## Quick Start (DDEV)

Prasyarat: [Docker](https://docs.docker.com/get-docker/) + [DDEV](https://ddev.readthedocs.io/en/latest/) terinstall.

```bash
# 1. Clone
git clone git@github.com:TudeOrangBiasa/silk-swarakarna-uas.git
cd silk-swarakarna-uas

# 2. Start DDEV (otomatis provisioning PHP 8.2 + MariaDB 10.11 + nginx)
ddev start

# 3. Install Composer dependencies (di dalam container)
ddev composer install

# 4. Import schema + seed data
ddev setup-db

# 5. Open browser
ddev launch
```

URL default: `https://silk-swarakarna-uas.ddev.site/`

### Perintah DDEV yang sering dipakai

```bash
ddev start            # nyalakan container
ddev stop             # matikan container
ddev restart          # restart
ddev launch           # buka di browser
ddev describe         # tampilkan URL + kredensial

ddev setup-db         # import database/silk_swarakarna.sql
ddev reset-db         # drop + re-import (ulang dari nol)

ddev composer install # install/update composer deps
ddev composer <args>  # run composer command di dalam container

ddev exec php <file>  # run PHP script di dalam container
ddev exec php -r "..."# run PHP one-liner

ddev mysql            # masuk MariaDB shell
ddev mysql -e "SHOW TABLES;"  # query cepat
ddev logs             # tail logs container
```

### Kredensial DDEV default

- **DB host**: `db` (di dalam container) / `localhost:3306` (dari host)
- **DB user**: `db`
- **DB pass**: `db`
- **DB name**: `silk_swarakarna`

Tidak perlu edit `.env` secara manual — DDEV inject env vars lewat `web_environment` di `.ddev/config.yaml`.

## Folder Structure

```
silk-swarakarna-uas/
├── .ddev/                              ← DDEV config (tracked)
│   ├── config.yaml                     ← PHP 8.2, MariaDB 10.11
│   └── commands/host/
│       ├── setup-db                    ← ddev setup-db
│       └── reset-db                    ← ddev reset-db
│
├── public/                             ← Document root
│   ├── index.php                       ← Front controller + router
│   ├── .htaccess                       ← URL rewrite
│   └── assets/css/
│
├── src/                                ← OOP domain classes (issues 02, 06-09)
│   ├── Database.php                    ← PDO singleton (issue 02)
│   ├── Pasien.php                      ← (issue 06)
│   ├── Dokter.php                      ← (issue 07)
│   ├── Layanan.php                     ← (issue 08)
│   └── Pemeriksaan.php                 ← (issue 09)
│
├── includes/
│   ├── bootstrap.php                   ← Autoload + session + errors (issue 01)
│   └── config.php                      ← Env loader (issue 01)
│
├── views/                              ← Presentation layer
│   ├── layout/{header,footer}.php      ← (issue 05)
│   ├── dashboard.php                   ← (issue 18)
│   ├── pasien/                         ← (issues 10, 11)
│   ├── dokter/                         ← (issues 12, 13)
│   ├── layanan/                        ← (issues 14, 15)
│   └── pemeriksaan/                    ← (issues 16, 17)
│
├── database/
│   └── silk_swarakarna.sql             ← Schema + seed (issue 03)
│
├── docs/                               ← Architecture + business logic
│   ├── architecture.md
│   ├── business-logic.md
│   └── agents/                         ← Skill config
│
├── .scratch/silk-swarakarna-uas/       ← Issue tracker (local markdown)
│   ├── PRD.md
│   └── issues/                         ← 20 tracked issues
│
├── CONTEXT.md                          ← Domain glossary
├── AGENTS.md                           ← Agent skill config
├── composer.json
├── .env.example                        ← Template (DDEV inject env otomatis)
└── README.md
```

## Sprint Roadmap

20 issues, dependency-aware:

| Wave | Issues | Bisa paralel | Description |
|---|---|---|---|
| 1 | 01–05 | 5 orang | Foundation: bootstrap, DB, schema, router, layout |
| 2 | 06–09 | 4 orang | Domain classes: Pasien, Dokter, Layanan, Pemeriksaan |
| 3 | 10–17 | 8 orang | Views: list+form per master + create+list for transaksi |
| 4 | 18–19 | 2 orang | Dashboard widget + delete handlers |
| 5 | 20 | 1 orang | Final integration + README polish |

Detail per issue: `.scratch/silk-swarakarna-uas/issues/<NN>-<slug>.md`

## Workflow Kontribusi

1. Pick issue yang belum diklaim (set `Status:` ke `ready-for-human` atau `wontfix` di file issue)
2. Buat branch: `git checkout -b feature/<NN>-<slug>`
3. Implement sesuai acceptance criteria di file issue
4. Test via `ddev exec php -r "..."` atau buka di browser
5. Commit + push
6. PR ke `main`

## Domain Reference

- **Pasien** (Pasien) — pendaftar klinik. Identifier: **No Rekam Medis** format `RM-XXX` (auto-generated, stored in `id_pasien` column).
- **Dokter** (Dokter) — spesialis THT. PK: `id_dokter` (auto-increment).
- **Layanan** (Layanan) — jenis tes (Audiometri, OAE, BERA, Timpanometri). PK: `id_layanan` (auto-increment). Punya `biaya` (IDR integer).
- **Pemeriksaan** (Pemeriksaan) — transaksi: 1 Pasien + 1 Dokter + 1 Layanan pada tanggal tertentu. Identifier: **No Transaksi** format `TRX-YYYYNNN` (auto-generated, stored in `id_periksa` column).
- **Status Pemeriksaan**: `Menunggu` → `Sedang Diperiksa` → `Selesai`. Sekali `Selesai`, immutable (tidak bisa dihapus — audit trail).

Lihat `CONTEXT.md` untuk glossary lengkap.

## License

MIT — UAS project, Primakara University Teknik Informatika Pagi 01.

## Tim

_— Tim SILK-Swarakarna —_
