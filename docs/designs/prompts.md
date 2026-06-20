# Per-page design prompts untuk Open CoDesign

10 prompt, satu per view. Setiap prompt fokus pada **UI/UX design** (user flow, states, layout, components, anti-patterns) + **implementation guidance** (sample data, JS interactivity, output spec).

**Sebelum menjalankan prompt ini**, jalankan dulu [system-prompt.md](system-prompt.md) di CoDesign. Prompt ini reference design system dari situ.

## Cara pakai

1. Buka Open CoDesign
2. Jalankan system-prompt.md → review design system showcase → iterate sampai OK
3. Untuk setiap page di bawah, mulai conversation baru, paste prompt
4. CoDesign generate HTML berdasarkan system context + prompt
5. Review output, cek anti-patterns
6. Save HTML ke path yang ditentukan

## Daftar prompt

| # | File | Halaman | Issue |
|---|---|---|---|
| 1 | `layouts/layout.html` | Master layout (header/footer/navbar) | #05 |
| 2 | `dashboards/dashboard.html` | Dashboard dengan 4 widget | #18 |
| 3 | `crud/pasien-list.html` | List + search pasien | #10 |
| 4 | `forms/pasien-form.html` | Form create/edit pasien | #11 |
| 5 | `crud/dokter-list.html` | List + search dokter | #12 |
| 6 | `forms/dokter-form.html` | Form create/edit dokter | #13 |
| 7 | `crud/layanan-list.html` | List layanan | #14 |
| 8 | `forms/layanan-form.html` | Form create/edit layanan | #15 |
| 9 | `forms/pemeriksaan-create.html` | Form daftar pemeriksaan | #16 |
| 10 | `crud/pemeriksaan-list.html` | List + status badge + quick actions | #17 |

---

## 1. Layout (master shell)

File: `layouts/layout.html`. Issue: #05.

### Context

Admin klinik buka SILK-Swarakarna pertama kali di pagi hari. Butuh shell tenang, navigasi antar 5 menu utama, anchor untuk semua page. Calm, professional, not flashy.

### User flow

1. User masuk ke aplikasi → shell render dengan current page di-highlight
2. Click nav link → navigate ke page lain (no full reload, soft transition)
3. Resize window ke mobile → navbar collapse ke hamburger
4. User scroll page → navbar sticky di top, footer tetap di bottom

### States

- Default: navbar solid bg-primary, brand text visible, all 5 links visible
- Active link: bg sedikit darker + 2px bottom underline, aria-current="page"
- Hover link: subtle bg change (Bootstrap default)
- Mobile (< 992px): hamburger toggle, menu collapse
- Focus: visible focus ring (2px navy)

### Layout

Vary spacing, jangan uniform:
- Navbar: tight (px-3 py-2 inner, brand 5 fw-bold)
- Main: generous breathing (container py-5)
- Footer: minimal (py-3)

Hierarchy: brand > nav > page title (di main) > body content.

### Components

Lihat system-prompt.md untuk full spec. Yang dipakai di sini:
- Navbar: navbar-expand-lg navbar-dark bg-primary shadow-sm
- Brand: navbar-brand fw-bold
- Nav links: nav-link + active state
- Toggler: data-bs-toggle="collapse" data-bs-target="#nav"
- Container: container (max 960px)
- Footer: bg-white border-top mt-auto py-3 text-center small text-muted

### Sample content

Brand: "SILK-Swarakarna" + small subtitle "Klinik THT" (opacity 75%, fs-7 or smaller)
Nav links: Dashboard, Pasien, Dokter, Layanan, Pemeriksaan
Current: Dashboard (active)
Footer: "2026 SILK-Swarakarna, UAS Pemrograman Web Pagi 01"

### Anti-patterns

- Hero-metric di navbar (no big number, no stats, no chart)
- Identical nav link styling (active harus visually distinct)
- Brand pakai logo image placeholder (text only)
- border-left colored accent di nav
- Em dash di mana pun
- Tailwind class

### Responsive

- Desktop (>= 992px): horizontal nav
- Tablet/mobile (< 992px): hamburger toggle, vertical nav saat expanded

### Accessibility

