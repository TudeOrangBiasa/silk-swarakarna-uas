# Business logic flow

Flowchart per fitur. Semua alur ada di sini.

## 1. High-level navigation

```mermaid
flowchart TD
    Start([User buka aplikasi]) --> Login{Login?}
    Login -->|/login GET| LoginForm[Tampil form login]
    LoginForm --> SubmitLogin[POST /login]
    SubmitLogin --> ValidLogin{username + password<br/>cocok?}
    ValidLogin -->|Ya| Session[Session regenerate ID<br/>redirect ke /]
    ValidLogin -->|Tidak| LoginForm
    Session --> Index[index.php load]
    Index --> Boot[bootstrap.php:<br/>security headers + session + autoload + config + timezone]
    Boot --> Auth[auth.php load:<br/>fungsi login/logout/CSRF available]
    Auth --> CSRF{method = POST?}
    CSRF -->|POST| CSRFCheck[csrf_verify<br/>hash_equals]
    CSRFCheck -->|Gagal| CSRFErr[HTTP 403 + flash error<br/>redirect /]
    CSRFCheck -->|OK| AuthGate
    CSRF -->|GET| AuthGate{is_logged_in?}
    AuthGate -->|Tidak| RedirectLogin[Redirect /login]
    AuthGate -->|Ya| Route{Route?}
    Route -->|default| Dash[Dashboard]
    Route -->|pasien.*| PasienMenu
    Route -->|dokter.*| DokterMenu
    Route -->|layanan.*| LayananMenu
    Route -->|pemeriksaan.*| PeriksaMenu
    Route -->|logout POST| Logout[Session destroy + cookie clear<br/>redirect /login]
```

Urutan eksekusi: `bootstrap.php` → `auth.php` → CSRF check (POST only) → auth gate → route resolution → action/view.

## 2. Dashboard

```mermaid
flowchart TD
    A[GET /] --> B[PemeriksaanPresenter::getDashboardStats]
    B --> C[PasienPresenter::getCount]
    B --> D[DokterPresenter::getCount]
    B --> E[LayananPresenter::getCount]
    B --> F[Pemeriksaan::countByDate hari ini]
    B --> G[PemeriksaanQuery::getCountByMonth tahun ini]
    B --> H[PemeriksaanQuery::getTopLayanan]
    B --> I[PemeriksaanQuery::getDokterStats]
    B --> J[PemeriksaanPresenter::getLatest limit 5]
    B --> K2[PemeriksaanPresenter::getMonthlyRevenue tahun, bulan ini]
    C & D & E & F & G & H & I & J & K2 --> M[Render views/dashboard.php]
    M --> N[Tampil: hero card pasien gradient + 4 small card (termasuk Pendapatan Bulan Ini) + tabel 5 pemeriksaan terbaru]
```

`getDashboardStats` aggregates 9 sumber data dalam 1 call (no N+1). `pemeriksaan_bulan_ini` = sum `count_by_month[1..currentMonth]`. `pendapatan_bulan_ini` = `SUM(l.biaya)` di current month via `getDateRangeTotal`. Hero card pasien: gradient teal + sparkline + trend badge + 2 CTA buttons. Card "Pendapatan Bulan Ini" format `format_rupiah()`, link "Lihat Laporan" ke `/pemeriksaan/cetak`.

## 3. CRUD Pasien

