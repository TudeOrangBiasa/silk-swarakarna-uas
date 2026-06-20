<?php
$utama_items = [
    ['url' => '/', 'icon' => 'bi-grid-1x2', 'label' => 'Dashboard'],
];
$master_data_items = [
    ['url' => '/pasien', 'icon' => 'bi-people', 'label' => 'Pasien'],
    ['url' => '/dokter', 'icon' => 'bi-heart-pulse', 'label' => 'Dokter'],
    ['url' => '/layanan', 'icon' => 'bi-tags', 'label' => 'Layanan'],
];
$transaksi_items = [
    ['url' => '/pemeriksaan', 'icon' => 'bi-journal-medical', 'label' => 'Pemeriksaan'],
];
?>
<aside class="sidebar sidebar-dark offcanvas offcanvas-lg offcanvas-start" id="sidebarDrawer" tabindex="-1" aria-label="Sidebar navigation" data-bs-config='{"backdrop":true,"scroll":false,"keyboard":true}'>
    <div class="offcanvas-header d-lg-none border-bottom border-secondary border-opacity-25">
        <h5 class="offcanvas-title text-white fw-bold">SILK-Swarakarna</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <!-- Brand (desktop only) -->
        <a href="<?= APP_URL ?>" class="d-none d-lg-flex align-items-center p-4 sidebar-brand fs-5 fw-bold text-nowrap">
            <div class="bg-info text-white rounded p-1 me-2 d-flex align-items-center justify-content-center icon-box-sm">
                <i class="bi bi-soundwave"></i>
            </div>
            <span class="sidebar-brand-text">SILK-Swarakarna</span>
        </a>

        <!-- Nav (scrollable area — scrolls if nav too long) -->
        <div class="flex-grow-1 overflow-y-auto">
            <?php
            $label = 'Utama';
            $items = $utama_items;
            include __DIR__ . '/../partials/_sidebar_section.php';
            ?>

            <?php
            $label = 'Master Data';
            $items = $master_data_items;
            include __DIR__ . '/../partials/_sidebar_section.php';
            ?>

            <?php
            $label = 'Transaksi';
            $items = $transaksi_items;
            include __DIR__ . '/../partials/_sidebar_section.php';
            ?>
        </div>

        <!-- Profile + Logout (sticky bottom, always visible) -->
        <div class="sidebar-profile p-3">
            <div class="d-flex align-items-center mb-3">
                <div class="sidebar-avatar me-2">AD</div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-white text-truncate">Admin</div>
                    <div class="text-light opacity-75 small text-truncate">Resepsionis</div>
                </div>
            </div>
            <a href="/logout" class="btn btn-outline-danger btn-sm w-100 sidebar-logout-btn">
                <i class="bi bi-box-arrow-right me-2"></i><span>Keluar</span>
            </a>
        </div>
    </div>
</aside>
