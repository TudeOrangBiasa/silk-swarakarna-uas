# Laporan Bug: Notifikasi Hapus Data "Berhasil" Tapi Data Tidak Terhapus (Gagal Hapus Siluman)

**Tingkat Keparahan:** 🔴 High (Critical UX Bug)
**Lokasi Bug:** `public/index.php` (Baris 131-136)

## Deskripsi Bug
Ketika pengguna mencoba menghapus data (terjadi pada *semua tabel* seperti Pasien, Dokter, Layanan, Pemeriksaan) yang memiliki relasi dengan tabel lain, sistem menampilkan notifikasi hijau bertuliskan **"Data berhasil dihapus"**. 
Akan tetapi, data tersebut sebenarnya **tidak terhapus** dari *database* dan masih muncul di dalam tabel aplikasi. Bug ini membingungkan karena pengguna merasa operasi berhasil, padahal gagal.

Satu-satunya data yang benar-benar bisa terhapus hanyalah data yang "kebetulan" tidak memiliki relasi sama sekali (misalnya data pada baris/urutan 1 yang belum pernah dipakai di transaksi).

## Analisis Akar Masalah (Root Cause)
1. **Foreign Key Constraint:** Data-data pada baris selain baris 1 rata-rata sudah digunakan di tabel lain (misalnya Pasien memiliki riwayat Pemeriksaan). Database secara otomatis akan menolak penghapusan (*Foreign Key Constraint Violation / Error 23000*).
2. **Penanganan Error Diam-Diam (Silent Failure):** Di dalam file Entitas (contoh: `src/Entity/Pasien.php` method `delete()`), *error* dari *database* tersebut ditangkap (`catch`) dan fungsi mengembalikan nilai `false`.
3. **Pengabaian Nilai Return di Controller Utama:** Di `public/index.php`, aplikasi memanggil fungsi hapus dan menyimpannya di variabel `$result`:
   ```php
   $result = $instance->{$action['method']}(...$args);
   // Success: redirect to the entity list page
   $entity = explode('.', $routeKey)[0];
   $_SESSION['flash_success'] = $action['method'] === 'delete'
       ? ucfirst($entity) . ' berhasil dihapus.'
       : ucfirst($entity) . ' berhasil disimpan.';
   ```
   **Kesalahannya:** Aplikasi sama sekali *tidak mengecek* apakah `$result` bernilai `true` atau `false`. Aplikasi langsung berasumsi bahwa proses selalu berhasil dan memunculkan notifikasi sukses `flash_success`.

## Saran Perbaikan (Langkah Rekomendasi)
Harus dilakukan pengecekan terhadap nilai kembalian `$result` pada file `public/index.php`. Jika `$result === false`, berikan pesan *error* alih-alih pesan sukses. 

Contoh perbaikannya:
```php
$result = $instance->{$action['method']}(...$args);

if ($result === false) {
    redirectBackWithError('Gagal menghapus ' . ucfirst($entity) . '. Data ini masih digunakan di transaksi lain (terikat relasi).');
}

// Jika sukses:
$_SESSION['flash_success'] = $action['method'] === 'delete'
    ? ucfirst($entity) . ' berhasil dihapus.'
    : ucfirst($entity) . ' berhasil disimpan.';
```

*(Catatan: Sesuai instruksi, saya tidak mengubah kode apa pun untuk memperbaiki bug ini. Laporan ini dapat diserahkan ke tim backend untuk segera ditindaklanjuti).*
