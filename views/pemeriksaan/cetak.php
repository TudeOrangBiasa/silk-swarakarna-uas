<?php
/**
 * Standalone print-friendly report (no layout wrapper).
 */
use Silk\Entity\Pemeriksaan;
use Silk\Presenter\PemeriksaanPresenter;

$keyword   = query_param('search');
$status    = query_param('status');
$startDate = query_param('startDate');
$endDate   = query_param('endDate');

$presenter = new PemeriksaanPresenter(new Pemeriksaan());
$data      = $presenter->getCetakData($keyword, $status, $startDate, $endDate);
$rows      = $data['rows'];
$total     = $data['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pemeriksaan - SILK-Swarakarna</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { padding: 1.5rem; font-size: 14px; }
        .report-title { margin-bottom: 0.25rem; }
        .report-subtitle { color: #6c757d; margin-bottom: 1.5rem; }
        .table th { background: #f8f9fa; }
        .grand-total { font-size: 1.1rem; font-weight: 700; text-align: right; padding: 0.75rem 0; border-top: 2px solid #212529; margin-top: 0.5rem; }
        .btn-cetak { margin-bottom: 1rem; }
        .filter-form { background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }

        @media print {
            .btn-cetak, .filter-form, .no-print { display: none !important; }
            body { padding: 0; }
            .table { width: 100%; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>
    <div class="no-print btn-cetak">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Cetak
        </button>
        <a href="/pemeriksaan" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <form method="get" class="filter-form no-print">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-medium">Tanggal Awal</label>
                <input type="date" name="startDate" class="form-control form-control-sm" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-medium">Tanggal Akhir</label>
                <input type="date" name="endDate" class="form-control form-control-sm" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-medium">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="Menunggu" <?= $status === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="Sedang Diperiksa" <?= $status === 'Sedang Diperiksa' ? 'selected' : '' ?>>Sedang Diperiksa</option>
                    <option value="Selesai" <?= $status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-medium">Cari</label>
                <input type="search" name="search" class="form-control form-control-sm" placeholder="Pasien/dokter..." value="<?= htmlspecialchars($keyword) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Tampilkan</button>
            </div>
        </div>
    </form>

    <h1 class="report-title">Laporan Pemeriksaan</h1>
    <p class="report-subtitle">
        <?php if ($startDate !== '' && $endDate !== ''): ?>
            Periode: <?= htmlspecialchars(format_tanggal($startDate)) ?> - <?= htmlspecialchars(format_tanggal($endDate)) ?>
        <?php elseif ($startDate !== ''): ?>
            Dari: <?= htmlspecialchars(format_tanggal($startDate)) ?>
        <?php elseif ($endDate !== ''): ?>
            Sampai: <?= htmlspecialchars(format_tanggal($endDate)) ?>
        <?php else: ?>
            Semua Periode
        <?php endif; ?>
        <?php if ($status !== ''): ?> &middot; Status: <?= htmlspecialchars($status) ?><?php endif; ?>
        <?php if ($keyword !== ''): ?> &middot; Cari: <?= htmlspecialchars($keyword) ?><?php endif; ?>
    </p>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>No Transaksi</th>
                <th>Tanggal</th>
                <th>Pasien</th>
                <th>Dokter</th>
                <th>Layanan</th>
                <th class="text-end">Biaya</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td></tr>
            <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($r['id_periksa']) ?></td>
                        <td><?= htmlspecialchars($r['tanggal_periksa_fmt']) ?></td>
                        <td><?= htmlspecialchars($r['nama_pasien'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['nama_dokter'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['nama_layanan'] ?? '') ?></td>
                        <td class="text-end"><?= htmlspecialchars($r['biaya_fmt']) ?></td>
                        <td><?= htmlspecialchars($r['status_pemeriksaan'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="grand-total">Total Pendapatan: <?= format_rupiah($total) ?></div>
</body>
</html>
