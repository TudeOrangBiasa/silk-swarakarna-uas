# Business Logic Flow

Flowchart business logic per fitur. Setiap fitur punya alur sendiri.

## 1. High-Level Navigation

```mermaid
flowchart TD
    Start([User buka aplikasi]) --> Index[index.php load]
    Index --> Boot[bootstrap.php:<br/>session + autoload + config]
    Boot --> Route{Route?}
    Route -->|default| Dash[Tampil Dashboard]
    Route -->|'pasien'| PasienMenu
    Route -->|'dokter'| DokterMenu
    Route -->|'layanan'| LayananMenu
    Route -->|'pemeriksaan'| PeriksaMenu

    Dash --> DashView[Widget:<br/>- total pasien<br/>- total dokter<br/>- total layanan<br/>- 5 transaksi terbaru]
    DashView --> Menu
```

## 2. Dashboard

```mermaid
flowchart TD
    A[GET /] --> B[Pemeriksaan::readLatest limit 5]
    B --> C[Pasien::count]
    C --> D[Dokter::count]
    D --> E[Layanan::count]
    E --> F[Render views/dashboard.php]
    F --> G[Tampil 4 widget + tabel 5 row]
```

## 3. CRUD Pasien

```mermaid
flowchart TD
    Start([Menu Pasien]) --> List[GET /pasien<br/>tampil list]
    List --> Search{Search?}
    Search -->|Yes| Filter[WHERE nama_pasien LIKE %q%]
    Search -->|No| All[Tampil semua]
    Filter --> ListView
    All --> ListView[views/pasien/index.php]
    ListView --> Action{User action}

    Action -->|Tambah| FormCreate[views/pasien/create.php]
    Action -->|Edit| FormEdit[views/pasien/edit.php?id=]
    Action -->|Hapus| Del[DELETE pasien by id]
    Action -->|Search| Search

    FormCreate --> Submit[POST handler]
    FormEdit --> Submit
    Submit --> Validate{Validasi server-side}
    Validate -->|Empty / invalid| Err[Return error ke form]
    Err --> FormCreate
    Validate -->|OK| GenCode[generateKodeOtomatis<br/>cari MAX id_pasien<br/>format RM-XXX]
    GenCode --> Insert[INSERT INTO pasien]
    Insert --> Redirect[Redirect ke /pasien]
    Redirect --> List

    Del --> DelCheck{Cek FK di pemeriksaan?}
    DelCheck -->|Dipakai| DelErr[Gagal: pasien punya riwayat]
    DelCheck -->|Bebas| DelExec[DELETE FROM pasien]
    DelErr --> List
    DelExec --> List
```

## 4. CRUD Dokter

```mermaid
flowchart TD
    Start([Menu Dokter]) --> List[GET /dokter<br/>tampil list]
    List --> Action{User action}

    Action -->|Tambah| Form[views/dokter/create.php]
    Action -->|Edit| FormEdit[views/dokter/edit.php?id=]
    Action -->|Hapus| Del[DELETE dokter by id]

    Form --> Submit[POST handler]
    FormEdit --> Submit
    Submit --> Validate{Validasi}
    Validate -->|Invalid| Err[Return error]
    Err --> Form
    Validate -->|OK| Insert[INSERT INTO dokter<br/>id auto-increment]
    Insert --> Redirect[Redirect /dokter]
    Redirect --> List

    Del --> DelCheck{Cek FK di pemeriksaan?}
    DelCheck -->|Dipakai| DelErr[Gagal]
    DelCheck -->|Bebas| DelExec[DELETE FROM dokter]
    DelErr --> List
    DelExec --> List
```

## 5. CRUD Layanan

```mermaid
flowchart TD
    Start([Menu Layanan]) --> List[GET /layanan<br/>tampil list]
    List --> Action{User action}

    Action -->|Tambah| Form[views/layanan/create.php]
    Action -->|Edit| FormEdit[views/layanan/edit.php?id=]
    Action -->|Hapus| Del[DELETE layanan by id]

    Form --> Submit[POST handler]
    FormEdit --> Submit
    Submit --> Validate{Validasi:<br/>nama + biaya numeric}
    Validate -->|Invalid| Err[Return error]
    Err --> Form
    Validate -->|OK| Insert[INSERT INTO layanan]
    Insert --> Redirect[Redirect /layanan]
    Redirect --> List

    Del --> DelCheck{Cek FK di pemeriksaan?}
    DelCheck -->|Dipakai| DelErr[Gagal]
    DelCheck -->|Bebas| DelExec[DELETE FROM layanan]
    DelErr --> List
    DelExec --> List
```

