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
    Index --> Boot[bootstrap.php:<br/>security headers + session + autoload + config]
    Boot --> AuthGate{is_logged_in?}
    AuthGate -->|Tidak| RedirectLogin[Redirect /login]
    AuthGate -->|Ya| CSRF{method = POST?}
    CSRF -->|POST| CSRFCheck[csrf_verify<br/>hash_equals]
    CSRFCheck -->|Gagal| CSRFErr[HTTP 403 + flash error<br/>redirect /]
    CSRFCheck -->|OK| Route{Route?}
    CSRF -->|GET| Route
    Route -->|default| Dash[Dashboard]
    Route -->|pasien.*| PasienMenu
    Route -->|dokter.*| DokterMenu
    Route -->|layanan.*| LayananMenu
    Route -->|pemeriksaan.*| PeriksaMenu
    Route -->|logout POST| Logout[Session destroy<br/>redirect /login]
```

## 2. Dashboard

```mermaid
flowchart TD
    A[GET /] --> B[PemeriksaanPresenter::getDashboardStats]
    B --> C[PasienPresenter::getCount]
    B --> D[DokterPresenter::getCount]
    B --> E[LayananPresenter::getCount]
    B --> F[PemeriksaanPresenter::getCountByDate hari ini]
    B --> G[PemeriksaanQuery::getCountByMonth]
    B --> H[PemeriksaanQuery::getTopLayanan]
    B --> I[PemeriksaanQuery::getDokterStats]
    B --> J[PemeriksaanPresenter::getLatest limit 5]
    C & D & E & F & G & H & I & J --> K[Render views/dashboard.php]
    K --> L[Tampil: hero card pasien + 3 small card + tabel 5 pemeriksaan terbaru]
```

## 3. CRUD Pasien

```mermaid
flowchart TD
    Start([Menu Pasien]) --> List[GET /pasien]
    List --> Search{Search?}
    Search -->|Ya| Filter[WHERE nama_pasien LIKE %q%]
    Search -->|Tidak| All[Tampil semua]
    Filter --> ListView
    All --> ListView[views/pasien/index.php<br/>+ pagination + flash message]
    ListView --> Action{User action}

    Action -->|Tambah| FormCreate[views/pasien/create.php]
    Action -->|Edit| FormEdit[views/pasien/edit.php?id=]
    Action -->|Hapus| Del[DELETE pasien by id]

    FormCreate --> Submit[POST handler]
    FormEdit --> Submit
    Submit --> Validate[Validator::validate dengan 6 rule ]
    Validate -->|Error| Err[ValidationException:<br/>flash + old input + error per field]
    Err --> FormCreate
    Validate -->|OK| GenCode[generateKodeOtomatis<br/>MAX id_pasien + increment<br/>format RM-XXX]
    GenCode --> TryInsert[Transaction: INSERT INTO pasien]
    TryInsert --> Dup{Duplicate key?}
    Dup -->|Ya, retry| GenCode
    Dup -->|Tidak| Insert[Commit]
    Insert --> Redirect[Redirect to /pasien<br/>flash success]
    Redirect --> List

    Del --> DelCheck{Catch PDOException<br/>FK violation?}
    DelCheck -->|Ya| DelErr[Flash error: punya riwayat]
    DelCheck -->|Tidak| DelOK[Flash success]
    DelErr --> List
    DelOK --> List
```

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
| `no_hp` | Required + PhoneFormat | 10-15 digit |
| `alamat` | Required + MaxLength(255) | Alamat lengkap |

## 4. CRUD Dokter

```mermaid
flowchart TD
    Start([Menu Dokter]) --> List[GET /dokter]
    List --> Action{User action}

    Action -->|Tambah| Form[views/dokter/create.php]
    Action -->|Edit| FormEdit[views/dokter/edit.php?id=]
    Action -->|Hapus| Del[DELETE dokter by id]

    Form --> Submit[POST handler]
    FormEdit --> Submit
    Submit --> Validate[Validator: Required + MaxLength + PhoneFormat]
    Validate -->|Error| Err[Flash error + old input]
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
    Start([Menu Layanan]) --> List[GET /layanan]
    List --> Action{User action}

    Action -->|Tambah| Form[views/layanan/create.php]
    Action -->|Edit| FormEdit[views/layanan/edit.php?id=]
    Action -->|Hapus| Del[DELETE layanan by id]

    Form --> Submit[POST handler]
    FormEdit --> Submit
    Submit --> Validate[Validator: Required + MaxLength + PositiveNumber]
    Validate -->|Error| Err[Flash error + old input]
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

