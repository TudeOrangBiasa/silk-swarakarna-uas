<?php
/**
 * Dashboard View
 * Issue #38 — 4 widget summary + 5 pemeriksaan terbaru
 */

use Silk\Entity\Pemeriksaan;
use Silk\Presenter\PemeriksaanPresenter;

$presenter         = new PemeriksaanPresenter(new Pemeriksaan());
$stats             = $presenter->getDashboardStats();
$totalPasien         = $stats['total_pasien'];
$totalDokter         = $stats['total_dokter'];
$totalLayanan        = $stats['total_layanan'];
$totalPeriksaHariIni = $stats['pemeriksaan_hari_ini'];
$pendapatanBulanIni  = $stats['pendapatan_bulan_ini'];
$latestPeriksa       = $presenter->getLatest(5);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-semibold mb-1">Selamat Datang, Admin!</h1>
        <p class="text-muted mb-0">Overview klinik hari ini.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
        <!-- HERO: Pasien with trend + sparkline + 2 CTAs -->
        <div class="card border-0 shadow-sm rounded-4 h-100 text-white overflow-hidden" style="background: linear-gradient(135deg, #0e7490 0%, #0d9488 100%);">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <small class="text-uppercase fw-semibold opacity-75 d-block mb-1">Total Pasien</small>
                        <div class="display-3 fw-bold lh-1"><?= $totalPasien ?></div>
                    </div>
                    <i class="bi bi-people-fill hero-icon"></i>
                </div>

                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill">
                        <i class="bi bi-arrow-up-short"></i> +2 minggu ini
                    </span>
                    <small class="opacity-75">vs minggu lalu</small>
                </div>

                <div class="d-flex align-items-end gap-1 mb-3" style="height: 32px;">
                    <div class="bg-white bg-opacity-25 spark-bar" style="height: 40%;"></div>
                    <div class="bg-white bg-opacity-25 spark-bar" style="height: 55%;"></div>
                    <div class="bg-white bg-opacity-25 spark-bar" style="height: 35%;"></div>
                    <div class="bg-white bg-opacity-50 spark-bar" style="height: 70%;"></div>
                    <div class="bg-white bg-opacity-50 spark-bar" style="height: 60%;"></div>
                    <div class="bg-white bg-opacity-75 spark-bar" style="height: 85%;"></div>
                    <div class="bg-white spark-bar" style="height: 100%;"></div>
                </div>

                <div class="d-flex gap-2">
                    <a href="/pasien" class="btn btn-light btn-sm fw-semibold">
                        Lihat semua <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="/pasien/create" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="row g-4 h-100">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="text-muted fw-medium small">Total Dokter</span>
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center icon-box">
                                <i class="bi bi-heart-pulse fs-5 text-primary"></i>
                            </div>
                        </div>
                        <div class="display-6 fw-bold text-dark"><?= $totalDokter ?></div>
                        <a href="/dokter" class="text-decoration-none small text-primary mt-2 d-inline-block">Lihat Dokter &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="text-muted fw-medium small">Total Layanan</span>
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center icon-box">
                                <i class="bi bi-tags fs-5 text-primary"></i>
                            </div>
                        </div>
                        <div class="display-6 fw-bold text-dark"><?= $totalLayanan ?></div>
                        <a href="/layanan" class="text-decoration-none small text-primary mt-2 d-inline-block">Lihat Layanan &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="text-muted fw-medium small">Pemeriksaan Hari Ini</span>
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center icon-box">
                                <i class="bi bi-journal-medical fs-5 text-primary"></i>
                            </div>
                        </div>
                        <div class="display-6 fw-bold text-dark"><?= $totalPeriksaHariIni ?></div>
                        <a href="/pemeriksaan" class="text-decoration-none small text-primary mt-2 d-inline-block">Lihat Pemeriksaan &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="text-muted fw-medium small">Pendapatan Bulan Ini</span>
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center icon-box">
                        <i class="bi bi-cash-stack fs-5 text-primary"></i>
                    </div>
                </div>
                <div class="display-6 fw-bold text-dark"><?= format_rupiah($pendapatanBulanIni) ?></div>
                <a href="/pemeriksaan/cetak" class="text-decoration-none small text-primary mt-2 d-inline-block">Lihat Laporan &rarr;</a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <h2 class="h6 fw-bold mb-0">Pemeriksaan Terbaru</h2>
    </div>
    <div class="card-body p-0 mt-3">
        <div class="table-responsive px-2 pb-2">
            <table class="table table-borderless table-hover align-middle mb-0">
                <thead class="text-muted small border-bottom">
                    <tr>
                        <th class="fw-medium px-4">ID Periksa</th>
                        <th class="fw-medium">Tanggal</th>
                        <th class="fw-medium">Pasien</th>
                        <th class="fw-medium">Dokter</th>
                        <th class="fw-medium">Layanan</th>
                        <th class="fw-medium text-end">Biaya</th>
                        <th class="fw-medium px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($latestPeriksa)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pemeriksaan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($latestPeriksa as $row): ?>
                            <tr>
                                <td class="px-4"><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['id_periksa']) ?></span></td>
                                <td><?= htmlspecialchars($row['tanggal_periksa_fmt']) ?></td>
                                <td class="fw-medium"><?= htmlspecialchars($row['nama_pasien'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['nama_dokter'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['nama_layanan'] ?? '') ?></td>
                                <td class="text-end fw-medium"><?= htmlspecialchars($row['biaya_fmt']) ?></td>
                                <td class="px-4"><?= $row['status_badge_html'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