```mermaid
flowchart TD
    Start([Menu Pasien]) --> List[GET /pasien]
    List --> Search{Search?}
    Search -->|Ya| Filter[PasienQuery::searchByName<br/>WHERE nama_pasien LIKE %q%]
    Search -->|Tidak| All[PasienRepository::findAll]
    Filter & All --> ListView[views/pasien/index.php<br/>+ pagination + flash message]
    ListView --> Action{User action}

    Action -->|Tambah| FormCreate[views/pasien/create.php]
    Action -->|Edit| FormEdit[views/pasien/edit.php?id=]
    Action -->|Hapus link| DelConfirm[GET /pasien/delete?id=]

    FormCreate --> Submit[POST /pasien]
    FormEdit --> SubmitEdit[POST /pasien.update]
    Submit & SubmitEdit --> Validate[Validator::validate<br/>7 field x 6 Rule class]
    Validate -->|Error| Err[ValidationException:<br/>flash + old input + error per field]
    Err --> FormBack[Redirect back ke form]
    Validate -->|OK| GenCode[PasienQuery::generateKodeOtomatis<br/>MAX numeric part + 1<br/>format RM-XXX zero-pad 3]
    GenCode --> TryInsert[Transaction: INSERT INTO pasien]
    TryInsert --> Dup{Duplicate key?}
    Dup -->|Ya, retry max 3x| GenCode
    Dup -->|Tidak| Insert[Commit + return new id]
    Insert --> Redirect[Redirect to /pasien<br/>flash success]
    Redirect --> List

    DelConfirm --> DelView[views/pasien/delete.php<br/>confirmation card]
    DelView --> DelSubmit[POST /pasien.delete<br/>CSRF + hidden id]
    DelSubmit --> DelEntity[Pasien::delete id]
    DelEntity --> DelTry{Try repo::delete}
    DelTry -->|PDOException 23000<br/>FK violation| DelFK[Entity return false<br/>data not deleted]
    DelTry -->|Success| DelOK[Entity return true]
    DelFK --> Flash[Router: flash success<br/>berhasil dihapus]
    DelOK --> Flash
    Flash --> List
```

Note: router ignores entity return value untuk delete method, selalu set flash success. Pasien dengan riwayat pemeriksaan tidak benar-benar terhapus (FK violation ditahan entity), tapi user melihat pesan "berhasil dihapus". Pemeriksaan FK ada di `pemeriksaan.id_pasien`.

### Fields dan validasi Pasien

| Field | Rule | Keterangan |
|---|---|---|
| `nama_pasien` | Required + MaxLength(100) | Nama pasien |
| `tanggal_lahir` | Required + DateNotFuture | Tidak boleh masa depan |
| `jenis_kelamin` | Required + Enum(['L', 'P']) | Laki-laki atau Perempuan |
| `pekerjaan` | MaxLength(100) | Opsional |
| `golongan_darah` | Enum(['A', 'B', 'AB', 'O']) | Opsional |
| `riwayat_penyakit` | - | Opsional (text) |
| `alergi` | - | Opsional (text) |
| `no_hp` | Required + PhoneFormat | 10-15 digit angka |
| `alamat` | Required + MaxLength(255) | Alamat lengkap |

7 field divalidasi menggunakan 6 Rule class (Required, MaxLength, DateNotFuture, PhoneFormat, Enum, PositiveNumber -- PositiveNumber tidak dipakai Pasien).

## 4. CRUD Dokter

```mermaid
flowchart TD
    Start([Menu Dokter]) --> List[GET /dokter]
    List --> Search{Search?}
    Search -->|Ya| Filter[DokterQuery::searchByName]
    Search -->|Tidak| All[DokterRepository::findAll]
    Filter & All --> ListView[views/dokter/index.php<br/>+ pagination]
    ListView --> Action{User action}

    Action -->|Tambah| Form[views/dokter/create.php]
    Action -->|Edit| FormEdit[views/dokter/edit.php?id=]
    Action -->|Hapus link| DelConfirm[GET /dokter/delete?id=]

    Form --> Submit[POST /dokter<br/>id auto-increment INT]
    FormEdit --> SubmitEdit[POST /dokter.update]
    Submit & SubmitEdit --> Validate[Validator: Required + MaxLength + PhoneFormat<br/>4 field]
    Validate -->|Error| Err[Flash error + old input]
    Err --> FormBack
    Validate -->|OK| Insert[INSERT INTO dokter<br/>id auto-increment]
    Insert --> Redirect[Redirect /dokter + flash]
    Redirect --> List

    DelConfirm --> DelView[views/dokter/delete.php]
    DelView --> DelSubmit[POST /dokter.delete<br/>CSRF + hidden id]
    DelSubmit --> DelEntity[Dokter::delete id]
    DelEntity --> DelTry{Try repo::delete}
    DelTry -->|PDOException 23000| DelFK[Entity return false]
    DelTry -->|Success| DelOK[Entity return true]
    DelFK --> Flash[Flash success]
    DelOK --> Flash
    Flash --> List
```

Default value: `spesialisasi` kosong/empty → diset `'THT'` oleh entity sebelum insert.

## 5. CRUD Layanan