## 6. Transaksi Pemeriksaan

### 6.1 Create

```mermaid
flowchart TD
    Start([Pemeriksaan -> Tambah]) --> Form[views/pemeriksaan/create.php]
    Form --> Load[Load 3 dropdown:<br/>PasienPresenter::getOptions<br/>DokterPresenter::getOptions<br/>LayananPresenter::getOptions]
    Load --> Show[Tampil form dengan dropdown + keluhan + tanggal]
    Show --> Submit[POST handler]
    Submit --> Validate[Validator: Required + DateNotFuture + MaxLength]
    Validate -->|Error| Err[Flash error + retain input]
    Err --> Form
    Validate -->|OK| GenCode[generateKodeOtomatis<br/>TRX-YYYYNNN<br/>by current year + increment]
    GenCode --> TryInsert[Transaction: INSERT]
    TryInsert --> Dup{Duplicate?}
    Dup -->|Ya, retry| GenCode
    Dup -->|Tidak| Insert[Commit, status = Menunggu]
    Insert --> Redirect[Redirect /pemeriksaan]
    Redirect --> List
```

### 6.2 List with JOIN

```mermaid
flowchart TD
    Start([Menu Pemeriksaan]) --> List[GET /pemeriksaan]
    List --> Search{Search + filter?}
    Search -->|Ya| Filter[JOIN + WHERE + status + date range]
    Search -->|Tidak| All[All rows JOIN]
    Filter --> P["PemeriksaanQuery::findAllJoined<br/>(keyword, status, startDate, endDate, page, perPage)"]
    All --> P
    P --> Render[views/pemeriksaan/index.php<br/>+ badge warna status<br/>+ tombol quick action]
    Render --> Action{User action}

    Action -->|Mulai| Mulai[POST updateStatus<br/>Menunggu -> Sedang Diperiksa]
    Action -->|Selesai| Selesai[POST updateStatus<br/>Sedang Diperiksa -> Selesai]
    Action -->|Update Status| US[views/pemeriksaan/update_status.php]
    Action -->|Hapus| Del[DELETE if not Selesai]
    Action -->|Search| Search

    Mulai & Selesai --> USPost
    USPost[POST updateStatus] --> USValidate[State machine check<br/>allowed transition?]
    USValidate -->|Tidak valid| Err[Flash error]
    USValidate -->|OK| USUpdate[Transaction:<br/>SELECT FOR UPDATE + UPDATE]
    USUpdate --> List
    Del --> DelExec[DELETE FROM pemeriksaan<br/>only if status != Selesai]
    DelExec --> List
```

### 6.3 Status state machine

```mermaid
stateDiagram-v2
    [*] --> Menunggu: INSERT baru
    Menunggu --> SedangDiperiksa: Quick action "Mulai"
    SedangDiperiksa --> Selesai: Quick action "Selesai"
    Menunggu --> Selesai: Skip (direct)
    SedangDiperiksa --> Menunggu: Revert (admin)
    Selesai --> [*]: Terminal (no edit, no delete)
```

Transitions matrix:

| From | To | Method |
|---|---|---|
| Menunggu | Sedang Diperiksa | POST button "Mulai" |
| Menunggu | Selesai | Skip, direct |
| Sedang Diperiksa | Menunggu | Revert |
| Sedang Diperiksa | Selesai | POST button "Selesai" |
| Selesai | - | Terminal |

Race protection: `updateStatus` wraps in transaction with `SELECT ... FOR UPDATE`. Two concurrent clicks cannot race.

## 7. Kode otomatis logic

```mermaid
flowchart TD
    Trigger([Trigger: insert pasien / periksa]) --> Query["SELECT MAX numeric part<br/>FROM table<br/>WHERE prefix match"]
    Query --> Check{Ada row?}
    Check -->|Tidak| First["Return prefix-001<br/>mis. RM-001 atau TRX-2026001"]
    Check -->|Ya| Inc["Increment +1<br/>zero-pad<br/>3 digit (pasien)<br/>7 digit dgn tahun (periksa)"]
    Inc --> Return[Return new code]
    First --> Return
    Return --> Try[Transaction + INSERT]
    Try --> Dup{Duplicate key?}
    Dup -->|Ya| Retry[Repeat generate]
    Retry --> Query
    Dup -->|Tidak| Done[Commit]
```

