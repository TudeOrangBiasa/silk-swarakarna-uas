# Laporan Bug: Pencarian Nama Dokter Tidak Berfungsi di Halaman Pemeriksaan

**Tingkat Keparahan:** 🟡 Medium (Functional Bug)
**Lokasi Bug:** `src/Query/PemeriksaanQuery.php` (Baris 41)

## Deskripsi Bug
Pada halaman daftar Data Pemeriksaan, terdapat kotak pencarian ( *Search* ) dengan tulisan placeholder: **"Cari pasien atau dokter..."**. 
Namun, ketika pengguna mengetikkan nama dokter ke dalam kotak pencarian tersebut, hasil pencarian selalu kosong. Fitur pencarian ini hanya merespons dan berhasil mencari jika kata kunci yang dimasukkan adalah nama pasien.

## Analisis Akar Masalah (Root Cause)
1. **Logika Filter Pencarian yang Kurang Lengkap:** 
   Di dalam file `src/Query/PemeriksaanQuery.php` pada fungsi `buildFilter()`, *query* SQL yang dibentuk untuk menangani `$keyword` pencarian hanya memeriksa kecocokan pada kolom nama pasien saja (`ps.nama_pasien`).
   ```php
   if ($keyword !== null && $keyword !== '') {
       $clauses[] = 'ps.nama_pasien LIKE ?'; // <-- Hanya mengecek tabel pasien
       $params[] = '%' . $keyword . '%';
   }
   ```
   *Backend* sama sekali tidak mengikutsertakan kolom nama dokter (`d.nama_dokter`) ke dalam filter pencarian tersebut, sehingga wajar jika nama dokter tidak pernah terdeteksi.

2. **Hilangnya `JOIN` pada Fungsi Penghitungan Paginasi:**
   Jika filter nama dokter tersebut ditambahkan nantinya, akan timbul masalah baru di fungsi `countAllJoined()`. Fungsi ini digunakan untuk menghitung total halaman (Paginasi) saat ada filter pencarian, namun fungsi ini belum melakukan penggabungan tabel (`JOIN`) ke tabel dokter. Jika `d.nama_dokter` dipanggil di parameter pencarian, sistem akan melempar error SQL berupa *unknown column `d`* karena tabel `dokter` tidak di-`JOIN` di query hitungnya.

## Saran Perbaikan (Langkah Rekomendasi)
*(Catatan: Laporan ini ditujukan untuk tim Backend. Tidak ada kode yang diubah secara langsung oleh QA)*

1. **Ubah `buildFilter()`** di `PemeriksaanQuery.php` agar mencakup klausul *OR* untuk nama dokter:
   ```php
   if ($keyword !== null && $keyword !== '') {
       $clauses[] = '(ps.nama_pasien LIKE ? OR d.nama_dokter LIKE ?)';
       $params[] = '%' . $keyword . '%';
       $params[] = '%' . $keyword . '%';
   }
   ```
2. **Ubah `countAllJoined()`** di `PemeriksaanQuery.php` dengan menambahkan `JOIN dokter d` agar SQL penghitungan baris dapat mengenali alias `d`:
   ```php
   $sql = 'SELECT COUNT(*) AS n FROM pemeriksaan p 
           JOIN pasien ps ON p.id_pasien = ps.id_pasien 
           JOIN dokter d ON p.id_dokter = d.id_dokter' . $where;
   ```
