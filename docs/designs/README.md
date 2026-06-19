# UI/UX designs

Design references buat implementor. Export dari Open CoDesign (atau tool lain), simpan HTML di folder ini.

## Workflow

```
1. Buka Open CoDesign (desktop app)
2. Settings → Providers → OpenRouter → paste API key
3. Prompt per halaman (lihat prompts.md)
4. Export as HTML (inlined CSS)
5. Save ke folder ini dengan nama <page>.html
6. Commit
7. Reference saat implement view issues (#10-17, #18)
```

## File naming

| File | Halaman | Issue |
|---|---|---|
| `layout.html` | Master layout (header/footer) | #05 |
| `dashboard.html` | Dashboard dengan widget | #18 |
| `pasien-list.html` | List + search pasien | #10 |
| `pasien-form.html` | Form create/edit pasien | #11 |
| `dokter-list.html` | List + search dokter | #12 |
| `dokter-form.html` | Form create/edit dokter | #13 |
| `layanan-list.html` | List layanan | #14 |
| `layanan-form.html` | Form create/edit layanan | #15 |
| `pemeriksaan-create.html` | Form daftar pemeriksaan | #16 |
| `pemeriksaan-list.html` | List + status badge pemeriksaan | #17 |

## Status badge colors (konsisten di semua design)

| Status | Background | Text |
|---|---|---|
| Menunggu | yellow-100 | yellow-800 |
| Sedang Diperiksa | blue-100 | blue-800 |
| Selesai | green-100 | green-800 |

## Typography & color

- Font: sans-serif default (Tailwind: `font-sans`)
- Heading sizes: `text-2xl` (page title), `text-xl` (section), `text-lg` (card)
- Body: `text-sm` atau `text-base`
- Primary: `bg-blue-800` (`#1e40af`) + `text-white`
- Secondary: `bg-gray-200` + `text-gray-800`
- Danger: `bg-red-600` + `text-white`
- Success: `bg-green-600` + `text-white`
- Background page: `bg-gray-50`
- Card: `bg-white` + `shadow` + `rounded-lg`

## Bahasa

Semua text UI pakai Bahasa Indonesia (sesuai PRD).
