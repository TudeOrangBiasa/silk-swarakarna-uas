# 10 — View Pasien List + Search

Status: ready-for-dev
Slug: 10-view-pasien-list
Depends: 04, 05, 06
Estimate: 1h

## Goal

Build pasien list page: table with all pasien data, search bar filtering by nama_pasien, action buttons (Edit, Hapus). No inline form.

## Files

- `views/pasien/index.php` (create)

## Acceptance

- [ ] Page receives `$pasien` array from Pasien::read() or Pasien::search()
- [ ] Table columns: No, No Rekam Medis, Nama Pasien, Tanggal Lahir, Jenis Kelamin, No HP, Aksi
- [ ] Search bar with input name="search", value retained after submit
- [ ] Form search uses GET method, submits to same page
- [ ] Aksi column: Edit link to pasien.edit&id=X, Hapus link to pasien.delete&id=X with JS confirm
- [ ] Shows "Tambah Pasien" button linking to pasien.create
- [ ] Table uses Tailwind striped rows: class="bg-white" / "bg-gray-50"
- [ ] Empty state: "Belum ada data pasien" message when $pasien empty
- [ ] Pagination not required (simple list for UAS scope)
- [ ] Date formatted: tanggal_lahir in d/m/Y format
- [ ] Jenis Kelamin: L → "Laki-laki", P → "Perempuan"

## Test

```php
// In router handler
$pasienObj = new Pasien();
$keyword = $_GET['search'] ?? '';
$pasien = $keyword ? $pasienObj->search($keyword) : $pasienObj->read();
$pageTitle = 'Data Pasien';
require 'views/layout/header.php';
require 'views/pasien/index.php';
require 'views/layout/footer.php';
```

## Out of scope

- Pagination
- Export CSV
- Bulk delete
