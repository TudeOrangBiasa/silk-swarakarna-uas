# 13 — View Dokter Create + Edit

Status: ready-for-dev
Slug: 13-view-dokter-form
Depends: 04, 05, 07, 12
Estimate: 1h

## Goal

Build create and edit forms for Dokter. Same pattern as Pasien forms: create empty, edit pre-filled, POST to router handler.

Fields: nama_dokter, no_izin_praktik, spesialisasi (text input, default "THT"), no_hp.

## Files

- `views/dokter/create.php` (create)
- `views/dokter/edit.php` (create)

## Acceptance

- [ ] create.php form action POST to dokter route, hidden action=create
- [ ] edit.php receives `$dokter`, pre-fills fields, hidden input id_dokter
- [ ] Field: nama_dokter — text, required, max 100
- [ ] Field: no_izin_praktek (note: column is no_izin_praktik) — text, required
- [ ] Field: spesialisasi — text, required, default value "THT"
- [ ] Field: no_hp — text, required, numeric 10-15 digits
- [ ] Error messages inline red text
- [ ] Success flash on redirect to dokter list
- [ ] Cancel button → dokter list
- [ ] Old input retained on error
- [ ] Styled with Tailwind card layout

## Test

```php
// In create.php
<form action="index.php?page=dokter" method="POST">
<input type="hidden" name="action" value="create">
```

## Out of scope

- Multi-spesialisasi
- Photo upload
- Jadwal praktik
