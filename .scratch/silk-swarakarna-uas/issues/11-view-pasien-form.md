# 11 — View Pasien Create + Edit

Status: ready-for-agent
Slug: 11-view-pasien-form
Depends: 04, 05, 06, 10
Estimate: 1.5h

## Goal

Build create and edit forms for Pasien. Create shows empty form. Edit pre-fills from existing data. Both POST to same handler in router which calls Pasien::create() or Pasien::update(). Form fields match DB columns.

Fields: nama_pasien, tanggal_lahir, alamat (textarea), no_hp.

## Files

- `views/pasien/create.php` (create)
- `views/pasien/edit.php` (create)

## Acceptance

- [ ] create.php form action POST to pasien route (router detects no id → create)
- [ ] edit.php receives `$pasien` array, pre-fills all fields
- [ ] edit.php form includes hidden input for id_pasien
- [ ] Field: nama_pasien — text input, required, max 100
- [ ] Field: tanggal_lahir — date input, required, no future date
- [ ] Field: alamat — textarea, required
- [ ] Field: no_hp — text input, required, numeric, 10-15 digits
- [ ] Error messages displayed inline as red text above each field
- [ ] Success flash message displayed after redirect (use session flash)
- [ ] Form styled with Tailwind: w-full md:w-1/2, card shadow, proper label spacing
- [ ] Cancel button redirects to pasien list
- [ ] Old input retained on validation error ($_SESSION['old_input'])

## Test

```php
// In create.php
<form action="index.php?page=pasien" method="POST">
<input type="hidden" name="action" value="create">
<input type="text" name="nama_pasien" value="<?= $_SESSION['old_input']['nama_pasien'] ?? '' ?>">
<!-- ... -->
```

## Out of scope

- Photo upload
- Duplicate detection
- Autocomplete
