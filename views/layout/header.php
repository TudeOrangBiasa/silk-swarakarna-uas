<?php
/** @var string $page_title Set this variable before including header.php to override the page title. */
$page_title = $page_title ?? 'SILK-Swarakarna';
$current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body class="bg-body">
    <div class="d-flex min-vh-100 flex-column flex-lg-row">
        <!-- Sidebar -->
        <aside class="sidebar bg-light border-end flex-shrink-0" style="width: 260px; min-height: 100vh;">
            <div class="p-4 position-sticky top-0 d-flex flex-column" style="height: 100vh;">
                <a href="<?= APP_URL ?>" class="d-flex align-items-center mb-4 text-decoration-none text-dark fw-bold fs-5">
                    <div class="bg-dark text-white rounded p-1 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-moon-stars-fill"></i>
                    </div>
                    SILK-Swarakarna
                </a>
                
                <ul class="nav flex-column gap-1">
                    <?php
                    $navLinks = [
                        '/'             => ['icon' => 'bi-grid-1x2', 'label' => 'Dashboard'],
                        '/pasien'       => ['icon' => 'bi-people', 'label' => 'Pasien'],
                        '/dokter'       => ['icon' => 'bi-heart-pulse', 'label' => 'Dokter'],
                        '/layanan'      => ['icon' => 'bi-tags', 'label' => 'Layanan'],
                        '/pemeriksaan'  => ['icon' => 'bi-journal-medical', 'label' => 'Pemeriksaan'],
                    ];
                    foreach ($navLinks as $path => $item):
                        $active = $current_uri === $path || str_starts_with($current_uri, $path . '/');
                        $activeClass = $active ? 'bg-secondary text-dark bg-opacity-10 fw-semibold rounded' : 'text-muted';
                    ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 <?= $activeClass ?>" href="<?= $path ?>">
                                <i class="bi <?= $item['icon'] ?> fs-5"></i>
                                <span><?= $item['label'] ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="mt-auto p-3 bg-white rounded shadow-sm border">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-person-circle fs-3 text-dark me-2"></i>
                        <div>
                            <div class="fw-bold fs-6">Admin</div>
                            <div class="text-muted small">Resepsionis</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="content-wrapper flex-grow-1 bg-body-tertiary d-flex flex-column w-100 overflow-hidden">
            <main class="container-fluid p-4 p-lg-5">
