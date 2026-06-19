# 14 — View Layanan List

Status: ready-for-agent
Slug: 14-view-layanan-list
Depends: 04, 05, 08
Estimate: 45m

## Goal

Build layanan list page: table with No, Nama Layanan, Biaya (formatted IDR), Aksi. No search needed — small dataset.

## Files

- `views/layanan/index.php` (create)

## Acceptance

- [ ] Page receives `$layanan` array from Layanan::read()
- [ ] Table columns: No, Nama Layanan, Biaya, Aksi
- [ ] Biaya formatted as Rp X.XXX (number_format with 0 decimals, . as thousand separator)
- [ ] "Tambah Layanan" button links to layanan.create
- [ ] Edit link to layanan.edit&id=X
- [ ] Hapus link to layanan.delete&id=X with JS confirm()
- [ ] Empty state: "Belum ada data layanan"
- [ ] Table striped rows Tailwind
- [ ] No search bar

## Test

```php
$layananObj = new Layanan();
$layanan = $layananObj->read();
$pageTitle = 'Data Layanan';
require 'views/layout/header.php';
require 'views/layanan/index.php';
require 'views/layout/footer.php';
```

## Out of scope

- Search
- Pagination
- Discount column
