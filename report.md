# Laporan Perbaikan UI (Sidebar & Scroll)

Berikut adalah hasil identifikasi masalah (*Root Cause Analysis*) dan perbaikan yang telah dilakukan terkait *bug* pada sidebar dan form yang tidak bisa di-scroll.

## 1. Masalah Form Tidak Bisa di-Scroll
**Penyebab:**
Pada file `views/layout/header.php`, div pembungkus utama menggunakan class `min-vh-100` (minimal tinggi 100% viewport) dikombinasikan dengan `<body>` yang diset `overflow-hidden`. 
Karena memakai `min-vh-100`, saat isi form panjang, div pembungkus tersebut ikut memanjang tak terhingga melebihi tinggi layar. Akibatnya, elemen bawah form tertutup/terpotong oleh batas body (`overflow-hidden`) dan *scrollbar* bawaan pada elemen `<main>` tidak muncul.

**Solusi:**
Mengganti `min-vh-100` menjadi `vh-100` di `header.php`. Hal ini memaksa div pembungkus utama memiliki tinggi **mutlak** 100% layar (tidak boleh lebih). Dengan demikian, jika isi form di dalamnya terlalu panjang, elemen `<main>` yang memiliki atribut `overflow-y-auto` akan otomatis memunculkan *scrollbar* dan berfungsi secara normal.

## 2. Masalah Sidebar Tidak Berfungsi (Toggle Collapse/Expand)
**Penyebab:**
Aplikasi menggunakan class bawaan Bootstrap 5.3 yaitu `.offcanvas-lg` pada elemen sidebar. Pada *desktop*, Bootstrap menerapkan *variable* CSS `--bs-offcanvas-width` (lebar *default* 400px) menggunakan selector `.offcanvas-lg.offcanvas-start`. 
Selector bawaan Bootstrap ini memiliki bobot spesifisitas CSS (0,2,0) yang **lebih tinggi** dibanding class kustom `.sidebar-dark` (0,1,0) yang kita tulis di `app.css`. Akibatnya, perintah JavaScript yang mengubah *class* HTML ke `.sidebar-collapsed` (dengan target lebar 60px) diabaikan oleh peramban (kalah dari aturan spesifisitas bawaan Bootstrap).

**Solusi:**
Menambahkan tag `!important` pada atribut `width` di file `public/assets/css/app.css` (baik untuk mode normal `280px` maupun mode collapsed `60px`). Hal ini memaksa peramban untuk memprioritaskan lebar Sidebar kustom aplikasi kita dibandingkan lebar bawaan komponen *offcanvas* milik Bootstrap.

## 3. Sidebar Tidak Muncul Saat Tombol Burger Diklik di Mobile
**Penyebab:**
Ada dua penyebab utama:
1. Fungsi `bootstrap.Offcanvas.getOrCreateInstance(...).show()` yang dipanggil lewat JavaScript di `footer.php` seringkali bertabrakan dengan penundaan *loading* library Bootstrap (`defer`) dan inisialisasi *offcanvas* bawaan.
2. Aturan `transition: width 0.2s ease-out;` di `app.css` secara tidak sengaja menimpa animasi `transform` bawaan Bootstrap. Hal ini membuat JavaScript Bootstrap "kebingungan" karena tidak pernah menerima *event* `transitionend`, sehingga panel sidebar macet setengah jalan dan gagal terbuka di layar *mobile*.

**Solusi:**
1. Memisahkan tombol *toggle* antara Desktop dan Mobile di `views/layout/_topbar.php`. Di Mobile, kita memakai fungsionalitas bawaan Bootstrap (`data-bs-toggle="offcanvas" data-bs-target="#sidebarDrawer"`) yang 100% stabil menangani pemunculan sidebar tanpa kustom JavaScript. Di Desktop, kita memakai kustom JavaScript lama yang dikhususkan untuk tombol desktop (`#topbarToggleDesktop`) guna menciutkan ukuran sidebar.
2. Memperbaiki aturan CSS menjadi `transition: width 0.2s ease-out, transform 0.3s ease-in-out;` agar animasi *slide* bawaan Bootstrap tetap berjalan mulus.

## 4. Masalah CSS Terblokir oleh Content Security Policy (CSP)
**Penyebab:**
Saat Anda mengetes di browser, *URL host* (misalnya `127.0.0.1:8000`) kadang berbeda secara literal dengan `APP_URL` yang didefinisikan secara *default* (`http://localhost:8000`). Aturan **Content Security Policy (CSP)** yang sangat ketat melarang pemuatan file CSS jika *domain* yang dimuat berbeda dengan URL halaman (`'self'`), sehingga file `app.css` diblokir mentah-mentah oleh peramban dan desain sidebar hancur.
Di samping itu, browser DevTools mencoba mengunduh *source map* dari CDN Bootstrap namun diblokir oleh aturan `connect-src 'self'`.

**Solusi:**
- Pada file `views/layout/header.php`, pemuatan `app.css` diubah dari yang tadinya menggunakan *absolute URL* (`APP_URL . '/assets/css/app.css'`) menjadi *relative URL* (`/assets/css/app.css`). Dengan begini, peramban selalu menganggapnya sebagai `'self'` tak peduli apakah Anda mengakses lewat `localhost` ataupun `127.0.0.1`.
- Pada `includes/bootstrap.php`, memodifikasi *header* CSP untuk mengizinkan `https://cdn.jsdelivr.net` pada direktif `connect-src`.

## 5. Sidebar Tampil Sangat Sempit (60px) di Mode Mobile
**Penyebab:**
Jika pengguna pernah menciutkan sidebar di mode desktop, status tersebut (`sidebar-collapsed: 1`) akan tersimpan di `localStorage` peramban. Saat layar dikecilkan ke mode *mobile*, class `.sidebar-collapsed` tersebut masih aktif di tag `<html>`. Karena aturan CSS sebelumnya berlaku untuk semua ukuran layar, panel *offcanvas* di mobile secara keliru dipaksa mengikuti lebar `60px` dan teksnya disembunyikan, sehingga sidebar terlihat terpotong/sangat sempit seperti di *screenshot*.

**Solusi:**
Membungkus semua blok CSS yang berkaitan dengan `html.sidebar-collapsed` ke dalam *media query* `@media (min-width: 992px)` di file `app.css`. Hal ini memastikan bahwa status penciutan (*collapse*) hanya memengaruhi tampilan Desktop, sedangkan di mode *Mobile*, sidebar akan selalu mengabaikan status tersebut dan tampil *full-width* (280px) secara normal.

---
*QA Perbaikan telah selesai dilakukan. Aplikasi seharusnya kini sudah bebas dari bug layout tersebut.*
