# Design system primer (impeccable teach output)

Jalankan prompt ini **pertama** di Open CoDesign. Output: 1 file HTML showcase design system (style guide). Setelah OK, jalankan per-page prompts di `prompts.md` (mereka reference system ini).

## Apa yang di-generate

1 file HTML standalone yang mendokumentasikan design system secara visual:
- Color tokens (swatch per token, hex + nama + use case)
- Typography scale (live preview tiap step)
- Spacing scale (visual bar per step)
- Component library (button, card, form input, table, badge, alert, navbar, footer)
- Pattern library (dashboard widget, list page, form page)
- Anti-pattern examples (what NOT to do, side by side with correct)

## Prompt untuk CoDesign

```
Generate 1 file HTML standalone: design system showcase untuk SILK-Swarakarna
(aplikasi admin klinik THT, Bahasa Indonesia).

== CONTEXT ==

Product: SILK-Swarakarna. Sistem Informasi Layanan Klinik Swarakarna.
Use case: admin klinik catat pasien, dokter, layanan, transaksi pemeriksaan THT.
Users: 1 role (admin / resepsionis klinik), pakai 8 jam/hari di desktop kantor.
Setting: ruang admin klinik, monitor 24-27 inch, cahaya lampu neon terang (pagi-sore),
aksen bahasa santai profesional (Bahasa Indonesia, tidak formal kaku, tidak terlalu casual).
Brand personality: tenang, terpercaya, helpful. Bukan flashy. Bukan SaaS template.

Anti-references (JANGAN tiru vibe dari):
- Generic medical SaaS (putih + teal + dashboard grid identik)
- Health-tech startup (ungu gradient, hero animation)
- Klinik app template di ThemeForest (card shadow tebal, ikon emoji, warna pastel campur aduk)
- Bootstrap default theme (primary blue #0d6efd, terlalu generic)
- AI-generated dashboards (4 metric card identik + chart default + sidebar gelap)

Strategic principles:
- Information density matters (admin scroll cepat, banyak baris per view)
- Repetition OK untuk list/table (bukan variety untuk variety sake)
- Action clarity matters (tombol primary jelas, danger jelas)
- Error states harus helpful (bukan hanya "error" generic)
- Mobile responsive tapi desktop-first (primary device = desktop)

== COLOR STRATEGY ==

Committed. Navy primary #1e40af carries ~30% of surface: navbar, primary button,
active link, focus ring, brand mark. Sisanya tinted neutrals.

Theme: light. Justified by physical scene: admin klinik indoor siang hari,
bukan dark mode monitoring. Body bg #f8f9fa (off-white tinted slightly warm,
bukan pure white).

Color tokens:
- Primary: #1e40af (navy), bg-primary, btn-primary, text-primary, focus ring
- Primary dark: #1e3a8a, hover state
- Body bg: #f8f9fa (tinted warm off-white), page background, NEVER pure #fff
- Surface: #ffffff, card, navbar inner, table thead
- Muted text: #6c757d, caption, helper, footer
- Border: #dee2e6, separator, input border
- Success: #198754, status Selesai, success alert
- Warning: #ffc107, status Menunggu, warning alert
- Info: #0dcaf0, status Sedang Diperiksa, info alert
- Danger: #dc3545, hapus button, error state

OKLCH note: target chroma di primary navy ~0.15, di neutrals ~0.005-0.01.
Pure #000 atau #fff DILARANG. Pakai tinted off-white.

== TYPOGRAPHY ==

Font: system sans-serif stack (no Google Fonts, no web font load).
Reasoning: zero build, zero latency, native OS feel, faster load.

Mono: system mono stack untuk ID (No Rekam Medis, No Transaksi).

Hierarchy via scale + weight, ratio >= 1.25 antar step:
- display-1 sampai display-6: angka widget (kalau dipakai), page hero
- h1 (2rem fw-semibold): page title utama
- h2 (1.5rem fw-semibold): section title besar
- h3 (1.25rem fw-semibold): card title, sub-section
- h4 (1rem fw-semibold): form section
- h5 (0.875rem fw-semibold): table head, label
- body (1rem): default
- small (0.875rem): caption, helper
- text-muted: secondary info

Line length: cap di 65-75ch untuk body text (form description, alert content).
Heading tidak di-cap karena biasanya pendek.

== SPACING ==

Base unit 4px. Bootstrap default p-1, p-2, p-3, p-4, p-5 = 4, 8, 16, 24, 48px.

Vary the rhythm, jangan uniform:
- Page wrapper: py-5 (generous breathing)
- Section gap: mb-4 (16px-24px)
- Card body: p-4 (16-24px)
- Form field gap: mb-3 (16px)
- Form actions: mt-4 (24px, extra space before action)
- Table cell: py-2 px-3 (tight untuk density)
- Navbar inner: px-3 py-2 (compact)
- Footer: py-3 (minimal)

Rhythm: tight in tables and navbar, generous in pages and forms.

== COMPONENTS ==

Library (semua Bootstrap 5.3 + minimal override):

Button:
- Primary: btn btn-primary (navy)
- Secondary: btn btn-outline-secondary
- Danger: btn btn-outline-danger atau btn btn-danger
- Success: btn btn-success (untuk "Selesai" action)
- Info: btn btn-info (untuk "Mulai" action)
- Small action: btn btn-sm + appropriate color
- Icon di kiri: <i class="bi bi-plus-lg"></i>

Card:
- Standard: card shadow-sm (subtle shadow, NOT shadow-lg)
- Header: card-header bg-white + h5/h6
- Body: card-body p-4
- Footer: card-footer bg-white (untuk form action)

Form:
- Label: form-label (above input, NOT floating label)
- Required indicator: <span class="text-danger">*</span>
- Input: form-control
- Select: form-select
- Textarea: form-control with rows attribute
- Error: class is-invalid + div.invalid-feedback d-block text-danger small
- Helper: div.form-text text-muted small (below input)
- Validation: HTML5 required + Bootstrap class was-validated

Table:
- Wrapper: <div class="table-responsive">
- Class: table table-hover align-middle
- Header: thead class="table-light"
- ID column: <code> untuk mono
- Numeric column: class="text-end"
- Row hover: bg-light (Bootstrap default)

Badge (status):
- Menunggu: <span class="badge rounded-pill bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Menunggu</span>
- Sedang Diperiksa: <span class="badge rounded-pill bg-info text-dark"><i class="bi bi-arrow-repeat"></i> Sedang Diperiksa</span>
- Selesai: <span class="badge rounded-pill bg-success"><i class="bi bi-check-circle-fill"></i> Selesai</span>

Alert:
- Success: alert alert-success (untuk flash success)
- Error: alert alert-danger (untuk flash error)
- Info: alert alert-info (untuk empty state hint)
- Warning: alert alert-warning

Navbar:
- Class: navbar navbar-expand-lg navbar-dark bg-primary shadow-sm
- Container: container
- Brand: navbar-brand fw-bold
- Toggler: data-bs-toggle="collapse" data-bs-target="#nav"
- Active link: class="nav-link active" + aria-current="page"
- Mobile: collapse otomatis di < 992px

Footer:
- Class: bg-white border-top mt-auto
- Inner: text-center small text-muted
- Padding: py-3

Empty state:
- Container: text-center py-5
- Icon: bi-* display-1 text-secondary opacity-50
- Text: text-muted mb-3
- Action: btn btn-primary

== MOTION ==

Minimal. Mostly Bootstrap built-in:
- Hover transition: 150ms ease-out (default Bootstrap)
- Focus ring: instant, no animation
- Collapse (navbar mobile): Bootstrap default slide
- Modal: tidak dipakai (use inline confirm)

NO:
- CSS layout animation (width, height, top, left transitions)
- Bounce, elastic, spring easing
- Parallax, scroll animation
- Loading spinner yang berputar-putar lama (cuma spinner Bootstrap default OK)

== ACCESSIBILITY ==

Baseline WCAG 2.1 AA:
- Color contrast: minimum 4.5:1 untuk body text, 3:1 untuk large text
- Focus visible: outline 2px solid primary atau box-shadow ring
- Keyboard nav: semua interactive element reachable via Tab
- Skip link: <a href="#main" class="visually-hidden-focusable">Skip to main content</a>
- ARIA labels: aria-label untuk icon-only button, aria-current untuk active nav
- Form labels: explicit <label for="id"> bukan placeholder-only
- Error association: aria-describedby pointing to error message
- Live region: aria-live="polite" untuk flash message
- Alt text: alt="" untuk decorative, alt="description" untuk informative

== ANTI-PATTERNS (IMPECCABLE BANS) ==

Match-and-refuse. Tolak kalau muncul:

Layout:
- Side-stripe borders (border-left atau border-right > 1px sebagai colored accent di card, list item, alert, callout)
- Identical card grid (4 widget card dengan icon + heading + text, semua sama persis)
- Card untuk SEMUA section (beberapa section cukup section heading + content, no card)
- Modal as first thought (exhaust inline / progressive disclosure dulu)
- Nested cards (card di dalam card)
- Container untuk semua (beberapa section tidak perlu container wrapper)

Visual:
- Tailwind class apapun (rounded-md, bg-blue-800, max-w-6xl, text-2xl, font-bold, gap-4, p-6, text-gray-500)
- Gradient text (background-clip: text + gradient)
- Glassmorphism (blur, glass card dekoratif)
- Hero-metric template (big number + small label + supporting stats + gradient accent)
- Box shadow berat (shadow-lg, shadow-xl pada card biasa)
- Emoji sebagai icon (pakai Bootstrap Icons)
- #000 atau #fff sebagai netral
- Inline style="" untuk visual

Copy:
- Em dash atau en dash (long atau medium horizontal line)
- English UI string
- Heading yang restate title page

== OUTPUT FORMAT ==

1 file HTML. CDN:
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

Max 1 style block inline (max 10 baris, hanya untuk override spesifik).
Max 1 script block inline (max 30 baris, untuk demo interaksi).
Sample data hardcoded, bukan real Presenter call.
Bahasa Indonesia untuk semua label dan helper text.

Struktur page:
1. Top heading: "SILK-Swarakarna Design System" + subtitle "v1, Bootstrap 5.3"
2. Section: Color (swatch per token)
3. Section: Typography (live preview tiap step)
4. Section: Spacing (visual bar per step)
5. Section: Components (button variants, card variants, form variants, table, badge, alert)
6. Section: Patterns (navbar, dashboard widget, list, form)
7. Section: Anti-patterns (side by side WRONG vs RIGHT, dengan caption)
8. Footer: "Generated via Open CoDesign, design rules by impeccable"

TOLAK kalau output:
- Pakai Tailwind class
- Ada em dash
- Pakai #000 atau #fff
- Pakai emoji icon
- Demo widget identik 4x (vary: 1 plain number, 1 dengan trend indicator, 1 dengan icon only, 1 dengan link)
- Card shadow tebal
- Gradient di mana-mana
```
