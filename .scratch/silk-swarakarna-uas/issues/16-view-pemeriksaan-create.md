# 16 — View Pemeriksaan Create Form

Status: ready-for-dev
Slug: 16-view-pemeriksaan-create
Depends: 04, 05, 09
Estimate: 1.5h

## Goal

Build create pemeriksaan form with 3 dropdowns (Pasien, Dokter, Layanan) populated from master data. Auto-populate dropdown options from respective ::read() methods.

Fields: id_pasien (select), id_dokter (select), id_layanan (select), tanggal_periksa (date), keluhan (textarea).

## Files

- `views/pemeriksaan/create.php` (create)

## Acceptance

- [ ] Form loads 3 dropdowns: Pasien (id_pasien→nama_pasien), Dokter (id_dokter→nama_dokter), Layanan (id_layanan→nama_layanan)
- [ ] Dropdown data passed as `$pasienList`, `$dokterList`, `$layananList` from Master::read()
- [ ] Dropdown default option: "-- Pilih Pasien --", "-- Pilih Dokter --", "-- Pilih Layanan --"
- [ ] Field: tanggal_periksa — date input, required, min = today
- [ ] Field: keluhan — textarea, required
- [ ] Form POST to pemeriksaan route, hidden action=create
- [ ] No edit form — Pemeriksaan cannot be edited after creation (only status changes)
- [ ] Error messages inline red
- [ ] Success flash on redirect to pemeriksaan list
- [ ] Cancel button → pemeriksaan list
- [ ] Form shows auto-generated No Transaksi preview after submit (display on success)
- [ ] Styled with Tailwind card, w-full md:w-2/3

## Test

```php
$pasienObj = new Pasien();
$dokterObj = new Dokter();
$layananObj = new Layanan();
$pasienList = $pasienObj->read();
$dokterList = $dokterObj->read();
$layananList = $layananObj->read();
$pageTitle = 'Tambah Pemeriksaan';
require 'views/layout/header.php';
require 'views/pemeriksaan/create.php';
require 'views/layout/footer.php';
```

```html
<!-- Dropdown structure -->
<select name="id_pasien" required>
    <option value="">-- Pilih Pasien --</option>
    <?php foreach ($pasienList as $p): ?>
        <option value="<?= $p['id_pasien'] ?>"><?= $p['id_pasien'] ?> — <?= $p['nama_pasien'] ?></option>
    <?php endforeach; ?>
</select>
```

## Out of scope

- Edit form (Pemeriksaan no edit per domain rules)
- Multiple layanan per pemeriksaan
- Pricing display
