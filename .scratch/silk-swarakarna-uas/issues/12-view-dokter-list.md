# 12 — View Dokter List

Status: ready-for-dev
Slug: 12-view-dokter-list
Depends: 04, 05, 07
Estimate: 45m

## Goal

Build dokter list page: table with columns No, Nama Dokter, No Izin Praktik, Spesialisasi, No HP, Aksi. Search bar optional (can be implemented same pattern as pasien).

## Files

- `views/dokter/index.php` (create)

## Acceptance

- [ ] Page receives `$dokter` array from Dokter::read() or Dokter::search()
- [ ] Table columns: No, Nama Dokter, No Izin Praktik, Spesialisasi, No HP, Aksi
- [ ] Search bar with input name="search", GET method
- [ ] "Tambah Dokter" button links to dokter.create
- [ ] Edit link to dokter.edit&id=X
- [ ] Hapus link to dokter.delete&id=X with JS confirm()
- [ ] Empty state: "Belum ada data dokter"
- [ ] Table striped rows Tailwind
- [ ] Search query passed to Dokter::search($keyword)

## Test

```php
$dokterObj = new Dokter();
$keyword = $_GET['search'] ?? '';
$dokter = $keyword ? $dokterObj->search($keyword) : $dokterObj->read();
$pageTitle = 'Data Dokter';
require 'views/layout/header.php';
require 'views/dokter/index.php';
require 'views/layout/footer.php';
```

## Out of scope

- Inline edit
- Availability schedule
