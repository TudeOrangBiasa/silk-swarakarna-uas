<?php
/**
 * View Pasien List + Search
 * Issue #30 — Data via PasienPresenter
 */

use Silk\Entity\Pasien;
use Silk\Presenter\PasienPresenter;

$presenter = new PasienPresenter(new Pasien());
$keyword = query_param('search');
$page = max(1, (int) query_param('page', '1'));
$data = $presenter->getListData($keyword, $page);
$rows = $data['rows'];
$pagination = $data['pagination'];
?>

<?php include __DIR__ . '/../partials/_flash.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Data Pasien</h1>
    <a href="/pasien/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Pasien
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <form method="get" class="mb-3">
            <div class="input-group input-group-lg shadow-sm rounded-3">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="search" name="search" class="form-control border-start-0 ps-0 fs-6" placeholder="Cari nama pasien..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit" class="btn btn-primary px-4 fs-6">Cari</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive px-2 pb-2">
            <table class="table table-borderless table-hover align-middle mb-0" id="tabel-pasien">
                <thead class="text-muted small border-bottom">
                    <tr>
                        <th class="fw-medium px-4">No</th>
                        <th class="fw-medium">No Rekam Medis</th>
                        <th class="fw-medium">Nama</th>
                        <th class="fw-medium">Tgl Lahir</th>
                        <th class="fw-medium">JK</th>
                        <th class="fw-medium">No HP</th>
                        <th class="fw-medium px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data pasien.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $p): ?>
                            <tr>
                                <td class="px-4 text-muted"><?= $i + 1 ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['id_pasien']) ?></span></td>
                                <td class="fw-medium">
                                    <?php if (!empty($p['foto'])): ?>
                                        <img src="/<?= htmlspecialchars($p['foto']) ?>" class="avatar-sm rounded-circle me-2" width="32" height="32" alt="">
                                    <?php else: ?>
                                        <span class="avatar-sm rounded-circle me-2 d-inline-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary" style="width:32px;height:32px;font-size:0.75rem;font-weight:600;"><?= strtoupper(substr($p['nama_pasien'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($p['nama_pasien']) ?>
                                </td>
                                <td><?= htmlspecialchars($p['tanggal_lahir_fmt']) ?></td>
                                <td><?= $p['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                <td><?= htmlspecialchars($p['no_hp'] ?? '-') ?></td>
                                <td class="px-4">
                                    <div class="d-flex gap-2">
                                        <a href="/pasien/edit?id=<?= urlencode($p['id_pasien']) ?>" class="btn btn-sm btn-touch btn-light text-secondary border" title="Edit" aria-label="Edit pasien <?= htmlspecialchars($p['nama_pasien']) ?>">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="/pasien/delete?id=<?= urlencode($p['id_pasien']) ?>" class="btn btn-sm btn-touch btn-light text-danger border" title="Hapus" aria-label="Hapus pasien <?= htmlspecialchars($p['nama_pasien']) ?>">
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

<?php
$baseUrl = '/pasien';
include __DIR__ . '/../partials/pagination.php';
?>
