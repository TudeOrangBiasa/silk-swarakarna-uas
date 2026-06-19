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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= APP_URL ?>">SILK-Swarakarna</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto">
                    <?php
                    $navLinks = [
                        '/'             => 'Dashboard',
                        '/pasien'       => 'Pasien',
                        '/dokter'       => 'Dokter',
                        '/layanan'      => 'Layanan',
                        '/pemeriksaan'  => 'Pemeriksaan',
                    ];
                    foreach ($navLinks as $path => $label):
                        $active = $current_uri === $path || str_starts_with($current_uri, $path . '/');
                    ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $active ? ' active' : '' ?>" href="<?= $path ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                                <?= $label ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container py-4">
