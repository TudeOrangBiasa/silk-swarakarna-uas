# UI/UX designs

Design references untuk implementor. Workflow: generate design system dulu, baru per-page design.

## Design system

Spec of-record: [DESIGN.md](../../DESIGN.md) (root). Baca dulu sebelum generate apapun.

Bootstrap 5.3 + Bootstrap Icons via CDN. Navy primary `#1e40af`. Bahasa Indonesia. Tidak ada build step.

## File struktur

| File | Tujuan |
|---|---|
| `system-prompt.md` | Design system primer (impeccable teach). Jalankan PERTAMA di CoDesign. Output: 1 HTML showcase design system. |
| `prompts.md` | 10 per-page design briefs. Jalankan SETELAH system OK. Output: 1 HTML per page. |
| `README.md` | File ini. Workflow + rules. |
| `layouts/` `dashboards/` `crud/` `forms/` `assets/` | Output dari prompts (HTML). |

## Workflow

1. Setup: install Open CoDesign di `~/Apps/open-codesign/` (AppImage).
2. Settings > Providers > OpenRouter > paste API key. Model: Claude Sonnet.
3. **Jalankan `system-prompt.md`** di CoDesign. Output: design system showcase (colors, typography, components, patterns, anti-patterns).
4. Review design system. Kalau ada yang tidak sesuai, tweak prompt + regenerate. Jangan lanjut sebelum system OK karena semua page reference system ini.
5. **Jalankan per-page prompt** dari `prompts.md` (1-10). CoDesign generate HTML berdasarkan system context.
6. Cek anti-patterns (section di bawah). Kalau ada violation, regenerate atau edit manual.
7. Save HTML ke subfolder sesuai tabel di bawah.
8. Commit + push.
9. Reference saat implement view issue terkait.

## Output format (semua HTML)

- HTML5 doctype, viewport meta, `lang="id"`
- `<link>` Bootstrap 5.3.3 CSS + Bootstrap Icons 1.11.3 dari jsdelivr CDN
- `<script>` Bootstrap 5.3.3 bundle (defer)
- Max 1 `<style>` block (override spesifik, ~10 baris)
- Max 1 `<script>` block (interactivity, ~30 baris)
- Sample data hardcoded (placeholder untuk Presenter call)
- Tidak ada npm, tidak ada build, tidak ada framework JS (no React, no Vue, no Alpine)

CDN (pakai persis di setiap HTML):

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
```

## File naming (per-page output)

| File | Halaman | Issue |
|---|---|---|
| `layouts/layout.html` | Master layout (header/footer/navbar) | #05 |
| `dashboards/dashboard.html` | Dashboard dengan 4 widget | #18 |
| `crud/pasien-list.html` | List + search pasien | #10 |
| `forms/pasien-form.html` | Form create/edit pasien | #11 |
| `crud/dokter-list.html` | List + search dokter | #12 |
| `forms/dokter-form.html` | Form create/edit dokter | #13 |
| `crud/layanan-list.html` | List layanan | #14 |
| `forms/layanan-form.html` | Form create/edit layanan | #15 |
| `forms/pemeriksaan-create.html` | Form daftar pemeriksaan | #16 |
| `crud/pemeriksaan-list.html` | List + status badge + quick actions | #17 |

## Anti-patterns (impeccable rules)

Detail lengkap di `system-prompt.md`. Ringkasan untuk cek cepat:

### Layout

- **Tailwind classes** apapun (rounded-md, max-w-6xl, text-2xl, font-bold, gap-4, p-6, text-gray-500). Pakai Bootstrap 5 equivalent.
- **Side-stripe borders** (border-left/right > 1px sebagai colored accent).
- **Identical card grids** (icon + heading + text, repeated). Vary.
- **Modal as first thought**. Inline confirmation cukup.

### Visual

- **Gradient text** (background-clip: text + gradient).
- **Glassmorphism** (blur, glass card dekoratif).
- **Hero-metric template** (big number + small label + supporting stats + gradient accent).
- **Emoji icon**. Pakai Bootstrap Icons.
- **`#000` atau `#fff` neutral**. Pakai `bg-body` (`#f8f9fa`).

### Copy

- **Em dash atau en dash** (long atau medium horizontal line).
- **English UI string**.
- **Heading yang restate title**.

### Code

- **Inline `style=""`** untuk visual. Pakai class atau `<style>` block.
- **Custom CSS file**.
- **Build step indication**.

## Color strategy

**Committed**: navy primary di ~30% surface. Sisanya tinted neutrals.

| Token | Hex | Bootstrap class | Pakai untuk |
|---|---|---|---|
| Primary | `#1e40af` | `bg-primary`, `btn-primary` | Navbar, primary button, active link |
| Surface | `#ffffff` | `bg-white` | Card, navbar inner, table head |
| Body | `#f8f9fa` | `bg-body` | Page background |
| Muted | `#6c757d` | `text-muted` | Caption, helper, footer |
| Border | `#dee2e6` | `border` | Separator, input border |
| Success | `#198754` | `bg-success` | Status Selesai |
| Warning | `#ffc107` | `bg-warning text-dark` | Status Menunggu |
| Info | `#0dcaf0` | `bg-info text-dark` | Status Sedang Diperiksa |
| Danger | `#dc3545` | `btn-danger` | Hapus button, error |

## Status badge (konsisten di semua design)

| Status | Class | Icon |
|---|---|---|
| Menunggu | `badge rounded-pill bg-warning text-dark` | `bi-hourglass-split` |
| Sedang Diperiksa | `badge rounded-pill bg-info text-dark` | `bi-arrow-repeat` |
| Selesai | `badge rounded-pill bg-success` | `bi-check-circle-fill` |

## Bahasa

Semua UI string wajib Bahasa Indonesia. Glossary di [CONTEXT.md](../../CONTEXT.md).