```mermaid
flowchart TD
    Start([Menu Layanan]) --> List[GET /layanan]
    List --> All[LayananRepository::findAll<br/>no search]
    All --> ListView[views/layanan/index.php<br/>+ pagination]
    ListView --> Action{User action}

    Action -->|Tambah| Form[views/layanan/create.php]
    Action -->|Edit| FormEdit[views/layanan/edit.php?id=]
    Action -->|Hapus link| DelConfirm[GET /layanan/delete?id=]

    Form --> Submit[POST /layanan]
    FormEdit --> SubmitEdit[POST /layanan.update]
    Submit & SubmitEdit --> Validate[Validator: Required + MaxLength + PositiveNumber<br/>2 field]
    Validate -->|Error| Err[Flash error + old input]
    Err --> FormBack
    Validate -->|OK| Insert[INSERT INTO layanan<br/>id auto-increment INT]
    Insert --> Redirect[Redirect /layanan + flash]
    Redirect --> List

    DelConfirm --> DelView[views/layanan/delete.php]
    DelView --> DelSubmit[POST /layanan.delete<br/>CSRF + hidden id]
    DelSubmit --> DelEntity[Layanan::delete id]
    DelEntity --> DelTry{Try repo::delete}
    DelTry -->|PDOException 23000| DelFK[Entity return false]
    DelTry -->|Success| DelOK[Entity return true]
    DelFK --> Flash[Flash success]
    DelOK --> Flash
    Flash --> List
```

Tidak ada search di Layanan. Biaya: `format_rupiah()` di presenter (Rp 250.000, separator titik).

## 6. Transaksi Pemeriksaan

### 6.1 Create

```mermaid
flowchart TD
    Start([Pemeriksaan -> Tambah]) --> Form[views/pemeriksaan/create.php]
    Form --> Load[PemeriksaanPresenter::getPasienOptions<br/>-> PasienPresenter::getOptions]
    Form --> Load2[PemeriksaanPresenter::getDokterOptions<br/>-> DokterPresenter::getOptions]
    Form --> Load3[PemeriksaanPresenter::getLayananOptions<br/>-> LayananPresenter::getOptions]
    Load & Load2 & Load3 --> Show[Tampil form: 3 dropdown FK + tanggal + keluhan]
    Show --> Submit[POST /pemeriksaan]
    Submit --> Validate[Validator: 5 field<br/>id_pasien+id_dokter+id_layanan: Required<br/>tanggal_periksa: Required+DateNotFuture<br/>keluhan: Required+MaxLength 1000]
    Validate -->|Error| Err[Flash error + old input]
    Err --> Form
    Validate -->|OK| GenCode[PemeriksaanQuery::generateKodeOtomatis<br/>MAX numeric part + 1<br/>format TRX-YYYYNNN<br/>zero-pad 3, total 11 char]
    GenCode --> TryInsert[Transaction: INSERT]
    TryInsert --> Dup{Duplicate?}
    Dup -->|Ya, retry max 3x| GenCode
    Dup -->|Tidak| Insert[Commit, status default = Menunggu]
    Insert --> Redirect[Redirect /pemeriksaan]
    Redirect --> List
```

`PemeriksaanPresenter` punya referensi ke 3 presenter lain (Pasien, Dokter, Layanan) lewat constructor injection. Method `getPasienOptions/getDokterOptions/getLayananOptions` delegate ke presenter masing-masing. View cukup instantiate `PemeriksaanPresenter` saja.

### 6.2 List with JOIN + filter

