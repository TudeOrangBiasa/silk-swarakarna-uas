# UI/UX designs

Design references buat implementor. Export dari Open CoDesign (atau tool lain), simpan HTML di folder ini.

## Design system

Baca [DESIGN.md](../../DESIGN.md) dulu. Itu spec of-record. Semua export dari Open CoDesign harus conform ke Bootstrap 5.3 + navy primary `#1e40af` + Bootstrap Icons + Bahasa Indonesia.

## Workflow

```
1. Buka Open CoDesign (desktop app)
2. Settings → Providers → OpenRouter → paste API key
3. Pilih model (Claude Sonnet recommended)
4. Prompt per halaman (lihat prompts.md)
5. Generate → preview → adjust kalau perlu
6. Export as HTML (inlined CSS, no JS framework needed)
7. Save ke subfolder yang sesuai dengan nama <page>.html
8. Commit
9. Reference saat implement view issue terkait
```

## Subfolder layout

| Subfolder | Isi |
|---|---|
| `layouts/` | Shell: header, footer, dashboard frame |
| `dashboards/` | Dashboard admin dengan widget |
| `crud/` | List + detail view (pasien, dokter, layanan, pemeriksaan) |
| `forms/` | Form create/edit per entity |
| `assets/` | Login page, error page, empty state components |

## File naming

| File | Halaman | Issue |
|---|---|---|
| `layouts/layout.html` | Master layout (header/footer/navbar) | #05 |
| `dashboards/dashboard.html` | Dashboard dengan widget | #18 |
| `crud/pasien-list.html` | List + search pasien | #10 |
| `forms/pasien-form.html` | Form create/edit pasien | #11 |
| `crud/dokter-list.html` | List + search dokter | #12 |
| `forms/dokter-form.html` | Form create/edit dokter | #13 |
| `crud/layanan-list.html` | List layanan | #14 |
| `forms/layanan-form.html` | Form create/edit layanan | #15 |
| `forms/pemeriksaan-create.html` | Form daftar pemeriksaan | #16 |
| `crud/pemeriksaan-list.html` | List + status badge pemeriksaan | #17 |

## Status badge (konsisten di semua design)

| Status | Bootstrap class | Contoh |
|---|---|---|
| Menunggu | `badge rounded-pill bg-warning text-dark` | badge kuning teks gelap |
| Sedang Diperiksa | `badge rounded-pill bg-info text-dark` | badge biru muda teks gelap |
| Selesai | `badge rounded-pill bg-success` | badge hijau teks putih |

## Bahasa

Semua text UI pakai Bahasa Indonesia (sesuai PRD). Lihat [CONTEXT.md](../../CONTEXT.md) untuk glossary.
