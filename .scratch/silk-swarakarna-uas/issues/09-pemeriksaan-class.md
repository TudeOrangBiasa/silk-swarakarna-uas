# 09 — Pemeriksaan Transaksi Class

Status: ready-for-dev
Slug: 09-pemeriksaan-class
Depends: 02, 06, 07, 08
Estimate: 2h

## Goal

Build Pemeriksaan class: transactional unit linking Pasien + Dokter + Layanan. Supports create with auto No Transaksi (TRX-YYYYNNN), readWithJoin for list with 3 master tables, updateStatus for status state machine, getById for single record.

## Files

- `src/Pemeriksaan.php` (create)

## Acceptance

- [ ] Constructor gets Database singleton
- [ ] create($data) inserts pemeriksaan with status_pemeriksaan = 'Menunggu', returns id_periksa
- [ ] create() calls generateKodeOtomatis() for id_periksa (No Transaksi) format TRX-YYYYNNN
- [ ] generateKodeOtomatis() queries MAX id_periksa for current year, pads sequence 3 digits, total 10 chars e.g. TRX-2026001
- [ ] generateKodeOtomatis() handles empty table for year → returns TRX-YYYY001
- [ ] readWithJoin() returns all rows with JOIN pasien, dokter, layanan, ordered by tanggal_periksa DESC
- [ ] readWithJoin($keyword) filters by p.nama_pasien LIKE %keyword%
- [ ] updateStatus($id, $newStatus) updates status_pemeriksaan, validates transition per state machine
- [ ] Valid transitions: Menunggu→Sedang Diperiksa, Menunggu→Selesai, Sedang Diperiksa→Selesai, Sedang Diperiksa→Menunggu
- [ ] Invalid transition (e.g. Selesai→Menunggu) throws exception
- [ ] getById($id) returns single pemeriksaan with JOIN data (nama_pasien, nama_dokter, nama_layanan, biaya)
- [ ] delete($id) deletes pemeriksaan only if status != 'Selesai', returns bool

## Test

```php
$periksa = new Pemeriksaan();
$newId = $periksa->create([
    'id_pasien' => 'RM-001',
    'id_dokter' => 1,
    'id_layanan' => 1,
    'tanggal_periksa' => '2026-06-19',
    'keluhan' => 'Sulit mendengar sejak 1 minggu'
]);
echo $newId; // TRX-2026001

$data = $periksa->getById($newId);
echo $data['id_periksa']; // TRX-2026001
echo $data['status_pemeriksaan']; // Menunggu

$periksa->updateStatus($id, 'Sedang Diperiksa');
$data = $periksa->getById($id);
echo $data['status_pemeriksaan']; // Sedang Diperiksa

// Invalid transition
$periksa->updateStatus($id, 'Menunggu'); // OK (revert allowed)
$periksa->updateStatus($id, 'Selesai'); // OK (skip)
// try { $periksa->updateStatus($id, 'Menunggu'); } catch  — throws, Selesai→Menunggu invalid
```

## Out of scope

- Invoice generation
- Payment tracking
- Email notification
