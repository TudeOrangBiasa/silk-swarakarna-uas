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
$data = $presenter->getListData($keyword, $page);
$rows = $data['rows'];
$pagination = $data['pagination'];
$flash = flash_message();
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

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
                            <tr>
                                <td class="px-4 text-muted"><?= $i + 1 ?></td>
                                <td class="fw-medium"><?= htmlspecialchars($d['nama_dokter']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($d['no_izin_praktik'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($d['spesialisasi']) ?></td>
                                <td><?= htmlspecialchars($d['no_hp'] ?? '-') ?></td>
                                <td class="px-4">
                                    <div class="d-flex gap-2">
                                        <a href="/dokter/edit?id=<?= (int) $d['id_dokter'] ?>" class="btn btn-sm btn-light text-secondary border" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="/dokter/delete?id=<?= (int) $d['id_dokter'] ?>" class="btn btn-sm btn-light text-danger border" title="Hapus">
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
$baseUrl = '/dokter';
include __DIR__ . '/../partials/pagination.php';
?>