- Skip link ke #main
- aria-current="page" di active link
- aria-label="Toggle navigation" di hamburger
- Focus visible di semua interactive element
- Color contrast: white di navy (>= 7:1, OK)

### Output

1 file HTML dengan include Bootstrap 5 CDN (lihat system-prompt.md untuk snippet).
Body main: placeholder heading "Konten Halaman" + 1 paragraf dummy.
Max 1 style block (untuk subtitle opacity, max 5 baris).
No JS needed (semua handled Bootstrap built-in).

---

## 2. Dashboard

File: `dashboards/dashboard.html`. Issue: #18.

### Context

Admin buka pagi hari untuk lihat antrian. Butuh 4 angka glanceable di atas (no scroll), lalu tabel ringkas 5 baris. Clean, dense, no chart.

### User flow

1. User buka `/` → dashboard render dengan 4 widget + tabel
2. User scan 4 angka untuk tahu kondisi klinik hari ini
3. User scroll sedikit ke bawah → lihat 5 pemeriksaan terbaru
4. Click salah satu row atau "Lihat semua" → ke halaman detail/list

### States

- Default: 4 widget tampil, tabel populated dengan 5 baris sample
- Empty (no data): widget angka = 0, tabel empty state
- Loading (future): skeleton (tidak dipakai di v1)
- Hover row: bg-light (Bootstrap default)
- Focus widget: subtle ring

### Layout

Vary the widget presentation, JANGAN 4 card identik:

Option A (recommended):
- 2 widget dengan border tipis (border-top 3px primary) + content horizontal
- 2 widget plain: angka besar di kiri, icon kecil di kanan, no card wrapper
- Total: visual rhythm berbeda, bukan 4 box sama

Hierarchy:
- H1 page title: "Dashboard" + subtitle "Senin, 19 Juni 2026" (text-muted small)
- Widget angka: h3 fw-bold (24px) + label small text-uppercase text-muted
- Section heading: "Pemeriksaan Terbaru" h5 mb-3

Spacing:
- Page top: py-5
- Section gap: mb-4
- Widget: py-2 (tight)
- Table: standard (py-2 px-3 cells)

### Components

- Widget angka: h3 fw-bold + small text-uppercase text-muted + bi-* icon
- Tabel: table table-hover align-middle + thead table-light
- Quick link: text-end, btn btn-link atau text-primary
- Status badge: sesuai spec (bg-warning bg-info bg-success rounded-pill)

### Sample content

Widget:
- Total Pasien: 42, icon bi-people-fill
- Total Dokter: 5, icon bi-clipboard-pulse
- Total Layanan: 8, icon bi-clipboard-data
- Pemeriksaan Hari Ini: 7, icon bi-calendar-check

Tabel 5 baris:
- TRX-2026001, 19/06/2026, Andi Pratama, dr. Sari Wijaya, Audiometri, badge Menunggu
- TRX-2026002, 19/06/2026, Siti Aminah, dr. Budi Santoso, BERA, badge Sedang Diperiksa
- TRX-2026003, 18/06/2026, Budi Santoso, dr. Sari Wijaya, Timpanometri, badge Selesai
- TRX-2026004, 18/06/2026, Dewi Lestari, dr. Rina Kusuma, OAE, badge Menunggu
- TRX-2026005, 17/06/2026, Rudi Hermawan, dr. Ahmad Fauzi, ASSR, badge Sedang Diperiksa

### Anti-patterns

- 4 card identik dengan shadow-lg (vary presentasi)
- Hero-metric template (big number + gradient + supporting stats)
- Chart (out of scope v1)
- Card shadow tebal di widget
- border-left colored accent di widget atau row
- Gradient text di angka widget
- Identical row styling tanpa variation

### Responsive

- Desktop: 4 widget per row (col-lg-3)
- Tablet: 2 widget per row (col-md-6)
- Mobile: 1 widget per row (col-12)

### Accessibility

- Widget angka: aria-label="Total Pasien: 42"
- Table: thead dengan <th scope="col">
- Status badge: aria-label="Status Menunggu"

### Output