Format:
- Pasien: `RM-001` -> `RM-002` (zero-pad 3 digit)
- Pemeriksaan: `TRX-2026001` -> `TRX-2026002` (reset tiap tahun, total 10 char)

## 8. Validasi form

```mermaid
flowchart LR
    A[POST submit] --> B[Entity create/update method]
    B --> C[Validator::validate<br/>data vs rules array]
    C --> D{Rule->validate<br/>return null?}
    D -->|Tidak (ada error)| E[ValidationException<br/>field => error map]
    D -->|Ya (null)| F[Process to DB]
    E --> R[Router catch: flash + old input<br/>redirect back]
    F --> OK[Commit + redirect + flash success]
```

### Rule engine

Each field gets an array of Rule objects. Validator iterates, first error per field stops.

| Class | validate() logic |
|---|---|
| `Required` | null, empty string, empty array -> error |
| `MaxLength` | strlen > max -> error |
| `DateNotFuture` | string date > today -> error |
| `PhoneFormat` | not 10-15 digit numeric -> error |
| `Enum` | not in allowed array -> error |
| `PositiveNumber` | <= 0 -> error |

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
    foreach ($fieldRules as $rule) {
        $error = $rule->validate($data[$field] ?? null);
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

DB triggers also enforce date constraints:
- `trg_pasien_check_tanggal_lahir_bi/bu` -- pasien tanggal_lahir must not be future
- `trg_periksa_check_tanggal_bi/bu` -- pemeriksaan tanggal_periksa must not be > 1 year future

## 9. Login + CSRF flow

### Login

```mermaid
sequenceDiagram
    participant U as User
    participant Router as index.php
    participant Auth as auth.php
    
    U->>Router: GET /login
    Router->>U: views/auth/login.php

    U->>Router: POST /login (username + password)
    Router->>Auth: login($username, $password)
    Auth->>Auth: password_verify vs bcrypt hash
    alt Valid
        Auth->>Auth: session_regenerate_id(true)
        Auth->>Auth: $_SESSION['user_id'] = 1
        Router->>U: Redirect / (flash success)
    else Invalid
        Router->>U: Redirect /login (flash error)
    end
```

### CSRF

All POST requests go through CSRF check. Flow:

1. `csrf_token()` generates `bin2hex(random_bytes(32))`, stored in `$_SESSION['csrf_token']`.
2. `csrf_field()` renders hidden input `<input type="hidden" name="csrf_token" value="...">`.
3. All forms include `<?= csrf_field() ?>`.
4. Router calls `csrf_verify()` at top of every POST.
5. `csrf_verify()` compares `$_POST['csrf_token']` vs `$_SESSION['csrf_token']` using `hash_equals` (constant-time).
6. On mismatch: HTTP 403 + redirect `/` + flash error.

```php
// includes/auth.php
function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
```

Logout is POST-only (prevents CSRF via `<img src="/logout">`).

## 10. Sidebar collapse flow

```mermaid
flowchart TD
    A[Toggle button clicked] --> B{window.innerWidth < 992?}
    B -->|Ya| C[Treat as offcanvas: show drawer]
    B -->|Tidak (desktop)| D[Toggle class sidebar-collapsed on html]
    D --> E[localStorage.setItem<br/>sidebar-collapsed = 1/0]
    C --> F[Bootstrap Offcanvas.show]
```

On page load, inline script in `<head>` reads localStorage before CSS renders (prevents FOUC):

```js
if (localStorage.getItem('sidebar-collapsed') === '1') {
    document.documentElement.classList.add('sidebar-collapsed');
}
```

Collapsed mode: sidebar width 280px -> 60px. All text hidden. Icons centered. Logout button circular.

## 11. Command palette flow

```mermaid
flowchart TD
    A[User press cmd+K / ctrl+K] --> B[Show Bootstrap modal]
    B --> C[Input focus]
    C --> D{User type?}
    D -->|Ya| E[Filter items list<br/>by label text lowercase]
    D -->|Tidak| F[All items visible]
    E --> G{Enter pressed?}
    G -->|Ya| H[Navigate to first visible item]
    G -->|Tidak| I{Esc?}
    I -->|Ya| J[Close modal]
    I -->|Tidak| D
    H --> K[window.location.href = url]
    J --> L[Modal hidden]
```

5 items: Dashboard, Pasien, Dokter, Layanan, Pemeriksaan. Click also navigates.
