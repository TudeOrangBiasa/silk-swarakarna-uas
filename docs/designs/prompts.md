# Design prompts

## 1. Layout (master shell)

```
Web app layout untuk klinik. Header: navbar dengan logo text "SILK-Swarakarna"
kiri, nav links kanan (Dashboard, Pasien, Dokter, Layanan, Pemeriksaan),
active link di-highlight dengan underline biru. Main content: container
max-w-6xl mx-auto p-6 dengan background gray-50. Footer: text center
"© 2026 SILK-Swarakarna — UAS Pemrograman Web Pagi 01", text-xs text-gray-500,
py-4 border-t.

Primary color biru navy (#1e40af). Bahasa Indonesia. Bootstrap 5 CSS. Light theme.
Responsive: nav collapse ke hamburger di mobile.
```

## 2. Dashboard

```
Dashboard klinik THT. Atas: judul "Dashboard". 4 widget summary cards dalam
grid 4-col (responsive 2-col di tablet, 1-col di mobile):
- Total Pasien: icon orang + angka besar
- Total Dokter: icon stetoskop + angka
- Total Layanan: icon clipboard + angka
- Pemeriksaan Hari Ini: icon calendar + angka

Di bawah: card "5 Pemeriksaan Terbaru" dengan tabel:
No, No Transaksi, Tanggal, Pasien, Dokter, Layanan, Status (badge: Menunggu=yellow, Sedang Diperiksa=blue, Selesai=green, rounded-full px-2).

Style: card bg-white shadow rounded-lg, angka widget text-3xl font-bold, primary
color biru navy. Bahasa Indonesia. Bootstrap 5.
```

## 3. Pasien list

```
CRUD list page. Header: judul "Data Pasien" (text-2xl) + tombol "Tambah Pasien"
primary (bg-blue-800 text-white rounded-md px-4 py-2) di kanan.

Search bar: input full-width dengan placeholder "Cari nama pasien..." di atas
tabel, sticky saat scroll.

Tabel: columns No, No Rekam Medis (font-mono), Nama, Tanggal Lahir, No HP,
Alamat (truncate), Aksi (2 icon button: Edit pencil, Hapus trash, warna
default/merah). Alternating row bg-white/bg-gray-50. Border bottom.

Pagination di bawah: "Menampilkan 1-10 dari 25" + Prev 1 2 3 Next.

Empty state (kalau ga ada data): icon orang besar gray-300 + text "Belum ada
data pasien" + tombol "Tambah Pasien pertama".

Bahasa Indonesia. Bootstrap 5. Container max-w-6xl.
```

## 4. Pasien form

```
Form tambah/edit pasien klinik. Card max-w-2xl mx-auto bg-white shadow
rounded-lg p-8.

Field groups (vertical, gap-4):
- Nama Lengkap: input text, required, placeholder "Contoh: Budi Santoso"
- Tanggal Lahir: input date, required, max=today
- No HP: input tel, required, placeholder "08xxxxxxxxxx"
- Alamat: textarea 3 rows, required, placeholder "Alamat lengkap..."

Validation:
- Required fields ditandai asterisk merah
- Error message merah text-sm di bawah field

Tombol bawah: "Simpan" (primary bg-blue-800) + "Batal" (secondary
bg-white border, link kembali ke list).

Page title: "Tambah Pasien" atau "Edit Pasien" (text-2xl mb-6).

Bahasa Indonesia. Bootstrap 5.
```

## 5. Dokter list

```
Sama persis dengan pasien list tapi:
- Judul: "Data Dokter"
- Tombol: "Tambah Dokter"
- Search placeholder: "Cari nama dokter..."
- Kolom: No, Nama Dokter, Spesialisasi, No Izin Praktik (font-mono), Aksi
- Empty state: "Belum ada data dokter"
- Tombol: "Tambah Dokter pertama"
```

## 6. Dokter form

```
Form tambah/edit dokter klinik. Card max-w-2xl mx-auto bg-white shadow
rounded-lg p-8.

Fields:
- Nama Dokter: input text, required
- Spesialisasi: input text, required, default value "THT" (pre-filled)
- No Izin Praktik: input text, required, placeholder "SIP-001/2024"

Tombol: "Simpan" + "Batal". Page title: "Tambah Dokter"/"Edit Dokter".

Bahasa Indonesia. Bootstrap 5.
```

## 7. Layanan list

```
List layanan klinik THT. Header: judul "Data Layanan" + tombol "Tambah Layanan"
primary.

Tabel: No, Nama Layanan, Biaya (format Rp 250.000, right-aligned), Aksi
(Edit/Hapus).

Empty state: "Belum ada data layanan".

Catatan: tidak perlu search bar (master data kecil, biasanya < 20 row).
Bahasa Indonesia. Bootstrap 5.
```

## 8. Layanan form

```
Form tambah/edit layanan klinik. Card max-w-xl mx-auto.

Fields:
- Nama Layanan: input text, required, placeholder "Contoh: Audiometri"
- Biaya: input number, required, prefix "Rp ", min=0, step=1000

Helper text di bawah biaya: "Dalam rupiah, tanpa titik/koma. Contoh: 250000".

Tombol: "Simpan" + "Batal". Page title: "Tambah Layanan"/"Edit Layanan".

Bahasa Indonesia. Bootstrap 5.
```

## 9. Pemeriksaan form (create)

```
Form pendaftaran pemeriksaan. Card max-w-3xl mx-auto.

Header: "Daftar Pemeriksaan" + text-sm text-gray-500 "No Transaksi akan
dibuat otomatis (format TRX-YYYYNNN)".

Field groups (gap-6 antar group):
[Data Pasien & Dokter] (2-col grid):
- Pasien: dropdown searchable, format option: "RM-001 - Andi Pratama"
- Dokter: dropdown, format: "dr. Sari Wijaya, Sp.THT"
- Layanan: dropdown, format: "Audiometri - Rp 250.000"

[Jadwal]:
- Tanggal Periksa: date, min=today, default=today, required
- Keluhan: textarea 4 rows, required, placeholder "Keluhan pasien..."

Bottom info (di bawah tanggal): "Hari ini: [nama hari], [tanggal]"

Tombol: "Daftarkan" (primary) + "Batal" (secondary).

Bahasa Indonesia. Bootstrap 5. Primary biru navy.
```

## 10. Pemeriksaan list

```
List data pemeriksaan. Header: judul "Data Pemeriksaan" + tombol "Daftar
Pemeriksaan" primary.

Search bar: "Cari nama pasien..." di atas tabel.

Tabel: No, No Transaksi (font-mono), Tanggal Periksa, Pasien, Dokter,
Layanan, Biaya (Rp format, right-align), Status (badge rounded-full:
- Menunggu: bg-yellow-100 text-yellow-800
- Sedang Diperiksa: bg-blue-100 text-blue-800
- Selesai: bg-green-100 text-green-800), Aksi.

Aksi per status:
- Menunggu: tombol "Mulai" (blue) + "Hapus" (red)
- Sedang Diperiksa: tombol "Selesai" (green) + "Hapus" (red)
- Selesai: tombol "Hapus" disabled + text "Immutable"

Pagination + empty state "Belum ada data pemeriksaan".

Bahasa Indonesia. Bootstrap 5. Status badge rounded-full px-3 py-1 text-xs font-medium.
```
