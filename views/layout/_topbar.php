<?php
$current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = array_values(array_filter(explode('/', $current_uri)));
$section = $path_parts[0] ?? null;
$action = $path_parts[1] ?? null;

$section_map = [
    'pasien'      => ['section_label' => 'Master Data', 'entity_label' => 'Pasien'],
    'dokter'      => ['section_label' => 'Master Data', 'entity_label' => 'Dokter'],
    'layanan'     => ['section_label' => 'Master Data', 'entity_label' => 'Layanan'],
    'pemeriksaan' => ['section_label' => 'Transaksi',   'entity_label' => 'Pemeriksaan'],
];
$action_labels = [
    'create'         => 'Tambah',
    'edit'           => 'Edit',
    'delete'         => 'Hapus',
    'update_status'  => 'Update Status',
];
?>
<header class="topbar flex-shrink-0 d-flex align-items-center px-3 px-lg-4 py-2 bg-white border-bottom shadow-sm">
    <!-- Mobile toggle: native offcanvas -->
    <button type="button" class="btn btn-link text-body p-0 me-3 border-0 d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebarDrawer" aria-controls="sidebarDrawer" aria-label="Toggle navigation">
        <i class="bi bi-list fs-4"></i>
    </button>
    <!-- Desktop toggle: custom collapse -->
    <button type="button" class="btn btn-link text-body p-0 me-3 border-0 d-none d-lg-block" id="topbarToggleDesktop" aria-label="Toggle navigation">
        <i class="bi bi-list fs-4"></i>
    </button>
    <nav aria-label="breadcrumb" class="flex-grow-1">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
            <?php if ($section && isset($section_map[$section])): ?>
                <li class="breadcrumb-item"><?= $section_map[$section]['section_label'] ?></li>
                <li class="breadcrumb-item"><a href="/<?= $section ?>"><?= $section_map[$section]['entity_label'] ?></a></li>
                <?php if ($action && isset($action_labels[$action])): ?>
                    <li class="breadcrumb-item active" aria-current="page"><?= $action_labels[$action] ?></li>
                <?php endif; ?>
            <?php endif; ?>
        </ol>
    </nav>
    <button type="button" class="btn btn-light topbar-search-btn"
            data-bs-toggle="modal" data-bs-target="#commandPalette"
            title="Tekan ⌘K untuk mencari" aria-label="Buka pencarian">
        <i class="bi bi-search me-2 text-muted"></i>
        <span class="text-muted d-none d-md-inline">Cari atau navigasi...</span>
    </button>
</header>
