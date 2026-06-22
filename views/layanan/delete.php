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

if (empty($row) || empty($row['id_layanan'])) {
    echo '<div class="alert alert-danger" role="alert">Data layanan tidak ditemukan.</div>';
    echo '<a href="/layanan" class="btn btn-primary">Kembali</a>';
    return;
}
?>

<?php include __DIR__ . '/../partials/_flash.php'; ?>

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
                : <?= htmlspecialchars($row['biaya_fmt'] ?? '') ?>
            </p>
            <p class="text-muted small mt-2 mb-0">
                Data ini akan disembunyikan dari daftar. Riwayat pemeriksaan tetap aman.<br>
                Untuk menampilkan kembali, gunakan toggle "Tampilkan data dihapus" di daftar.
            </p>
        </div>

        <form method="post" action="/layanan/delete" class="d-flex gap-2 justify-content-center mt-4">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $row['id_layanan'] ?>">
            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus</button>
            <a href="/layanan" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
