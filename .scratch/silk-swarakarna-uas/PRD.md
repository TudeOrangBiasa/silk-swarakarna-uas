

# **PRODUCT REQUIREMENT DOCUMENT (PRD)**

## **SISTEM INFORMASI LAYANAN KLINIK SWARAKARNA (SILK-SWARAKARNA)**

### **1\. TOPIK PROJECT & DESKRIPSI UMUM**

* **Topik Project:** Sistem Klinik (Pusat Pendengaran dan Keseimbangan)  
* **Nama Sistem:** Sistem Informasi Layanan Klinik Swarakarna (SILK-Swarakarna)  
* **Deskripsi Singkat:** SILK-Swarakarna adalah aplikasi berbasis web *native* PHP berbasis Object-Oriented Programming (OOP) yang dirancang untuk mendigitalisasi proses pencatatan rekam medis dan pendaftaran tes pendengaran (seperti Audiometri, OAE, dan BERA). Sistem ini mempermudah manajemen data pasien, dokter, jenis layanan THT, serta pencatatan transaksi pemeriksaan secara terintegrasi.

### **2\. ANALISIS KEBUTUHAN SISTEM**

#### **2.1 Masalah yang Ingin Diselesaikan**

* Pencatatan data pasien dan penjadwalan tes pendengaran yang masih manual rawan menyebabkan salah input atau hilangnya riwayat pemeriksaan.  
* Sulitnya melacak status pemeriksaan pasien secara *real-time* (apakah sedang menunggu, diperiksa, atau selesai).  
* Rekapitulasi data transaksi layanan untuk laporan berkala membutuhkan waktu lama karena data belum tersentralisasi dalam satu database.

#### **2.2 Pengguna Sistem (Actor)**

* **Admin / Resepsionis Klinik:** Pengguna utama yang memiliki hak akses penuh untuk mengelola data master (pasien, dokter, layanan), melakukan input transaksi pemeriksaan, mengubah status transaksi, dan melihat dashboard.

#### **2.3 Kebutuhan Fungsional Sistem**

* Sistem harus mampu melakukan operasi CRUD (Create, Read, Update, Delete) pada data master Pasien.  
* Sistem harus mampu melakukan operasi CRUD pada data master Dokter.  
* Sistem harus mampu melakukan operasi CRUD pada data master Layanan.  
* Sistem harus mampu melakukan pencatatan transaksi Pemeriksaan baru yang berelasi dengan data master.  
* Sistem harus mampu mengubah status pemeriksaan (Menunggu / Sedang Diperiksa / Selesai).  
* Sistem harus memiliki fitur pencarian data pasien dan data transaksi.  
* Sistem harus menampilkan ringkasan data (total pasien, total dokter, total transaksi) di halaman Dashboard.

#### **2.4 Kebutuhan Non-Fungsional Sistem**

* **Antarmuka (UI):** Menggunakan Tailwind CSS untuk memastikan tampilan responsif, bersih, dan profesional.  
* **Keamanan dasar:** Validasi form di sisi server (*server-side validation*) untuk mencegah input kosong atau tidak valid.  
* **Arsitektur Kode:** Menerapkan konsep Object-Oriented Programming (OOP) murni dengan pemisahan fungsi di dalam *class* yang jelas.

### **3\. ARSITEKTUR DATA & DATABASE**

Sistem ini menggunakan **4 tabel database** yang saling berelasi, terdiri dari 3 data master dan 1 data transaksi.

#### **3.1 Tabel Master 1:** pasien

* id\_pasien (VARCHAR, Primary Key) \-\> Menggunakan kode otomatis (Contoh: RM-001)  
* nama\_pasien (VARCHAR)  
* tanggal\_lahir (DATE)  
* no\_hp (VARCHAR)  
* alamat (TEXT)

#### **3.2 Tabel Master 2:** dokter

* id\_dokter (INT, Auto Increment, Primary Key)  
* nama\_dokter (VARCHAR)  
* spesialisasi (VARCHAR)  
* no\_izin\_praktik (VARCHAR)

#### **3.3 Tabel Master 3:** layanan

* id\_layanan (INT, Auto Increment, Primary Key)  
* nama\_layanan (VARCHAR) \-\> Contoh: Audiometri, OAE, BERA  
* biaya (INT)

#### **3.4 Tabel Transaksi:** pemeriksaan

* id\_periksa (VARCHAR, Primary Key) \-\> Menggunakan kode otomatis (Contoh: TRX-2026001)  
* id\_pasien (VARCHAR, Foreign Key \-\> pasien.id\_pasien)  
* id\_dokter (INT, Foreign Key \-\> dokter.id\_dokter)  
* id\_layanan (INT, Foreign Key \-\> layanan.id\_layanan)  
* tanggal\_periksa (DATE)  
* keluhan (TEXT)  
* status\_pemeriksaan (ENUM('Menunggu', 'Sedang Diperiksa', 'Selesai'))

### **4\. KETENTUAN OBJECT-ORIENTED PROGRAMMING (OOP)**

Aplikasi wajib mengimplementasikan minimal 5 class utama dalam struktur direktori proyek:

1. Class Database: Mengatur koneksi ke MySQL menggunakan PDO atau MySQLi. Menyediakan *method* untuk eksekusi query global.  
2. Class Pasien: Mengemas enkapsulasi data master pasien, berisi properti pasien dan *method* CRUD (create(), read(), update(), delete(), generateKodeOtomatis()).  
3. Class Dokter: Mengemas data master dokter beserta *method* CRUD lengkap.  
4. Class Layanan: Mengemas data master layanan/tindakan klinik beserta *method* CRUD lengkap.  
5. Class Pemeriksaan: Mengemas data transaksi rekam medis, berisi fungsi untuk menyimpan pendaftaran pemeriksaan baru, menampilkan riwayat transaksional dengan klausa JOIN, serta mengubah status pemeriksaan.

### **5\. SPESIFIKASI FITUR**

#### **5.1 Fitur Minimal (Wajib)**

* **Dashboard:** Menampilkan widget total data master (Pasien, Dokter, Layanan) dan tabel sekilas untuk 5 pemeriksaan terbaru.  
* **Manajemen CRUD Master Pasien:** Halaman tambah, lihat, ubah, dan hapus data rekam medis pasien.  
* **Manajemen CRUD Master Dokter:** Halaman kelola data dokter spesialis THT.  
* **Manajemen CRUD Master Layanan:** Halaman kelola jenis pemeriksaan pendengaran beserta harganya.  
* **Form Transaksi Pemeriksaan:** Halaman untuk mendaftarkan pasien ke layanan tertentu dengan pilihan *dropdown* yang mengambil data langsung dari tabel master.  
* **Pencarian Data:** Kotak pencarian dinamis pada tabel pasien dan tabel transaksi untuk mempercepat pencarian berdasarkan nama.  
* **Validasi Form:** Pengecekan input kosong pada semua form sebelum dieksekusi ke database untuk menghindari kegagalan query.

#### **5.2 Fitur Tambahan (Optional yang Dipilih)**

* **Kode Otomatis:** Sistem secara otomatis men-generate nomor rekam medis baru (cth: RM-001) dan nomor transaksi baru (cth: TRX-001) tanpa perlu diinput manual oleh admin.  
* **Status Transaksi:** Menyediakan badge indikator warna (Kuning untuk *Menunggu*, Biru untuk *Sedang Diperiksa*, Hijau untuk *Selesai*) serta tombol aksi cepat untuk memperbarui status pemeriksaan langsung dari halaman transaksi.

