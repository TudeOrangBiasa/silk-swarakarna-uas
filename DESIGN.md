# SILK-Swarakarna design system

Single source of truth untuk semua styling. Semua view, design export, dan code review harus conform ke spec ini. Update file ini dulu sebelum ganti styling di code.

## Stack

- CSS framework: Bootstrap 5.3 via CDN
- Icons: Bootstrap Icons 1.11 via CDN
- JS: Bootstrap bundle (5.3) via CDN
- No build step. No npm. No Tailwind. No Materialize.

CDN:

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
```

## Brand color

Primary: navy `#1e40af` (Bootstrap `bg-primary` default, override ke navy via CSS variable di `header.php`).

| Token | Hex | Bootstrap class | Pakai untuk |
|---|---|---|---|
| Primary | `#1e40af` | `bg-primary`, `btn-primary` | Navbar, primary button, link aktif |
| Primary dark | `#1e3a8a` | hover state | |
| Body bg | `#f8f9fa` | `bg-body` | Page background |
| Surface | `#ffffff` | `bg-white` | Card, navbar, table head |
| Text | `#212529` | `text-body` | Default body |
| Muted | `#6c757d` | `text-muted` | Helper, footer, caption |
| Border | `#dee2e6` | `border` | Card, input, divider |
| Success | `#198754` | `bg-success` | Status Selesai |
| Warning | `#ffc107` | `bg-warning` | Status Menunggu |
| Info | `#0dcaf0` | `bg-info` | Status Sedang Diperiksa |
| Danger | `#dc3545` | `bg-danger` | Hapus, error |

## Typography

- Font: system sans-serif stack (no Google Fonts)
- Mono: system mono stack untuk No Rekam Medis dan No Transaksi
- H1 page title: `h1`, `fw-semibold`, `mb-4`
- H2 section: `h2`, `h4` Bootstrap class, `fw-semibold`
- Body: default 1rem
- Small: `small` atau `text-muted`

## Spacing

- Page wrapper: `<div class="container py-4">` di dalam `<main>`
- Section gap: `mb-4`
- Card body: `p-4`
- Form field gap: `mb-3`
- Form actions: `d-flex gap-2 mt-4`

## Components

### Navbar

```html
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/">SILK-Swarakarna</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="/">Dashboard</a></li>
      </ul>
    </div>
  </div>
</nav>
```

Active link: tambah class `active` dan `aria-current="page"`. Pakai PHP bandingkan `$current_uri` dengan path.

### Card

```html
<div class="card shadow-sm">
  <div class="card-header bg-white">
    <h2 class="h5 mb-0">Judul card</h2>
  </div>
  <div class="card-body p-4">...</div>
  <div class="card-footer bg-white d-flex gap-2 justify-content-end">...</div>
</div>
```

Tanpa header atau footer? Tinggal hapus bagian itu.

### Button

| Aksi | Class |
|---|---|
| Simpan, Daftar, primary | `btn btn-primary` |
| Tambah | `btn btn-primary` + icon `bi-plus-lg` |
| Edit | `btn btn-sm btn-outline-secondary` + icon `bi-pencil` |
| Hapus | `btn btn-sm btn-outline-danger` + icon `bi-trash` |
| Mulai | `btn btn-sm btn-info` + icon `bi-play-fill` |
| Selesai | `btn btn-sm btn-success` + icon `bi-check-lg` |
| Batal | `btn btn-outline-secondary` |

### Table

```html
<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="table-light">
      <tr><th>No</th><th>Nama</th><th class="text-end">Biaya</th><th>Aksi</th></tr>
    </thead>
    <tbody>...</tbody>
  </table>
</div>
```

- ID/No Rekam Medis/No Transaksi: bungkus `<code>` agar dapat font monospace
- Numeric/rupiah: kolom pakai `class="text-end"`, format pakai helper `format_rupiah()`

### Form

- Stack vertical (bukan inline, kecuali search bar)
- Label: `<label class="form-label">Nama <span class="text-danger">*</span></label>`
- Input: `class="form-control"`. Untuk select: `form-select`
- Error: `class="invalid-feedback d-block"` di bawah field
- Required validation: HTML5 `required` attribute, server-side double check

### Status badge

```html
<span class="badge rounded-pill bg-warning text-dark">Menunggu</span>
<span class="badge rounded-pill bg-info text-dark">Sedang Diperiksa</span>
<span class="badge rounded-pill bg-success">Selesai</span>
```

### Empty state

