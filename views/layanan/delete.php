<?php
/**
 * View Layanan Delete confirmation
 * Issue #39
 */

use Silk\Entity\Layanan;
use Silk\Presenter\LayananPresenter;

$presenter = new LayananPresenter(new Layanan());
$id = query_param('id');
$row = $id !== '' ? $presenter->getFormData((int) $id) : [];
$flash = flash_message();

if (empty($row) || empty($row['id_layanan'])) {
    echo '<div class="alert alert-danger">Data layanan tidak ditemukan.</div>';
    echo '<a href="/layanan" class="btn btn-primary">Kembali</a>';
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
    <h1 class="h3 fw-semibold mb-0">Hapus Layanan</h1>
</div>

<div class="card shadow-sm rounded-4 border-danger border-2">
    <div class="card-body p-4">
        <div class="text-center py-3">
            <i class="bi bi-exclamation-triangle-fill text-danger display-4"></i>
            <h2 class="h5 mt-3">Yakin ingin menghapus data layanan ini?</h2>
            <p class="text-muted mb-0">
                <strong><?= htmlspecialchars($row['nama_layanan']) ?></strong>
                — <?= htmlspecialchars($row['biaya_fmt'] ?? '') ?>
            </p>
        </div>

        <form method="post" action="/layanan/delete" class="d-flex gap-2 justify-content-center mt-4">
            <input type="hidden" name="id" value="<?= (int) $row['id_layanan'] ?>">
            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus</button>
            <a href="/layanan" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
