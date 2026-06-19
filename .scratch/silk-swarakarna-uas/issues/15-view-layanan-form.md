# 15 — View Layanan Create + Edit

Status: ready-for-agent
Slug: 15-view-layanan-form
Depends: 04, 05, 08, 14
Estimate: 1h

## Goal

Build create and edit forms for Layanan. Same pattern: create empty, edit pre-filled, POST handler.

Fields: nama_layanan, biaya (numeric rupiah).

## Files

- `views/layanan/create.php` (create)
- `views/layanan/edit.php` (create)

## Acceptance

- [ ] create.php form action POST to layanan route, hidden action=create
- [ ] edit.php receives `$layanan`, pre-fills fields, hidden input id_layanan
- [ ] Field: nama_layanan — text, required, max 100
- [ ] Field: biaya — number input, required, > 0, displayed without formatting
- [ ] Helper text: "Biaya dalam Rupiah (Rp)"
- [ ] Error messages inline red text
- [ ] Success flash on redirect to layanan list
- [ ] Cancel button → layanan list
- [ ] Old input retained on error
- [ ] Styled with Tailwind card layout

## Test

```php
// In create.php
<form action="index.php?page=layanan" method="POST">
<input type="hidden" name="action" value="create">
<input type="number" name="biaya" min="1" step="1"
       value="<?= $_SESSION['old_input']['biaya'] ?? '' ?>">
```

## Out of scope

- Discount logic
- Paket bundling
- Tax calculation