```mermaid
flowchart TD
    Start([Menu Pemeriksaan]) --> List[GET /pemeriksaan?search=&status=&page=]
    List --> GetList[PemeriksaanPresenter::getListData<br/>keyword, status, null, null, page, perPage]
    GetList --> Q[PemeriksaanQuery::findAllJoined<br/>keyword, status, startDate, endDate, limit, offset]
    GetList --> C[PemeriksaanQuery::countAllJoined<br/>same filters]
    Q --> Render[views/pemeriksaan/index.php<br/>JOIN 4 table: p + ps + d + l]
    C --> Pagination[generate pagination metadata]
    Render --> Action{User action}

    Action -->|Filter submit| List
    Action -->|Mulai / Selesai button| Quick[POST /pemeriksaan/update_status<br/>id + status_pemeriksaan + CSRF]
    Action -->|Update Status page| USPage[GET /pemeriksaan/update_status?id=]
    Action -->|Hapus link| DelConfirm[GET /pemeriksaan/delete?id=]

    Quick --> Update[Pemeriksaan::updateStatus id, newStatus]
    Update --> Txn[Transaction begin]
    Txn --> Lock[Query::findStatusForUpdate id<br/>SELECT ... FOR UPDATE]
    Lock --> Found{Found?}
    Found -->|Tidak| NotFound[RuntimeException]
    Found -->|Ya| Validate[validateStatusTransition<br/>cek TRANSITIONS map]
    Validate -->|Invalid| VErr[ValidationException]
    Validate -->|OK| Apply[Repo::updateStatus]
    Apply --> Commit[Commit + redirect /pemeriksaan]
    NotFound --> Commit
    VErr --> Commit

    USPage --> USView[views/pemeriksaan/update_status.php<br/>show form select status]
    USView --> USSubmit[POST /pemeriksaan/update_status]
    USSubmit --> Update

    DelConfirm --> DelCheck{Status Selesai?}
    DelCheck -->|Ya| DelBlock[Alert: tidak bisa hapus<br/>audit trail]
    DelCheck -->|Tidak| DelForm[Form POST /pemeriksaan.delete]
    DelBlock --> List
    DelForm --> DelSubmit2[POST /pemeriksaan.delete<br/>CSRF + hidden id]
    DelSubmit2 --> DelEnt[Pemeriksaan::delete id<br/>-> repo::deleteIfNotSelesai<br/>WHERE status != Selesai]
    DelEnt --> Flash[Flash success + redirect]
    Flash --> List
```

`findAllJoined` signature di Query: `(?string $keyword, ?string $status, ?string $startDate, ?string $endDate, int $limit, int $offset)`. Presenter `getListData` accepts `(keyword, status, startDate, endDate, page, perPage)` dan convert ke `(keyword, status, startDate, endDate, perPage, offset)`. View saat ini hanya pass 2 filter (search, status), startDate/endDate hardcoded null.

### 6.3 Status state machine

```mermaid
stateDiagram-v2
    [*] --> Menunggu: INSERT baru
    Menunggu --> SedangDiperiksa: Quick action "Mulai" (bi-play-fill, btn-info)
    SedangDiperiksa --> Selesai: Quick action "Selesai" (bi-check-lg, btn-success)
    Menunggu --> Selesai: Skip, direct (allowed)
    SedangDiperiksa --> Menunggu: Revert (allowed)
    Selesai --> [*]: Terminal (no edit, no delete)
```

TRANSITIONS matrix (di `Pemeriksaan::TRANSITIONS`):

| From | To | Trigger | Color |
|---|---|---|---|
| Menunggu | Sedang Diperiksa | Quick button "Mulai" | btn-info + bi-play-fill |
| Menunggu | Selesai | Quick button "Selesai" | btn-success + bi-check-lg |
| Sedang Diperiksa | Menunggu | Update Status form | - |
| Sedang Diperiksa | Selesai | Quick button "Selesai" | btn-success + bi-check-lg |
| Selesai | - | Terminal (no transition) | - |

Race protection: `updateStatus` wraps in transaction dengan `SELECT ... FOR UPDATE`. Dua request konkuren tidak bisa race. State transition divalidasi di dalam lock. View baca allowed transitions via `Pemeriksaan::getAllowedTransitions(currentStatus)` → render button untuk masing-masing next status. Selesai = terminal, tidak ada button, tidak ada delete form (alert "audit trail").

## 7. Kode otomatis logic

```mermaid
flowchart TD
    Trigger([Trigger: insert pasien / periksa]) --> Query["SELECT MAX(CAST(SUBSTRING(id, prefix_len) AS UNSIGNED))<br/>FROM table<br/>WHERE id LIKE prefix%"]
    Query --> Check{Ada row?}
    Check -->|Tidak| First["Return prefix-001<br/>mis. RM-001 atau TRX-{YEAR}001"]
    Check -->|Ya| Inc["Increment +1<br/>zero-pad sesuai format"]
    Inc --> Return[Return new code]
    First --> Return
    Return --> Try[Transaction + INSERT]
    Try --> Dup{Duplicate key?}
    Dup -->|Ya| Retry[Repeat generate, max 3x]
    Retry --> Query
    Dup -->|Tidak| Done[Commit + return id]
```

