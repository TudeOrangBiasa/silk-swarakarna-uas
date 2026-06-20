<?php
/** @var string $page_title Set this variable before including header.php to override the page title. */
$page_title = $page_title ?? 'SILK-Swarakarna';
$current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$active = match (true) {
    str_starts_with($current_uri, '/pasien')      => 'pasien',
    str_starts_with($current_uri, '/dokter')      => 'dokter',
    str_starts_with($current_uri, '/layanan')     => 'layanan',
    str_starts_with($current_uri, '/pemeriksaan') => 'pemeriksaan',
    default                                       => '',
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
</head>
<body class="bg-body">
    <script>
        if (localStorage.getItem('sidebar-collapsed') === '1') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
    <a class="visually-hidden-focusable" href="#mainContent">Skip to main content</a>
    <div class="d-flex min-vh-100 flex-column flex-lg-row">
        <!-- Sidebar (offcanvas on mobile, static on desktop) -->
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

                <!-- Master Data section -->
                <div class="px-3 pt-3 pb-1">
                    <small class="text-uppercase fw-semibold sidebar-section-label">Master Data</small>
                </div>
                <ul class="nav flex-column px-2">
                    <li class="nav-item">
                        <a class="sidebar-link <?= $active === 'pasien' ? 'active' : '' ?>" href="/pasien" <?= $active === 'pasien' ? 'aria-current="page"' : '' ?>>
                            <i class="bi bi-people me-3 fs-5"></i>
                            <span>Pasien</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="sidebar-link <?= $active === 'dokter' ? 'active' : '' ?>" href="/dokter" <?= $active === 'dokter' ? 'aria-current="page"' : '' ?>>
                            <i class="bi bi-heart-pulse me-3 fs-5"></i>
                            <span>Dokter</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="sidebar-link <?= $active === 'layanan' ? 'active' : '' ?>" href="/layanan" <?= $active === 'layanan' ? 'aria-current="page"' : '' ?>>
                            <i class="bi bi-tags me-3 fs-5"></i>
                            <span>Layanan</span>
                        </a>
                    </li>
                </ul>

                <!-- Transaksi section -->
                <div class="px-3 pt-3 pb-1">
                    <small class="text-uppercase fw-semibold sidebar-section-label">Transaksi</small>
                </div>
                <ul class="nav flex-column px-2 mb-3">
                    <li class="nav-item">
                        <a class="sidebar-link <?= $active === 'pemeriksaan' ? 'active' : '' ?>" href="/pemeriksaan" <?= $active === 'pemeriksaan' ? 'aria-current="page"' : '' ?>>
                            <i class="bi bi-journal-medical me-3 fs-5"></i>
                            <span>Pemeriksaan</span>
                        </a>
                    </li>
                </ul>

                <!-- Toggle button + Profile at bottom -->
                <div class="mt-auto">
                    <!-- Profile -->
                    <div class="sidebar-profile p-3">
                        <div class="d-flex align-items-center">
                            <div class="sidebar-avatar me-2">AD</div>
                            <div>
                                <div class="fw-semibold text-white">Admin</div>
                                <div class="text-light opacity-75 small">Resepsionis</div>
                            </div>
                        </div>
                    </div>

                    <!-- Logout button -->
                    <div class="px-3 pb-2">
                        <a href="/logout" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            <span class="logout-text">Keluar</span>
                        </a>
                    </div>

                    <!-- Toggle button (desktop only) -->
                    <button type="button" class="sidebar-toggle-btn d-none d-lg-flex align-items-center w-100" id="sidebarToggle" aria-label="Toggle sidebar">
                        <i class="bi bi-arrow-bar-left sidebar-toggle-icon"></i>
                        <span class="sidebar-toggle-text ms-2">Collapse</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="content-wrapper flex-grow-1 bg-body-tertiary d-flex flex-column w-100 overflow-hidden">
            <!-- Mobile top bar (hamburger + brand) -->
            <div class="d-lg-none bg-primary text-white p-3 d-flex align-items-center shadow-sm">
                <button class="btn btn-link text-white p-0 me-3 border-0" data-bs-toggle="offcanvas" data-bs-target="#sidebarDrawer" aria-label="Open navigation">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <span class="fw-semibold">SILK-Swarakarna</span>
            </div>
            <main id="mainContent" class="container-fluid p-4 p-lg-5">
