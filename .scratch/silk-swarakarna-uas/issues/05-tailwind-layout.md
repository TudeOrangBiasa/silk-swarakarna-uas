# 05 — Tailwind CSS + Layout

Status: ready-for-agent
Slug: 05-tailwind-layout
Depends: 04
Estimate: 1.5h

## Goal

Create Tailwind CSS file (compiled), header.php with navbar + sidebar, footer.php with script includes. Consistent layout all pages.

## Files

- `public/assets/css/tailwind.css` (create)
- `views/layout/header.php` (create)
- `views/layout/footer.php` (create)

## Acceptance

- [ ] tailwind.css loaded in header.php via `<link>`
- [ ] header.php opens `<html>`, `<head>` with meta charset + viewport + title dynamic from `$pageTitle` variable
- [ ] header.php contains navbar with logo "SILK-Swarakarna" and nav links: Dashboard, Pasien, Dokter, Layanan, Pemeriksaan
- [ ] Nav link active state highlights current page
- [ ] header.php starts `<body>` and main content `<div class="container mx-auto p-4">`
- [ ] footer.php closes container div + `<body>` + `<html>`
- [ ] footer.php includes `<script src="assets/js/main.js">` if file exists
- [ ] header.php checks `$pageTitle` default value if not set
- [ ] Use utility classes: bg-blue-600, text-white, rounded, shadow, hover effects
- [ ] Responsive: sidebar collapses on mobile (hamburger menu optional — at minimum stacked nav)

## Test

```php
// In router
$pageTitle = 'Daftar Pasien';
require 'views/layout/header.php';
require 'views/pasien/index.php';
require 'views/layout/footer.php';
// Renders full HTML page with nav + content + footer
```

## Out of scope

- JavaScript interactivity beyond nav toggle
- Custom fonts
- Dark mode
