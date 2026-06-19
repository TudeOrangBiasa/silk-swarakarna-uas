# 19 — Delete Handlers (All Entities)

Status: ready-for-dev
Slug: 19-delete-handlers
Depends: 06, 07, 08, 09, 10, 12, 14, 17
Estimate: 1h

## Goal

Build standalone delete handler files for all 4 entities. Each receives id via GET/POST, calls delete() on class, checks FK constraint (master tables), sets flash message, redirects to list.

Pemeriksaan delete has extra guard: cannot delete if status = 'Selesai'.

## Files

- `views/pasien/delete.php` (create)
- `views/dokter/delete.php` (create)
- `views/layanan/delete.php` (create)
- `views/pemeriksaan/delete.php` (create)

## Acceptance

- [ ] Each file receives `$id` from URL (GET parameter)
- [ ] Calls delete() on respective class instance
- [ ] Catches FK constraint violation (PDOException with 23000 code) for master tables
- [ ] On FK error: sets flash error "Tidak dapat menghapus [entity] karena masih digunakan di data Pemeriksaan"
- [ ] On success: sets flash success "[Entity] berhasil dihapus"
- [ ] Pemeriksaan delete checks status !== 'Selesai' before delete, else flash error
- [ ] All files redirect back to respective index page after handling
- [ ] No view rendering — pure handler + redirect
- [ ] Uses session flash for messages (stored in $_SESSION['flash'] array)
- [ ] Files are required by router AFTER route matching (not included inline in index.php)

## Test

```php
// pasien/delete.php pattern
session_start();
require_once '../../includes/bootstrap.php';

use Silk\Database;
use Silk\Pasien;

$id = $_GET['id'] ?? 0;
try {
    $pasien = new Pasien();
    if ($pasien->delete($id)) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pasien berhasil dihapus'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus pasien'];
    }
} catch (\PDOException $e) {
    if ($e->getCode() == 23000) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tidak dapat menghapus pasien karena masih memiliki riwayat Pemeriksaan'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    }
}
header('Location: index.php?page=pasien');
exit;
```

```php
// pemeriksaan/delete.php extra guard
$periksa = new Pemeriksaan();
$data = $periksa->getById($id);
if ($data['status_pemeriksaan'] === 'Selesai') {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pemeriksaan dengan status Selesai tidak dapat dihapus'];
    header('Location: index.php?page=pemeriksaan');
    exit;
}
$periksa->delete($id);
```

## Out of scope

- Bulk delete
- Soft delete
- Delete confirmation page (JS confirm in list is sufficient)
