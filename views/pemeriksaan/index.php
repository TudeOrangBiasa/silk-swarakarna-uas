<?php
/**
 * Issue #37 — JOINed data, status badges, quick status transitions
 */

use Silk\Entity\Pemeriksaan;
use Silk\Presenter\PemeriksaanPresenter;

$presenter = new PemeriksaanPresenter(new Pemeriksaan());
$keyword = query_param('search');
$status = query_param('status');
$page = max(1, (int) query_param('page', '1'));
$data = $presenter->getListData($keyword, $status, null, null, $page);
$rows = $data['rows'];
$pagination = $data['pagination'];
$flash = flash_message();
$statusOptions = $presenter->getStatusOptions();
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Data Pemeriksaan</h1>
    <a href="/pemeriksaan/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Pemeriksaan
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <form method="get" class="mb-3">
            <div class="row g-2">
                <div class="col-md-7">
                    <div class="input-group input-group-lg shadow-sm rounded-3">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" class="form-control border-start-0 ps-0 fs-6" placeholder="Cari pasien atau dokter..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-lg fs-6 shadow-sm">
                        <option value="">Semua Status</option>
                        <?php foreach ($statusOptions as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 btn-lg fs-6 shadow-sm">Filter</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive px-2 pb-2">
            <table class="table table-borderless table-hover align-middle mb-0" id="tabel-pemeriksaan">
                <thead class="text-muted small border-bottom">
                    <tr>
                        <th class="fw-medium px-4">No Transaksi</th>
                        <th class="fw-medium">Tanggal</th>
                        <th class="fw-medium">Pasien</th>
                        <th class="fw-medium">Dokter</th>
                        <th class="fw-medium">Layanan</th>
                        <th class="fw-medium text-end">Biaya</th>
                        <th class="fw-medium px-4">Status</th>
                        <th class="fw-medium px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data pemeriksaan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $p): ?>
                            <tr>
                                <td class="px-4"><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['id_periksa']) ?></span></td>
                                <td><?= htmlspecialchars($p['tanggal_periksa_fmt']) ?></td>
                                <td class="fw-medium"><?= htmlspecialchars($p['nama_pasien'] ?? '') ?></td>
                                <td><?= htmlspecialchars($p['nama_dokter'] ?? '') ?></td>
                                <td><?= htmlspecialchars($p['nama_layanan'] ?? '') ?></td>
                                <td class="text-end fw-medium"><?= htmlspecialchars($p['biaya_fmt']) ?></td>
                                <td class="px-4"><?= $p['status_badge_html'] ?></td>
                                <td class="px-4">
                                    <div class="d-flex gap-2">
                                        <?php
                                        $currentStatus = $p['status_pemeriksaan'] ?? '';
                                        $transitions = $presenter->getAllowedTransitions($currentStatus);
                                        ?>
                                        <?php foreach ($transitions as $nextStatus): ?>
                                            <form method="post" action="/pemeriksaan/update_status" class="m-0 p-0">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($p['id_periksa']) ?>">
                                                <input type="hidden" name="status_pemeriksaan" value="<?= htmlspecialchars($nextStatus) ?>">
                                                <?php if ($nextStatus === 'Sedang Diperiksa'): ?>
                                                    <button type="submit" class="btn btn-sm btn-info text-white shadow-sm" title="Mulai Periksa">
                                                        <i class="bi bi-play-fill"></i>
                                                    </button>
                                                <?php elseif ($nextStatus === 'Selesai'): ?>
                                                    <button type="submit" class="btn btn-sm btn-success shadow-sm" title="Selesai">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        <?php endforeach; ?>
                                        <?php if ($currentStatus !== 'Selesai'): ?>
                                            <a href="/pemeriksaan/delete?id=<?= urlencode($p['id_periksa']) ?>" class="btn btn-sm btn-light text-danger border" title="Hapus">
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
$baseUrl = '/pemeriksaan';
include __DIR__ . '/../partials/pagination.php';
?>
