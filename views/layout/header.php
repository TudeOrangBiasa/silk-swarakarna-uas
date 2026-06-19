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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <nav class="bg-white shadow-md border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="<?= APP_URL ?>" class="text-xl font-bold text-teal-700 tracking-tight">
                    SILK-Swarakarna
                </a>
                <div class="flex space-x-1">
                    <?php
                    $navLinks = [
                        '/'          => 'Dashboard',
                        '/pasien'    => 'Pasien',
                        '/dokter'    => 'Dokter',
                        '/layanan'   => 'Layanan',
                        '/pemeriksaan' => 'Pemeriksaan',
                    ];
                    foreach ($navLinks as $path => $label):
                        $active = $current_uri === $path || str_starts_with($current_uri, $path . '/');
                    ?>
                        <a href="<?= $path ?>"
                           class="px-3 py-2 rounded-md text-sm font-medium transition
                                  <?= $active
                                      ? 'bg-teal-100 text-teal-800'
                                      : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
