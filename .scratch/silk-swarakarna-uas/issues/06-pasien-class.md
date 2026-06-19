# 06 — Pasien CRUD + Kode Otomatis

Status: ready-for-agent
Slug: 06-pasien-class
Depends: 02
Estimate: 1.5h

## Goal

Build Pasien class: CRUD operations + auto-generate No Rekam Medis (RM-XXX) + search by nama_pasien.

## Files

- `src/Pasien.php` (create)

## Acceptance

- [ ] Constructor gets Database singleton via getInstance()
- [ ] create($data) inserts new pasien, returns id_pasien
- [ ] create() calls generateKodeOtomatis() before insert, assigns result to id_pasien (No Rekam Medis)
- [ ] read() returns all pasien ordered by created_at DESC
- [ ] read($id) returns single pasien by id_pasien
- [ ] update($id, $data) updates pasien row, returns affected row count
- [ ] delete($id) deletes pasien by id_pasien, returns bool (false if FK constraint fails)
- [ ] search($keyword) returns rows WHERE nama_pasien LIKE %keyword%
- [ ] generateKodeOtomatis() queries MAX id_pasien, formats RM-XXX zero-padded 3 digit, returns "RM-001" format
- [ ] generateKodeOtomatis() handles empty table → returns RM-001

## Test

```php
$pasien = new Pasien();
$id = $pasien->create([
    'nama_pasien' => 'Budi Santoso',
    'tanggal_lahir' => '1990-05-12',
    'alamat' => 'Jl. Merdeka No.1',
    'no_hp' => '081234567890'
]);
$data = $pasien->read($pasien->generateKodeOtomatis());
echo $data['id_pasien']; // RM-001
echo $pasien->generateKodeOtomatis(); // RM-002
```

## Out of scope

- Validation (handled in views)
- Export
- Bulk operations
