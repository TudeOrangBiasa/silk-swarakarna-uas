<?php
/**
 * View Layanan List
 * Issue #34 — Data via LayananPresenter (no search)
 */

use Silk\Entity\Layanan;
use Silk\Presenter\LayananPresenter;

$rows = [
    ['id_layanan' => 1, 'nama_layanan' => 'BERA (Brainstem Evoked Response Audiometry)', 'biaya_fmt' => 'Rp 750.000'],
    ['id_layanan' => 2, 'nama_layanan' => 'Audiometri', 'biaya_fmt' => 'Rp 250.000'],
    ['id_layanan' => 3, 'nama_layanan' => 'OAE (Otoacoustic Emission)', 'biaya_fmt' => 'Rp 350.000'],
];
$flash = flash_message();
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Data Layanan</h1>
    <a href="/layanan/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Layanan
    </a>
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
                            <tr>
                                <td class="px-4 text-muted"><?= $i + 1 ?></td>
                                <td class="fw-medium"><?= htmlspecialchars($l['nama_layanan']) ?></td>
                                <td class="text-end fw-medium"><?= htmlspecialchars($l['biaya_fmt']) ?></td>
                                <td class="px-4">
                                    <div class="d-flex gap-2">
                                        <a href="/layanan/edit?id=<?= (int) $l['id_layanan'] ?>" class="btn btn-sm btn-light text-secondary border" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="/layanan/delete?id=<?= (int) $l['id_layanan'] ?>" class="btn btn-sm btn-light text-danger border" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
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
