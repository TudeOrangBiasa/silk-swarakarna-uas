# 08 — Layanan CRUD

Status: ready-for-dev
Slug: 08-layanan-class
Depends: 02
Estimate: 1h

## Goal

Build Layanan class: CRUD operations. No search method needed (small dataset). Biaya stored as integer (IDR).

## Files

- `src/Layanan.php` (create)

## Acceptance

- [ ] Constructor gets Database singleton
- [ ] create($data) inserts layanan, returns id_layanan
- [ ] create() casts biaya to integer
- [ ] read() returns all layanan ordered by created_at DESC
- [ ] read($id) returns single layanan by id_layanan
- [ ] update($id, $data) updates layanan row, returns affected count
- [ ] update() casts biaya to integer
- [ ] delete($id) deletes layanan, returns bool (false if FK constraint fails)

## Test

```php
$layanan = new Layanan();
$id = $layanan->create([
    'nama_layanan' => 'Audiometri',
    'biaya' => '150000'
]);
echo $id; // 1
$data = $layanan->read($id);
echo $data['biaya']; // 150000 (integer)
```

## Out of scope

- Search
- Discount logic
- Bundled packages
