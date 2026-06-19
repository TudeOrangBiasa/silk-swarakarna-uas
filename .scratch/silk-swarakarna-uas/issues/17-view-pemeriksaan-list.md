# 17 — View Pemeriksaan List + Quick Actions

Status: ready-for-agent
Slug: 17-view-pemeriksaan-list
Depends: 04, 05, 09, 16
Estimate: 1.5h

## Goal

Build pemeriksaan list page with JOIN data from 3 master tables. Show status badge with color coding. Quick action buttons to advance status. Search by pasien name.

## Files

- `views/pemeriksaan/index.php` (create)
- `views/pemeriksaan/update_status.php` (create)

## Acceptance

- [ ] Page receives `$pemeriksaan` array from Pemeriksaan::readWithJoin()
- [ ] Table columns: No, No Transaksi, Tanggal Periksa, Pasien, Dokter, Layanan, Biaya, Keluhan, Status, Aksi
- [ ] Status displayed as badge: Menunggu → yellow, Sedang Diperiksa → blue, Selesai → green
- [ ] Quick action buttons:
  - Menunggu → "Mulai" (updateStatus to Sedang Diperiksa)
  - Sedang Diperiksa → "Selesai" (updateStatus to Selesai)
  - Selesai → no action (badge only, disabled)
- [ ] Quick action form POST to pemeriksaan.update_status&id=X
- [ ] update_status.php receives POST, calls Pemeriksaan::updateStatus(), redirects to list
- [ ] Search bar filters by nama_pasien using readWithJoin($keyword)
- [ ] Delete button with JS confirm on rows where status != Selesai
- [ ] "Tambah Pemeriksaan" button links to pemeriksaan.create
- [ ] Biaya formatted as Rp X.XXX
- [ ] Empty state: "Belum ada data pemeriksaan"
- [ ] Keluhan truncated to 50 chars in table, full text on hover/title attribute
- [ ] Table sorted by tanggal_periksa DESC
- [ ] Striped rows Tailwind

## Test

```php
$periksaObj = new Pemeriksaan();
$keyword = $_GET['search'] ?? '';
$pemeriksaan = $keyword ? $periksaObj->readWithJoin($keyword) : $periksaObj->readWithJoin();
$pageTitle = 'Data Pemeriksaan';
require 'views/layout/header.php';
require 'views/pemeriksaan/index.php';
require 'views/layout/footer.php';
```

```php
// Status badge helper
$badgeClass = match($row['status_pemeriksaan']) {
    'Menunggu' => 'bg-yellow-100 text-yellow-800',
    'Sedang Diperiksa' => 'bg-blue-100 text-blue-800',
    'Selesai' => 'bg-green-100 text-green-800',
};
```

## Out of scope

- Bulk status update
- Print invoice
- Detail modal
