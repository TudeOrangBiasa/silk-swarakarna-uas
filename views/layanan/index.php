<?php
/**
 * View Layanan List
 * Issue #34 — Data via LayananPresenter (no search)
 */

use Silk\Entity\Layanan;
use Silk\Presenter\LayananPresenter;

$presenter = new LayananPresenter(new Layanan());
$page = max(1, (int) query_param('page', '1'));
$showDeleted = query_param('show_deleted') === '1';
$data = $presenter->getListData($page, showDeleted: $showDeleted);
$rows = $data['rows'];
$pagination = $data['pagination'];
?>

<?php include __DIR__ . '/../partials/_flash.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Data Layanan</h1>
    <a href="/layanan/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Layanan
    </a>
</div>

<div class="d-flex justify-content-start mb-3">
    <form method="get" class="mb-0">
        <div class="form-check">
            <input type="checkbox" name="show_deleted" value="1" class="form-check-input" id="showDeleted" <?= $showDeleted ? 'checked' : '' ?> onchange="this.form.submit()">
            <label class="form-check-label text-muted small" for="showDeleted">Tampilkan data dihapus</label>
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0 mt-3">
        <div class="table-responsive px-2 pb-2">
            <table class="table table-borderless table-hover align-middle mb-0" id="tabel-layanan">
                <thead class="text-muted small border-bottom">
                    <tr>
                        <th class="fw-medium px-4">No</th>
                        <th class="fw-medium">Nama Layanan</th>
                        <th class="fw-medium text-end">Biaya</th>
                        <th class="fw-medium px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data layanan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $l): ?>
                            <tr class="<?= !empty($l['is_deleted']) ? 'text-decoration-line-through text-muted' : '' ?>">
                                <td class="px-4 text-muted"><?= $i + 1 ?></td>
                                <td class="fw-medium">
                                    <?= htmlspecialchars($l['nama_layanan']) ?>
                                    <?php if (!empty($l['is_deleted'])): ?><span class="badge bg-danger ms-1">Dihapus</span><?php endif; ?>
                                </td>
                                <td class="text-end fw-medium"><?= htmlspecialchars($l['biaya_fmt']) ?></td>
                                <td class="px-4">
                                    <div class="d-flex gap-2">
                                        <?php if (!empty($l['is_deleted'])): ?>
                                            <form method="post" action="/layanan/restore" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int) $l['id_layanan'] ?>">
                                                <button type="submit" class="btn btn-sm btn-touch btn-light text-success border" title="Pulihkan" aria-label="Pulihkan layanan <?= htmlspecialchars($l['nama_layanan']) ?>">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="/layanan/edit?id=<?= (int) $l['id_layanan'] ?>" class="btn btn-sm btn-touch btn-light text-secondary border" title="Edit" aria-label="Edit layanan <?= htmlspecialchars($l['nama_layanan']) ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="/layanan/delete?id=<?= (int) $l['id_layanan'] ?>" class="btn btn-sm btn-touch btn-light text-danger border" title="Hapus" aria-label="Hapus layanan <?= htmlspecialchars($l['nama_layanan']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$baseUrl = '/layanan';
include __DIR__ . '/../partials/pagination.php';
?>
