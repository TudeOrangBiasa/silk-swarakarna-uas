<?php
/**
 * View Dokter List
 * Issue #32 — Data via DokterPresenter
 */

use Silk\Entity\Dokter;
use Silk\Presenter\DokterPresenter;

$presenter = new DokterPresenter(new Dokter());
$keyword = query_param('search');
$page = max(1, (int) query_param('page', '1'));
$showDeleted = query_param('show_deleted') === '1';
$data = $presenter->getListData($keyword, $page, showDeleted: $showDeleted);
$rows = $data['rows'];
$pagination = $data['pagination'];
?>

<?php include __DIR__ . '/../partials/_flash.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Data Dokter</h1>
    <a href="/dokter/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Dokter
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <form method="get" class="mb-3">
            <div class="input-group input-group-lg shadow-sm rounded-3">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="search" name="search" class="form-control border-start-0 ps-0 fs-6" placeholder="Cari nama dokter..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit" class="btn btn-primary px-4 fs-6">Cari</button>
            </div>
            <div class="form-check mt-2">
                <input type="checkbox" name="show_deleted" value="1" class="form-check-input" id="showDeleted" <?= $showDeleted ? 'checked' : '' ?> onchange="this.form.submit()">
                <label class="form-check-label text-muted small" for="showDeleted">Tampilkan data dihapus</label>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive px-2 pb-2">
            <table class="table table-borderless table-hover align-middle mb-0" id="tabel-dokter">
                <thead class="text-muted small border-bottom">
                    <tr>
                        <th class="fw-medium px-4">No</th>
                        <th class="fw-medium">Nama Dokter</th>
                        <th class="fw-medium">No Izin Praktik</th>
                        <th class="fw-medium">Spesialisasi</th>
                        <th class="fw-medium">No HP</th>
                        <th class="fw-medium px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data dokter.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $d): ?>
                            <tr class="<?= !empty($d['is_deleted']) ? 'text-decoration-line-through text-muted' : '' ?>">
                                <td class="px-4 text-muted"><?= $i + 1 ?></td>
                                <td class="fw-medium">
                                    <?= htmlspecialchars($d['nama_dokter']) ?>
                                    <?php if (!empty($d['is_deleted'])): ?><span class="badge bg-danger ms-1">Dihapus</span><?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($d['no_izin_praktik'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($d['spesialisasi']) ?></td>
                                <td><?= htmlspecialchars($d['no_hp'] ?? '-') ?></td>
                                <td class="px-4">
                                    <div class="d-flex gap-2">
                                        <?php if (!empty($d['is_deleted'])): ?>
                                            <form method="post" action="/dokter/restore" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int) $d['id_dokter'] ?>">
                                                <button type="submit" class="btn btn-sm btn-touch btn-light text-success border" title="Pulihkan" aria-label="Pulihkan dokter <?= htmlspecialchars($d['nama_dokter']) ?>">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="/dokter/edit?id=<?= (int) $d['id_dokter'] ?>" class="btn btn-sm btn-touch btn-light text-secondary border" title="Edit" aria-label="Edit dokter <?= htmlspecialchars($d['nama_dokter']) ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="/dokter/delete?id=<?= (int) $d['id_dokter'] ?>" class="btn btn-sm btn-touch btn-light text-danger border" title="Hapus" aria-label="Hapus dokter <?= htmlspecialchars($d['nama_dokter']) ?>">
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
$baseUrl = '/dokter';
include __DIR__ . '/../partials/pagination.php';
?>