Format:

| Entity | Prefix | Format | Total char | Reset |
|---|---|---|---|---|
| Pasien | `RM-` | `RM-{NNN}` | 6 (RM- + 3 digit) | Tidak |
| Pemeriksaan | `TRX-{YEAR}-` | `TRX-{YYYY}{NNN}` | 11 (TRX- + 4 year + 3 digit) | Ya, per tahun |

Pemeriksaan MAX query: `MAX(CAST(SUBSTRING(id_periksa, 9) AS UNSIGNED))` -- substring dari posisi 9 (start after `TRX-YYYY`), filter `id_periksa LIKE 'TRX-{YEAR}%'`. Race-safe: 3 retry pada duplicate key (PDOException dengan code 23000 atau message contains "Duplicate").

## 8. Validasi form

```mermaid
flowchart LR
    A[POST submit] --> B[Router: csrf_verify]
    B -->|Gagal| Forbidden[HTTP 403 + redirect]
    B -->|OK| C[Route to postAction]
    C --> D[Entity create/update method]
    D --> E[Validator::validate<br/>data vs rules array]
    E --> F{Rule->validate<br/>return null?}
    F -->|Tidak| G[ValidationException<br/>field => error map]
    F -->|Ya| H[Process to DB]
    G --> R[Router catch:<br/>flash error + errors session + old input<br/>redirect back to referer]
    H --> OK[Commit + redirect list page + flash success]
```

### Rule engine

Each field gets list of Rule objects. Validator iterates per field, first error stops. Accumulate errors, throw `ValidationException` dengan map `field => message` di akhir.

| Class | validate() logic |
|---|---|
| `Required` | null, empty string, empty array → error |
| `MaxLength` | strlen > max → error |
| `DateNotFuture` | string date > today → error |
| `PhoneFormat` | bukan 10-15 digit angka → error |
| `Enum` | tidak ada di allowed array → error |
| `PositiveNumber` | <= 0 atau bukan numeric → error |

Rule interface:

```php
interface Rule {
    /** null = valid, string = error message */
    public function validate(mixed $value): ?string;
}
```

Validator:

```php
$errors = [];
foreach ($rules as $field => $fieldRules) {
    $value = $data[$field] ?? null;
    foreach ($fieldRules as $rule) {
        $error = $rule->validate($value);
        if ($error !== null) {
            $errors[$field] = $error;
            break; // first error per field
        }
    }
}
if ($errors !== []) {
    throw new ValidationException($errors);
}
```

Router catch:

```php
catch (ValidationException $e) {
    redirectBackWithError('Validasi gagal, periksa input', $e->getErrors());
}
```

`redirectBackWithError` set `$_SESSION['flash_error']`, `$_SESSION['errors']`, `$_SESSION['old_input']` lalu redirect ke `HTTP_REFERER` atau `/`. View pakai `old_input('field')`, `has_error('field')`, `error_for('field')` untuk render form. Setelah render, `unset($_SESSION['old_input'], $_SESSION['errors'])`.

DB triggers juga enforce date constraints:
- `trg_pasien_check_tanggal_lahir_bi/bu` -- pasien `tanggal_lahir` must not be future
- `trg_periksa_check_tanggal_bi/bu` -- pemeriksaan `tanggal_periksa` must not be > 1 year future

Defense in depth: PHP Validator + DB trigger.

## 9. Login + CSRF flow

### Login

```mermaid
sequenceDiagram
    participant U as User
    participant Router as index.php
    participant View as auth/login.php
    participant Auth as auth.php

    U->>Router: GET /login
    Router->>View: require
    View->>U: render form (username + password + CSRF)
    View->>Auth: csrf_field() generate token

    U->>Router: POST /login (username + password + csrf_token)
    Router->>Auth: login(username, password)
    Auth->>Auth: username === ADMIN_USERNAME?
    Auth->>Auth: password_verify vs ADMIN_PASSWORD_HASH
    alt Valid
        Auth->>Auth: session_regenerate_id(true)
        Auth->>Auth: $_SESSION[user_id] = 1
        Auth->>Auth: $_SESSION[username] = username
        Router->>U: 302 Location: / + flash success
    else Invalid
        Router->>U: 302 Location: /login + flash error
    end
```

