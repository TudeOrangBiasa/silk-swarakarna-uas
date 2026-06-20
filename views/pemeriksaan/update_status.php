<?php
/**
 * View Pemeriksaan Update Status (quick action)
 * Issue #37 — Status update form (fallback for GET /pemeriksaan/update_status)
 */

use Silk\Entity\Pemeriksaan;
use Silk\Presenter\PemeriksaanPresenter;

$presenter = new PemeriksaanPresenter(new Pemeriksaan());
$id = query_param('id');
$row = $id !== '' ? $presenter->getFormData($id) : [];
$flash = flash_message();

if (empty($row) || empty($row['id_periksa'])) {
    echo '<div class="alert alert-danger">Data pemeriksaan tidak ditemukan.</div>';
    echo '<a href="/pemeriksaan" class="btn btn-primary">Kembali</a>';
    return;
}

$currentStatus = $row['status_pemeriksaan'] ?? '';
$transitions = $presenter->getAllowedTransitions($currentStatus);
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Update Status Pemeriksaan</h1>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <dl class="row mb-4">
            <dt class="col-sm-4">No Transaksi</dt>
            <dd class="col-sm-8"><code><?= htmlspecialchars($row['id_periksa']) ?></code></dd>
            <dt class="col-sm-4">Pasien</dt>
            <dd class="col-sm-8"><?= htmlspecialchars($row['nama_pasien'] ?? '') ?></dd>
            <dt class="col-sm-4">Status Saat Ini</dt>
            <dd class="col-sm-8"><?= $row['status_badge_html'] ?></dd>
        </dl>

        <?php if (empty($transitions)): ?>
            <div class="alert alert-info">Tidak ada transisi status yang tersedia.</div>
        <?php else: ?>
            <form method="post" action="/pemeriksaan/update_status">
                <input type="hidden" name="id" value="<?= htmlspecialchars($row['id_periksa']) ?>">
                <div class="mb-3">
                    <label class="form-label">Status Baru <span class="text-danger">*</span></label>
                    <select name="status_pemeriksaan" required class="form-select">
                        <?php foreach ($transitions as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Status</button>
                    <a href="/pemeriksaan" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
