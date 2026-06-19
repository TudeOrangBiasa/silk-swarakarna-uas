# 07 — Dokter CRUD + Search

Status: ready-for-dev
Slug: 07-dokter-class
Depends: 02
Estimate: 1h

## Goal

Build Dokter class: CRUD + search by nama_dokter. No auto-code needed — id_dokter is auto-increment.

## Files

- `src/Dokter.php` (create)

## Acceptance

- [ ] Constructor gets Database singleton
- [ ] create($data) inserts dokter, returns id_dokter
- [ ] read() returns all dokter ordered by created_at DESC
- [ ] read($id) returns single dokter by id_dokter
- [ ] update($id, $data) updates dokter row, returns affected count
- [ ] delete($id) deletes dokter, returns bool (false if FK constraint fails)
- [ ] search($keyword) returns rows WHERE nama_dokter LIKE %keyword%

## Test

```php
$dokter = new Dokter();
$id = $dokter->create([
    'nama_dokter' => 'dr. Sari Wijaya, Sp.THT',
    'no_izin_praktik' => 'SIP-12345',
    'spesialisasi' => 'THT',
    'no_hp' => '081234567891'
]);
echo $id; // 1
$data = $dokter->read($id);
echo $data['spesialisasi']; // THT
```

## Out of scope

- Auto-code generation
- Multi-spesialisasi