Hardcoded credentials: `ADMIN_USERNAME = 'admin'`, `ADMIN_PASSWORD_HASH = bcrypt('admin123')`. Production: replace dengan users table + `password_hash` + `password_verify`. Login page standalone (tidak pakai layout/sidebar), langsung render form di tengah layar.

### CSRF

Semua POST melewati CSRF check. Flow:

1. `csrf_token()` generate `bin2hex(random_bytes(32))`, store di `$_SESSION['csrf_token']` (reused untuk session lifetime).
2. `csrf_field()` render hidden input `<input type="hidden" name="csrf_token" value="...">`.
3. Semua form POST include `<?= csrf_field() ?>`.
4. Router panggil `csrf_verify()` di top index.php SEBELUM dispatch ke postAction: setiap POST wajib lulus.
5. `csrf_verify()` compare `$_POST['csrf_token']` vs `$_SESSION['csrf_token']` pakai `hash_equals` (constant-time).
6. Gagal: HTTP 403 + set `$_SESSION['flash_error']` + redirect `/`.

```php
// includes/auth.php
function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
```

Logout POST-only. GET `/logout` no-op. Cegah CSRF via `<img src="/logout">`. Logout button di sidebar pakai POST form + CSRF field.

## 10. Sidebar collapse flow

```mermaid
flowchart TD
    A[Toggle button clicked] --> B{window.innerWidth < 992?}
    B -->|Ya| C[Treat as offcanvas: show drawer]
    B -->|Tidak| D[Toggle class sidebar-collapsed on html]
    D --> E[localStorage.setItem<br/>sidebar-collapsed = 1/0]
    C --> F[Bootstrap Offcanvas.show]
```

Toggle button di topbar (`#topbarToggle`). Inline script di `views/layout/footer.php`.

Page load, inline script di `<head>` (header.php) baca localStorage sebelum CSS render, cegah FOUC:

```js
if (localStorage.getItem('sidebar-collapsed') === '1') {
    document.documentElement.classList.add('sidebar-collapsed');
}
```

Sidebar width: 280px (default) → 60px (collapsed). CSS: `html.sidebar-collapsed .sidebar-dark { width: 60px; }`. Collapsed mode sembunyikan: brand text, link text, section label, profile name/role, logout text. Icon center, logout button jadi circular (32px square). Transition 0.2s ease-out.

## 11. Command palette flow

```mermaid
flowchart TD
    A[User press cmd+K / ctrl+K] --> B[Show Bootstrap modal<br/>#commandPalette]
    B --> C[Input auto focus]
    C --> D{User type?}
    D -->|Ya| E[Filter items list<br/>by label text lowercase]
    D -->|Tidak| F[All items visible]
    E --> G{Enter pressed?}
    G -->|Ya| H[Navigate to first visible item<br/>window.location.href = url]
    G -->|Tidak| I{Esc?}
    I -->|Ya| J[Close modal]
    I -->|Tidak| D
    H --> K[Modal hidden]
    J --> L[Modal hidden]
```

5 nav items: Dashboard, Pasien, Dokter, Layanan, Pemeriksaan. Click juga navigate. Reset on `shown.bs.modal`: input cleared, all items visible, input focus.

## 12. Delete flow summary

Semua entity (Pasien, Dokter, Layanan, Pemeriksaan) pakai 2-step delete:

1. **GET confirmation view**: klik icon trash di list/table -> link ke `/<entity>/delete?id=<id>` -> render confirmation card (icon warning + entity name + form POST).
2. **POST execute**: klik tombol "Hapus" di confirmation card -> form submit ke `/<entity>/delete` dengan CSRF + hidden id -> router dispatch ke entity method -> repo delete -> flash success + redirect list page.

Pemeriksaan punya extra check: jika `status_pemeriksaan === 'Selesai'`, confirmation view skip form, tampilkan alert "tidak dapat dihapus karena merupakan riwayat medis" + tombol kembali. Force POST ke `/pemeriksaan/delete` (bypassing confirmation view) tetap masuk `repo::deleteIfNotSelesai` yang punya `WHERE status_pemeriksaan != 'Selesai'`, return 0 rows affected. Entity return false, tapi router tetap set flash success (router tidak check return value untuk method `delete`).