```html
<div class="text-center py-5">
  <i class="bi bi-people display-1 text-secondary opacity-50"></i>
  <p class="text-muted mb-3">Belum ada data pasien.</p>
  <a href="/pasien/create" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> Tambah Pasien pertama
  </a>
</div>
```

### Pagination

```html
<div class="d-flex justify-content-between align-items-center mt-4">
  <small class="text-muted">Menampilkan 1-10 dari 25</small>
  <nav>
    <ul class="pagination pagination-sm mb-0">
      <li class="page-item disabled"><a class="page-link">Prev</a></li>
      <li class="page-item active"><a class="page-link">1</a></li>
      <li class="page-item"><a class="page-link" href="?page=2">2</a></li>
    </ul>
  </nav>
</div>
```

### Search bar

```html
<form method="get" class="mb-3">
  <div class="input-group">
    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
    <input type="search" name="q" class="form-control" placeholder="Cari nama pasien...">
  </div>
</form>
```

### Dashboard widget

```html
<div class="card shadow-sm h-100">
  <div class="card-body p-4">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <small class="text-muted text-uppercase fw-semibold">Total Pasien</small>
        <div class="display-6 fw-bold">42</div>
      </div>
      <i class="bi bi-people-fill text-primary" style="font-size: 2.5rem;"></i>
    </div>
  </div>
</div>
```

Grid: `<div class="row g-3">` lalu `<div class="col-12 col-md-6 col-lg-3">` per widget.

## Icon mapping

| Konsep | Icon |
|---|---|
| Pasien | `bi-person` / `bi-people-fill` |
| Dokter | `bi-clipboard-pulse` |
| Layanan | `bi-clipboard-data` |
| Pemeriksaan | `bi-calendar-check` |
| Edit | `bi-pencil` |
| Hapus | `bi-trash` |
| Cari | `bi-search` |
| Tambah | `bi-plus-lg` |
| Simpan | `bi-check-lg` |
| Batal | `bi-x-lg` |
| Mulai | `bi-play-fill` |
| Selesai | `bi-check-circle-fill` |
| Menunggu | `bi-hourglass-split` |

Pakai `bi-*` saja, jangan emoji. Lihat semua di [icons.getbootstrap.com](https://icons.getbootstrap.com).

## Format data

| Tipe | Format | Contoh |
|---|---|---|
| Tanggal | `dd/mm/yyyy` | `19/06/2026` |
| Rupiah | `Rp 250.000` (titik, tanpa desimal) | `Rp 1.500.000` |
| No Rekam Medis | `RM-XXX` (uppercase) | `RM-001` |
| No Transaksi | `TRX-YYYYNNN` | `TRX-2026007` |

Helper:

```php
function format_rupiah(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

function format_tanggal(string $iso): string {
    $d = new DateTime($iso);
    return $d->format('d/m/Y');
}
```

## Bahasa

Semua string UI wajib Bahasa Indonesia. Validasi error, placeholder, button label, table header, empty state, semua. Lihat [CONTEXT.md](CONTEXT.md) untuk glossary.

## Responsive

- Mobile-first (Bootstrap default)
- Table di mobile: wajib `<div class="table-responsive">` wrapper
- Form di mobile: full-width input
- Navbar mobile: `navbar-expand-lg` agar collapse di bawah 992px
- Dashboard grid: `col-12 col-md-6 col-lg-3` (1/2/4 kolom)

## Larangan

- Tailwind atau framework lain
- Custom CSS (kecuali override `--bs-primary` di header.php)
- Inline `style=""` untuk visual
- Emoji sebagai icon
- English UI string
- Build step (npm, webpack, vite)
- Komponen Bootstrap yang ga dipakai di app (accordion, carousel, offcanvas) supaya bundle tetap kecil

## Lokasi custom CSS

Satu file saja: `public/assets/css/app.css`. Override `--bs-primary` ke navy di sini. Tidak ada custom utility.

```css
:root {
  --bs-primary: #1e40af;
  --bs-primary-rgb: 30, 64, 175;
}
```

## Hubungan dengan file lain

- `views/layout/header.php`: include CDN + navbar partial
- `views/layout/footer.php`: include Bootstrap JS CDN + footer
- `public/assets/css/app.css`: override warna primary
- `docs/designs/prompts.md`: prompt untuk Open CoDesign, ganti "Tailwind" jadi "Bootstrap 5"
- `AGENTS.md`: agent harus baca DESIGN.md sebelum edit view apa pun
