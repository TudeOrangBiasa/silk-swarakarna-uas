# 18 — Dashboard View

Status: ready-for-agent
Slug: 18-view-dashboard
Depends: 04, 05, 06, 07, 08, 09
Estimate: 1h

## Goal

Build dashboard page: 4 summary widgets (total pasien, total dokter, total layanan, total pemeriksaan hari ini) + table of 5 latest pemeriksaan. Use counts from each master class + readLatest from Pemeriksaan.

## Files

- `views/dashboard.php` (create)

## Acceptance

- [ ] Widget 1: "Total Pasien" — Pasien::read() count or dedicated count() method
- [ ] Widget 2: "Total Dokter" — count(Dokter::read())
- [ ] Widget 3: "Total Layanan" — count(Layanan::read())
- [ ] Widget 4: "Pemeriksaan Hari Ini" — filtered count from Pemeriksaan::readWithJoin() where tanggal_periksa = today
- [ ] Widgets displayed as Tailwind cards: bg-white, shadow, rounded-lg, icon placeholder
- [ ] Each widget has label, number (large font), and icon (use simple emoji or SVG)
- [ ] Below widgets: table "5 Pemeriksaan Terbaru" showing same columns as list page (no action buttons)
- [ ] Table rows taken from Pemeriksaan::readWithJoin() with SQL LIMIT 5
- [ ] Status badge with color coding same as list page
- [ ] Quick link buttons: "Lihat Semua" redirect to each entity's list
- [ ] Responsive grid: 4 widgets in 1 row on desktop, 2x2 on tablet, stacked on mobile
- [ ] No search bar on dashboard
- [ ] Greeting: "Selamat Datang di SILK-Swarakarna" heading

## Test

```php
$pasienObj = new Pasien();
$dokterObj = new Dokter();
$layananObj = new Layanan();
$periksaObj = new Pemeriksaan();

$totalPasien = count($pasienObj->read());
$totalDokter = count($dokterObj->read());
$totalLayanan = count($layananObj->read());
$today = date('Y-m-d');
$allPeriksa = $periksaObj->readWithJoin();
$pemeriksaanHariIni = count(array_filter($allPeriksa, fn($p) => $p['tanggal_periksa'] == $today));
$latestPeriksa = array_slice($allPeriksa, 0, 5);

$pageTitle = 'Dashboard';
require 'views/layout/header.php';
require 'views/dashboard.php';
require 'views/layout/footer.php';
```

## Out of scope

- Charts / graphs
- Date range filter
- Revenue calculation