FK violation (Pasien/Dokter/Layanan dipakai di pemeriksaan): entity catch `PDOException` code 23000, return false. Router check return value (since commit `aa12f07`): if `delete` and `result === false`, `redirectBackWithError("Gagal menghapus {Entity}. Data ini masih digunakan di transaksi lain (terikat relasi).")`. Data tidak terhapus, user lihat error flash yang jelas. Flash error di-set via `$_SESSION['flash_error']` lalu redirect back ke referer (delete confirmation page).

Order penting: `$entity = explode('.', $routeKey)[0];` di-define SEBELUM FK check block, karena dipakai di error message.

## 13. Cetak Laporan

```mermaid
flowchart TD
    Start([Menu Pemeriksaan]) --> Link[Klik 'Cetak Laporan' button]
    Link --> OpenTab[GET /pemeriksaan/cetak<br/>target=_blank]
    OpenTab --> Pres[PemeriksaanPresenter::getCetakData<br/>keyword, status, startDate, endDate]
    Pres --> Q[PemeriksaanQuery::findAllJoined<br/>same filters]
    Pres --> T[PemeriksaanQuery::getDateRangeTotal<br/>SUM l.biaya + same filters]
    Q --> Render[views/pemeriksaan/cetak.php<br/>standalone HTML]
    T --> Render
    Render --> Table[Tabel: TRX, tanggal, pasien, dokter, layanan, biaya, status]
    Render --> Footer[Footer: Total Pendapatan Rp XXX]
    Render --> Btn[Tombol 'Cetak' onclick=window.print]
    Btn --> Print[Browser print dialog<br/>Save as PDF / print to paper]
```

`getCetakData` reuse `findAllJoined` + tambah `getDateRangeTotal`. View standalone (no header/sidebar), inline CSS dengan `@media print` rules hide filter form + tombol. User bisa print langsung atau "Save as PDF" via browser native. Filter form di top: date range, status, search. PerPage default 10000 (no pagination, cetak report selalu full).

## 14. Upload Foto Pasien

```mermaid
flowchart TD
    Start([Pasien Create/Edit]) --> Form[Form enctype=multipart/form-data]
    Form --> Submit[POST /pasien or /pasien.update]
    Submit --> Validate[Validator check data]
    Validate -->|Error| ErrBack[Flash error + old input]
    Validate -->|OK| HandleUp[Pasien::handleFileUpload]
    HandleUp --> CheckFile{_FILES foto uploaded?}
    CheckFile -->|Tidak| InsertNoFoto[INSERT tanpa foto]
    CheckFile -->|Ya| Mime[mime check via getimagesize<br/>jpeg/png/webp]
    Mime -->|Invalid| InsertNoFoto
    Mime -->|OK| Size[Size check max 2MB]
    Size -->|Over| InsertNoFoto
    Size -->|OK| Move[move_uploaded_file ke<br/>public/assets/uploads/pasien/]
    Move --> RandomName[Filename: bin2hex 16 byte.ext]
    RandomName --> SetPath[Set data.foto = assets/uploads/pasien/hash.ext]
    SetPath --> Insert[INSERT/UPDATE dengan foto]
    InsertNoFoto --> Insert
    Insert --> OldUnlink[Update: unlink foto lama jika ada]
    OldUnlink --> Redirect[Flash success + redirect list]
    ListView[List view] --> Thumb{Tampilkan foto?}
    Thumb -->|Ada| Img[img src=...uploads/pasien/hash.ext<br/>32x32 rounded circle]
    Thumb -->|Tidak| Initial[Inisial nama di circle placeholder]
```

`handleFileUpload` private method di `Pasien` entity. Validasi: file kosong -> skip upload (return null), lanjut insert tanpa foto. File present tapi invalid (bad mime via `getimagesize()`, oversize > 2MB, `move_uploaded_file` fail) -> throw `ValidationException` dengan field `foto` agar user lihat error. Valid -> simpan ke `public/assets/uploads/pasien/<bin2hex>.<ext>`, simpan path relatif di DB. `unlinkOldFoto` di update: reorder save new -> DB -> unlink old (hanya jika DB success). Delete entity: read foto dulu, repo delete, unlink old jika repo delete return true.

Path store di DB: `assets/uploads/pasien/<32-hex>.<ext>`. Folder `public/assets/uploads/` di-`.gitignore`. Web-accessible dari `/assets/uploads/...`.
