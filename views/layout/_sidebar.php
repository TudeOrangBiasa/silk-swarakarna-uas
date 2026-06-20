<?php
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

        <!-- Profile (pushed to bottom) -->
        <div class="mt-auto sidebar-profile p-3">
            <div class="d-flex align-items-center">
                <div class="sidebar-avatar me-2">AD</div>
                <div>
                    <div class="fw-semibold text-white">Admin</div>
                    <div class="text-light opacity-75 small">Resepsionis</div>
                </div>
            </div>
        </div>
    </div>
</aside>
