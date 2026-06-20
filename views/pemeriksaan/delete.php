<?php
/**
 * View Pemeriksaan Delete confirmation
 * Issue #39 — Only allowed if status != Selesai
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
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Hapus Pemeriksaan</h1>
</div>

<?php if (($row['status_pemeriksaan'] ?? '') === 'Selesai'): ?>
    <div class="alert alert-warning">
        <i class="bi bi-shield-exclamation"></i>
        Pemeriksaan dengan status <strong>Selesai</strong> tidak dapat dihapus karena merupakan riwayat medis (audit trail).
    </div>
    <a href="/pemeriksaan" class="btn btn-primary">Kembali ke Daftar</a>
<?php else: ?>
    <div class="card shadow-sm rounded-4 border-danger border-2">
        <div class="card-body p-4">
            <div class="text-center py-3">
                <i class="bi bi-exclamation-triangle-fill text-danger display-4"></i>
                <h2 class="h5 mt-3">Yakin ingin menghapus data pemeriksaan ini?</h2>
                <p class="text-muted mb-0">
                    <code><?= htmlspecialchars($row['id_periksa']) ?></code>
                    — <?= htmlspecialchars($row['nama_pasien'] ?? '') ?>
                    (<?= htmlspecialchars($row['nama_layanan'] ?? '') ?>)
                </p>
            </div>

            <form method="post" action="/pemeriksaan/delete" class="d-flex gap-2 justify-content-center mt-4">
                <input type="hidden" name="id" value="<?= htmlspecialchars($row['id_periksa']) ?>">
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                <a href="/pemeriksaan" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
<?php endif; ?>