## 6. Transaksi Pemeriksaan (Paling Kompleks)

### 6.1 Create Pemeriksaan

```mermaid
flowchart TD
    Start([Menu Pemeriksaan → Tambah]) --> Form[views/pemeriksaan/create.php]
    Form --> Load[Load 3 dropdown:<br/>- Pasien::read<br/>- Dokter::read<br/>- Layanan::read]
    Load --> Show[Tampil form dengan dropdown]
    Show --> Submit[POST handler]
    Submit --> Validate{Validasi:<br/>- pasien dipilih<br/>- dokter dipilih<br/>- layanan dipilih<br/>- tanggal_periksa valid<br/>- keluhan tidak kosong}
    Validate -->|Invalid| Err[Return error + retain input]
    Err --> Form
    Validate -->|OK| GenCode[generateKodeOtomatis<br/>TRX-YYYYNNN<br/>by current year + sequence]
    GenCode --> Insert[INSERT INTO pemeriksaan<br/>status_pemeriksaan = 'Menunggu']
    Insert --> Redirect[Redirect /pemeriksaan]
    Redirect --> List
```

### 6.2 List Pemeriksaan (with JOIN)

```mermaid
flowchart TD
    Start([Menu Pemeriksaan]) --> List[GET /pemeriksaan]
    List --> Search{Search?}
    Search -->|Yes| Filter[JOIN + WHERE p.nama_pasien LIKE %q%]
    Search -->|No| All[JOIN semua row]
    Filter --> Query
    All --> Query[SELECT p.*,<br/>ps.nama_pasien,<br/>d.nama_dokter,<br/>l.nama_layanan, l.biaya<br/>FROM pemeriksaan p<br/>JOIN pasien ps ON p.id_pasien = ps.id_pasien<br/>JOIN dokter d ON p.id_dokter = d.id_dokter<br/>JOIN layanan l ON p.id_layanan = l.id_layanan<br/>ORDER BY p.tanggal_periksa DESC]
    Query --> Render[views/pemeriksaan/index.php<br/>+ badge warna status<br/>+ tombol quick action]
    Render --> Action{User action}
    Action -->|Update Status| US[views/pemeriksaan/update_status.php]
    Action -->|Hapus| Del[DELETE]
    Action -->|Search| Search

    US --> USPost[POST new status]
    USPost --> USValid{Status valid?}
    USValid -->|No| Err
    USValid -->|Yes| USUpdate[UPDATE status_pemeriksaan<br/>WHERE id_periksa]
    USUpdate --> List

    Del --> DelExec[DELETE FROM pemeriksaan]
    DelExec --> List
```

### 6.3 Status State Machine

```mermaid
stateDiagram-v2
    [*] --> Menunggu: INSERT baru
    Menunggu --> SedangDiperiksa: Quick action "Mulai"
    SedangDiperiksa --> Selesai: Quick action "Selesai"
    Menunggu --> Selesai: Skip (allowed)
    SedangDiperiksa --> Menunggu: Revert (admin only)
    Selesai --> [*]: Historis (no edit)
```

## 7. Kode Otomatis Logic

```mermaid
flowchart TD
    Trigger([Trigger: insert pasien / periksa]) --> Query["SELECT MAX numeric part<br/>FROM table<br/>WHERE prefix matches"]
    Query --> Check{Ada row?}
    Check -->|No| First["Return prefix-001<br/>mis. RM-001 atau TRX-2026001"]
    Check -->|Yes| Inc["Increment +1<br/>zero-pad ke 3 digit<br/>atau 7 digit dgn tahun"]
    Inc --> Return[Return new code]
    First --> Return
```

**Format:**
- Pasien: `RM-001` → `RM-002` → ... (zero-pad 3 digit)
- Pemeriksaan: `TRX-2026001` → `TRX-2026002` → ... (reset tiap tahun, total 10 char)

## 8. Validasi Form Pattern

```mermaid
flowchart LR
    A[POST submit] --> B[Trim + sanitize input]
    B --> C{Empty?}
    C -->|Yes| E1[Error: field wajib diisi]
    C -->|No| D{Format valid?<br/>numeric / date / length}
    D -->|No| E2[Error: format salah]
    D -->|Yes| F[Process to DB]
    E1 --> R[Render form + error msg]
    E2 --> R
    F --> OK[Success: redirect + flash msg]
```

**Rules per field:**
- `nama_*` → tidak kosong, max 100 char
- `tanggal_lahir` → date valid, tidak di masa depan
- `no_hp` → numeric, 10-15 digit
- `biaya` → numeric, > 0
- `tanggal_periksa` → date valid, tidak sebelum hari ini (opsional, configurable)