1 file HTML dengan include layout shell (paste shell dari prompt #1 inline atau reference pattern).
CDN sama dengan system-prompt.md.

---

## 3. Pasien list

File: `crud/pasien-list.html`. Issue: #10.

### Context

Admin scroll mencari pasien tertentu, search by nama atau No Rekam Medis. Tabel dense, banyak baris, harus scannable. Live search filter.

### User flow

1. User buka `/pasien` → tabel render dengan semua pasien
2. User ketik di search bar → tabel filter real-time (no submit)
3. User click Edit → navigate ke form edit
4. User click Hapus → confirm dialog → submit ke delete handler
5. User click Tambah → navigate ke form create
6. User tekan Tab → focus visible di next interactive element

### States

- Default: tabel populated, search bar empty
- Typing: filter real-time, hanya row yang match tampil
- No results: empty state "Belum ada data pasien" + tombol Tambah
- Hover row: bg-light
- Hover tombol: subtle change
- Focus: visible ring
- Disabled: tidak ada di v1

### Layout

Hierarchy:
- Top bar: judul "Data Pasien" h2 fw-semibold kiri + tombol "Tambah Pasien" btn-primary kanan
- Search bar: max-w 400px, full-width di mobile
- Tabel: full-width, sticky thead saat scroll (optional)
- Empty state: text-center py-5

Spacing:
- Top bar: mb-4
- Search: mb-3
- Table: standard
- Empty state: py-5

### Components

- Tombol primary: btn btn-primary dengan icon bi-plus-lg
- Search: input-group dengan input-group-text bg-white + bi-search icon
- Tabel: table table-hover align-middle + thead table-light + table-responsive wrapper
- Edit: btn btn-sm btn-outline-secondary + bi-pencil
- Hapus: btn btn-sm btn-outline-danger + bi-trash + onclick return confirm()
- ID column: <code> untuk mono
- No HP column: text-body
- JK: "Laki-laki" / "Perempuan" (mapped dari L/P)

### Sample content

8 baris sample (lihat system-prompt untuk nama Indonesia realistic):
1. RM-001, Andi Pratama, 15/03/1985, Laki-laki, 081234567890
2. RM-002, Siti Aminah, 22/07/1990, Perempuan, 081234567891
3. RM-003, Budi Santoso, 10/11/1978, Laki-laki, 081234567892
4. RM-004, Dewi Lestari, 05/05/1992, Perempuan, 081234567893
5. RM-005, Rudi Hermawan, 30/08/1988, Laki-laki, 081234567894
6. RM-006, Ani Rahmawati, 12/04/1995, Perempuan, 081234567895
7. RM-007, Fajar Nugroho, 18/09/1982, Laki-laki, 081234567896
8. RM-008, Maya Sari, 25/12/1991, Perempuan, 081234567897

### JS interactivity (live search)

```js
document.querySelector('input[name=search]')?.addEventListener('input', function(e) {
  const q = e.target.value.toLowerCase();
  let visible = 0;
  document.querySelectorAll('tbody tr.data').forEach(row => {
    const match = row.textContent.toLowerCase().includes(q);
    row.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  document.getElementById('empty').style.display = visible === 0 ? 'block' : 'none';
});
```

Tambah class "data" ke semua <tr> sample. Empty state div id="empty" style="display:none".

### Anti-patterns

- Card wrapper untuk tabel
- border-left colored accent di row
- Pagination (out of scope UAS, < 1000 rows)
- Export button
- Bulk delete
- Sortable column header
- Gradient text / glassmorphism
- Em dash

### Responsive

- Desktop: tabel full-width dengan 7 columns
- Tablet: tabel dengan horizontal scroll (table-responsive)
- Mobile: tabel horizontal scroll + search full-width

### Accessibility

- Search input: aria-label="Cari pasien"
- Edit/Hapus buttons: aria-label masing-masing
- Tabel: <th scope="col">
- Empty state: role="status" aria-live="polite"

### Output

1 file HTML. Include layout shell.
CDN sesuai system-prompt.
Max 1 style block + 1 script block.

---

## 4. Pasien form

File: `forms/pasien-form.html`. Issue: #11.

### Context

Admin input pasien baru sambil berdiri di meja resepsionis. Butuh form jelas, touch target besar, validasi inline real-time. Bahasa Indonesia.

### User flow

1. User buka `/pasien/create` → form kosong, focus di field pertama
2. User isi field satu per satu, validasi real-time per field
3. User submit → jika valid, simpan dan redirect ke list dengan flash success
4. User submit → jika invalid, error per field, fokus ke field error pertama
5. User click Batal → kembali ke list tanpa simpan

### States per field

- Default: form-control normal
- Focus: ring primary subtle
- Filled: form-control normal dengan value
- Valid: subtle green check (optional, tidak over-engineer)
- Invalid: border-danger + invalid-feedback d-block text-danger small
- Required: asterisk merah di label
- Disabled: tidak ada di v1

### Form-level states

- Pristine: no validation run
- Validating: real-time saat user blur atau input
- Submitted with error: tampilkan semua error
- Submitting: tidak ada (sync, no async di v1)

### Layout

Hierarchy:
- Page title: "Tambah Pasien" atau "Edit Pasien" h2 fw-semibold mb-4
- Subtitle (edit mode only): "Perubahan data pasien RM-001" text-muted small
- Form card: max-w 720px, bg-white, border, rounded, p-4
- Field groups: vertical stack, mb-3 per field
- Form actions: d-flex gap-2 justify-content-end mt-4

Vary spacing:
- Form card: p-4 (generous inside card)
- Field: mb-3 (consistent)
- Action area: mt-4 (extra space before submit)

### Components

- Label: form-label + required indicator <span class="text-danger">*</span>
- Input: form-control dengan type sesuai
- Radio: form-check-inline untuk JK
- Textarea: form-control dengan rows="3"
- Error: class is-invalid + div.invalid-feedback d-block
- Helper: div.form-text text-muted small
- Tombol Simpan: btn btn-primary + icon bi-check-lg
- Tombol Batal: btn btn-outline-secondary + icon bi-x-lg

### Field spec

| Field | Type | Required | Validation | Helper |
|---|---|---|---|---|
| Nama Pasien | text | yes | maxlength 100 | "Contoh: Budi Santoso" placeholder |
| Tanggal Lahir | date | yes | max=today | - |
| Jenis Kelamin | radio (L/P) | yes | - | - |
| No HP | tel | yes | pattern [0-9]{10,15} | "10-15 digit angka" |
| Alamat | textarea | yes | - | 3 rows |

### Sample content

Mode Create: semua field empty, autofocus di Nama
Mode Edit: pre-filled dengan sample data (RM-001, Andi Pratama, 15/03/1985, L, 081234567890, "Jl. Merdeka No. 1, Denpasar")

### JS interactivity (validation)

```js
document.querySelector('form.needs-validation')?.addEventListener('submit', function(e) {
  if (!this.checkValidity()) {
    e.preventDefault();
    e.stopPropagation();
    const firstInvalid = this.querySelector(':invalid');
    firstInvalid?.focus();
  }
  this.classList.add('was-validated');
});
```

Form harus punya class="needs-validation" dan attribute novalidate.

### Anti-patterns

- Form inline horizontal (vertical only, kecuali radio JK)
- Modal untuk form
- Multi-step wizard
- File upload
- Duplicate detection
- Floating label
- border-left colored accent di form
- Custom font

### Responsive

- Desktop: form max-w 720px center
- Mobile: form full-width, padding reduced
- Touch target: min 44px (Bootstrap form-control default ~38px, bisa ditambah py-3 untuk form-control-lg)

### Accessibility

- Label for setiap input
- aria-required="true" untuk required field
- aria-describedby pointing ke helper/error
- Focus visible
- Error announcement: aria-live="polite" di error container

### Output

1 file HTML. Include layout shell.
CDN sesuai system-prompt.

---

## 5. Dokter list

File: `crud/dokter-list.html`. Issue: #12.

### Context

Admin cari dokter untuk di-link ke pemeriksaan. Butuh list ringkas dengan search by nama atau spesialisasi. Padat, scannable.

### User flow, States, Layout, Components, JS, Anti-patterns, Responsive, Accessibility, Output

Sama persis dengan Pasien list (prompt #3). Perbedaan:

### Sample content

5 baris sample:
1. dr. Sari Wijaya, Sp.THT, SIP-12345, THT, 081234567891
2. dr. Budi Santoso, Sp.THT-KL, SIP-12346, THT-KL, 081234567892
3. dr. Rina Kusuma, Sp.THT, SIP-12347, THT, 081234567893
4. dr. Ahmad Fauzi, Sp.THT-KL, SIP-12348, THT-KL, 081234567894
5. dr. Linda Marlina, Sp.THT, SIP-12349, THT, 081234567895

### Field berbeda

- Judul: "Data Dokter"
- Tombol: "Tambah Dokter" (link ke /dokter/create)
- Search placeholder: "Cari nama dokter atau spesialisasi..."
- Tabel columns: No, Nama Dokter, No Izin Praktik (font-mono), Spesialisasi, No HP, Aksi
- Empty state: "Belum ada data dokter"

### Output

1 file HTML. Include layout shell.

---

## 6. Dokter form

File: `forms/dokter-form.html`. Issue: #13.

Sama pattern dengan Pasien form (prompt #4). Perbedaan:

### Field spec

| Field | Type | Required | Validation | Helper / Default |
|---|---|---|---|---|
| Nama Dokter | text | yes | maxlength 100 | "Contoh: dr. Sari Wijaya, Sp.THT" placeholder |
| No Izin Praktik | text | yes | maxlength 50 | "SIP-12345/2024" placeholder |
| Spesialisasi | text | yes | maxlength 100 | default value "THT" pre-filled |
| No HP | tel | yes | pattern [0-9]{10,15} | "10-15 digit angka" |

### Sample content

Mode Create: pre-filled Spesialisasi = "THT", field lain empty
Mode Edit: pre-filled semua (dr. Sari Wijaya, Sp.THT, SIP-12345, THT, 081234567891)

### Output

1 file HTML. Include layout shell.

---

## 7. Layanan list

File: `crud/layanan-list.html`. Issue: #14.

### Context

Admin cek harga layanan sebelum daftarkan pemeriksaan. List pendek (max ~10 row), tidak perlu search. Ringkas, monetary info dominant.

### User flow

1. User buka `/layanan` → tabel render
2. User scan harga untuk konfirmasi sebelum daftarkan pemeriksaan
3. User click Edit / Hapus / Tambah sesuai kebutuhan

### States

Sama dengan list lain, plus:
- No search state (tidak ada)

### Layout

Sama dengan list lain, plus:
- Tidak ada search bar (dataset kecil)
- Biaya column: text-end, fw-semibold untuk emphasis monetary
- Nama Layanan column: bisa panjang, pertimbangkan truncation dengan title attribute

### Components

Sama dengan list lain, plus:
- Biaya format: "Rp 250.000" (titik thousand separator)
- text-end di column Biaya

### Sample content

5 baris sample:
1. Audiometri, Rp 250.000
2. OAE (Otoacoustic Emission), Rp 350.000
3. BERA (Brainstem Evoked Response), Rp 500.000
4. Timpanometri, Rp 200.000
5. ASSR (Auditory Steady State), Rp 600.000

### Anti-patterns

- Search bar
- Pagination
- Filter by harga
- Discount column
- Card wrapper
- Border-left colored accent

### Responsive, Accessibility, Output

Sama dengan list lain.

---

## 8. Layanan form

File: `forms/layanan-form.html`. Issue: #15.

Sama pattern dengan form lain. Perbedaan:

### Field spec

| Field | Type | Required | Validation | Helper |
|---|---|---|---|---|
| Nama Layanan | text | yes | maxlength 100 | "Contoh: Audiometri" placeholder |
| Biaya | number | yes | min=1 step=1000 | "Dalam rupiah, tanpa titik atau koma. Contoh: 250000" |

### Sample content

Mode Create: empty
Mode Edit: pre-filled (Audiometri, 250000)

### Anti-patterns

- Discount logic
- Currency converter
- Tax calculation

### Output

1 file HTML. Include layout shell.

---

## 9. Pemeriksaan form (create)

File: `forms/pemeriksaan-create.html`. Issue: #16.

### Context

Admin daftarkan pasien untuk pemeriksaan. Pilih dari 3 master (pasien, dokter, layanan) lalu isi tanggal + keluhan. Dropdown searchable untuk UX yang lebih baik saat list panjang.

### User flow

1. User buka `/pemeriksaan/create` → form dengan 3 dropdown + tanggal + keluhan
2. User ketik di dropdown searchable → option ter-filter real-time
3. User pilih dari hasil filter → value tersimpan di hidden input
4. User isi tanggal (default today) + keluhan
5. User submit → simpan + generate id_periksa otomatis, redirect ke list dengan flash

### States

- Default: dropdown dengan placeholder "-- Pilih --", tanggal default today
- Search dropdown: filter option real-time, no result = "Tidak ada hasil"
- Selected: option highlighted
- Required: asterisk merah
- Error: is-invalid + invalid-feedback
- Submit: btn-primary "Daftarkan"

### Layout

Hierarchy:
- Page title: "Daftar Pemeriksaan" h2 fw-semibold
- Subtitle: text-muted small "No Transaksi dibuat otomatis (format TRX-YYYYNNN)" + "Hari ini: Senin, 19 Juni 2026"
- Form card: max-w 960px (lebih lebar dari form lain karena 3 master)
- Section grouping dengan h6 text-uppercase text-muted sebagai subtle divider:
  - "Data Pasien & Dokter" section
  - "Jadwal" section

Spacing vary:
- Page: py-5
- Form card: p-4
- Section: mb-4
- Field within section: mb-3
- Action: mt-4

### Components

- 3 select: form-select dengan data-searchable attribute
- Date: input type=date, min=today
- Textarea: form-control, 4 rows
- Section heading: h6 text-uppercase text-muted fw-semibold

### Sample dropdown options

Pasien: RM-001, Andi Pratama | RM-002, Siti Aminah | RM-003, Budi Santoso | RM-004, Dewi Lestari | RM-005, Rudi Hermawan
Dokter: dr. Sari Wijaya, Sp.THT (THT) | dr. Budi Santoso, Sp.THT-KL (THT-KL) | dr. Rina Kusuma, Sp.THT (THT)
Layanan: Audiometri (Rp 250.000) | OAE (Rp 350.000) | BERA (Rp 500.000) | Timpanometri (Rp 200.000)

### JS (searchable dropdown dengan datalist)

```js
document.querySelectorAll('select[data-searchable]').forEach(sel => {
  const list = document.createElement('datalist');
  list.id = sel.id + '_list';
  Array.from(sel.options).forEach(opt => {
    if (opt.value) {
      const d = document.createElement('option');
      d.value = opt.textContent;
      list.appendChild(d);
    }
  });
  const input = document.createElement('input');
  input.type = 'text';
  input.className = 'form-select';
  input.setAttribute('list', list.id);
  input.name = sel.name;
  input.required = sel.required;
  input.placeholder = sel.options[0]?.textContent || '';
  sel.parentNode.insertBefore(input, sel);
  sel.parentNode.insertBefore(list, sel);
  sel.style.display = 'none';
  // Sync display text back to select value on change
  input.addEventListener('change', () => {
    const match = Array.from(sel.options).find(o => o.textContent === input.value);
    if (match) sel.value = match.value;
  });
});
```

Tambah attribute data-searchable ke 3 <select>.

### Anti-patterns

- Identical 3 dropdown styling (vary label hierarchy)
- Modal
- Multi-step wizard
- Pricing preview otomatis
- border-left colored accent di section
- Inline style

### Responsive

- Desktop: form max-w 960px
- Mobile: form full-width, section stack vertical

### Accessibility

- Select with label for
- Required indicator
- Helper for "Hari ini"
- Datalist compatible dengan screen reader

### Output

1 file HTML. Include layout shell.
CDN sesuai system-prompt.

---

## 10. Pemeriksaan list

File: `crud/pemeriksaan-list.html`. Issue: #17.

### Context

Admin pagi hari lihat antrian. Tabel ini operation utama, 50-100 baris per hari. Butuh sort by tanggal (desc), status visible at-a-glance, quick action untuk advance status (Mulai / Selesai).

### User flow

1. User buka `/pemeriksaan` → tabel render dengan semua pemeriksaan di-sort tanggal desc
2. User search by nama pasien atau No Transaksi → filter real-time
3. User lihat status badge untuk tahu state
4. User click "Mulai" (jika Menunggu) → confirm dialog → submit ke update_status
5. User click "Selesai" (jika Sedang Diperiksa) → confirm dialog → submit
6. User click Hapus (jika bukan Selesai) → confirm dialog → submit ke delete handler
7. User click "Daftar Pemeriksaan" → navigate ke form create

### States per row

- Menunggu: badge bg-warning + tombol Mulai (btn-info) + tombol Hapus
- Sedang Diperiksa: badge bg-info + tombol Selesai (btn-success) + tombol Hapus
- Selesai: badge bg-success + text "Immutable" text-muted (no action button)

### States per page

- Default: tabel populated, sorted by tanggal DESC
- Empty: empty state "Belum ada data pemeriksaan"
- Search active: filter real-time
- After quick action: reload dengan flash success/error

### Layout

Sama dengan list lain, plus:
- Action column: vary per status (different buttons atau no button)
- Biaya: text-end, fw-semibold
- Sort default: tanggal_periksa DESC, created_at DESC (dari Query)

### Components

- Status badge: sesuai spec system-prompt (with icon)
- Tombol Mulai: btn btn-sm btn-info + bi-play-fill
- Tombol Selesai: btn btn-sm btn-success + bi-check-lg
- Tombol Hapus: btn btn-sm btn-outline-danger + bi-trash
- Quick action button click: confirm dialog + submit hidden form

### Sample content

6 baris sample dengan variasi status:
1. TRX-2026001, 19/06/2026, Andi Pratama, dr. Sari Wijaya, Audiometri, Rp 250.000, badge Menunggu, [Mulai] [Hapus]
2. TRX-2026002, 19/06/2026, Siti Aminah, dr. Budi Santoso, BERA, Rp 500.000, badge Sedang Diperiksa, [Selesai] [Hapus]
3. TRX-2026003, 18/06/2026, Budi Santoso, dr. Sari Wijaya, Timpanometri, Rp 200.000, badge Selesai, "Immutable"
4. TRX-2026004, 18/06/2026, Dewi Lestari, dr. Rina Kusuma, OAE, Rp 350.000, badge Menunggu, [Mulai] [Hapus]
5. TRX-2026005, 17/06/2026, Rudi Hermawan, dr. Ahmad Fauzi, ASSR, Rp 600.000, badge Sedang Diperiksa, [Selesai] [Hapus]
6. TRX-2026006, 17/06/2026, Ani Rahmawati, dr. Linda Marlina, Audiometri, Rp 250.000, badge Selesai, "Immutable"

### JS (live search + quick action)

Live search sama dengan list lain.

Quick action pattern:
```html
<button type="button" class="btn btn-sm btn-info" 
        onclick="if(confirm('Ubah status ke Sedang Diperiksa?')) document.getElementById('form-TRX-2026001').submit();">
  <i class="bi bi-play-fill"></i> Mulai
</button>
<form id="form-TRX-2026001" method="post" action="/pemeriksaan/update_status?id=TRX-2026001" style="display:none">
  <input type="hidden" name="status_pemeriksaan" value="Sedang Diperiksa">
</form>
```

Hidden form per row dengan id pattern "form-TRX-{id}".

### Anti-patterns

- Card wrapper untuk tabel
- border-left colored accent di row berdasarkan status
- Pagination (out of scope UAS)
- Detail modal
- Print invoice
- Status history
- Bulk update
- Modal untuk konfirmasi (pakai inline confirm)
- Hero-metric

### Responsive

- Desktop: full table dengan 9 columns
- Tablet: horizontal scroll
- Mobile: horizontal scroll + simplified columns (sembunyikan Layanan / Biaya di mobile, atau reflow)

### Accessibility

- Status badge: aria-label="Status Menunggu"
- Action buttons: aria-label masing-masing
- Sort: aria-sort di thead
- Quick action confirm: announce result via aria-live

### Output

1 file HTML. Include layout shell.
CDN sesuai system-prompt.
Max 1 style + 1 script block.
