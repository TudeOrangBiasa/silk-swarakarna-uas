<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

// ---------------------------------------------------------------------------
// Route table: maps page key to view path (relative to views/)
// Keys use dot notation (e.g. pasien.create) matching browser URL paths
// where slashes are normalized to dots for lookup.
// ---------------------------------------------------------------------------
$routes = [
    // GET routes
    ''                   => 'dashboard',
    'pasien'             => 'pasien/index',
    'pasien.create'      => 'pasien/create',
    'pasien.edit'        => 'pasien/edit',
    'pasien.delete'      => 'pasien/delete',
    'dokter'             => 'dokter/index',
    'dokter.create'      => 'dokter/create',
    'dokter.edit'        => 'dokter/edit',
    'dokter.delete'      => 'dokter/delete',
    'layanan'            => 'layanan/index',
    'layanan.create'     => 'layanan/create',
    'layanan.edit'       => 'layanan/edit',
    'layanan.delete'     => 'layanan/delete',
    'pemeriksaan'             => 'pemeriksaan/index',
    'pemeriksaan.create'      => 'pemeriksaan/create',
    'pemeriksaan.update_status' => 'pemeriksaan/update_status',
    'pemeriksaan.delete'      => 'pemeriksaan/delete',
];

// POST handlers: executed before render when method === POST
$postActions = [
    'pasien'                => ['class' => 'Pasien',       'method' => 'create'],
    'pasien.update'         => ['class' => 'Pasien',       'method' => 'update'],
    'dokter'                => ['class' => 'Dokter',       'method' => 'create'],
    'dokter.update'         => ['class' => 'Dokter',       'method' => 'update'],
    'layanan'               => ['class' => 'Layanan',      'method' => 'create'],
    'layanan.update'        => ['class' => 'Layanan',      'method' => 'update'],
    'pemeriksaan'           => ['class' => 'Pemeriksaan',  'method' => 'create'],
    'pemeriksaan.update_status' => ['class' => 'Pemeriksaan',  'method' => 'updateStatus'],
];

// ---------------------------------------------------------------------------
// Parse URL
// ---------------------------------------------------------------------------
// Supports two modes:
//   - Apache: .htaccess rewrites /foo to index.php?url=foo
//   - nginx (DDEV): no .htaccess, parse from REQUEST_URI directly
$url = $_GET['url'] ?? '';
if ($url === '') {
    // nginx fallback: extract path from REQUEST_URI
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $url  = ltrim($path, '/');
}

// Normalize: browser paths use slashes (/pasien/create) but route keys
// use dot notation (pasien.create). Convert slashes to dots for lookup.
$routeKey = str_replace('/', '.', $url);
$method   = $_SERVER['REQUEST_METHOD'];

// ---------------------------------------------------------------------------
// POST handler
// ---------------------------------------------------------------------------
if ($method === 'POST' && isset($postActions[$routeKey])) {
    $action = $postActions[$routeKey];
    $class  = '\\Silk\\Entity\\' . $action['class'];

    if (class_exists($class)) {
        try {
            $instance = new $class();
            // Build args by method signature:
            //   create      → ($_POST)
            //   update      → ($id, $_POST)
            //   updateStatus → ($id, $newStatus)
            // id can come from POST body or GET query (forms embed hidden id;
            // some flows pass via ?id=). newStatus reads status_pemeriksaan from POST.
            $id        = $_POST['id'] ?? $_GET['id'] ?? null;
            $newStatus = $_POST['status_pemeriksaan'] ?? null;
            $args      = match ($action['method']) {
                'create'       => [$_POST],
                'update'       => [$id, $_POST],
                'updateStatus' => [$id, $newStatus],
                default        => [],
            };
            $result = $instance->{$action['method']}(...$args);
            // Success: redirect to the entity list page
            $entity = explode('.', $routeKey)[0];
            $_SESSION['flash_success'] = ucfirst($entity) . ' berhasil disimpan.';
            header('Location: /' . ($entity ?: ''));
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']  = $_POST;
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            header('Location: ' . $referer);
            exit;
        }
    } else {
        $_SESSION['flash_error'] = 'Class ' . $action['class'] . ' not yet implemented.';
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer);
        exit;
    }
}

// ---------------------------------------------------------------------------
// Cleanup stale form data on GET (flash_message clears on read; old_input
// and errors may not be read if a view never calls flash_message())
// ---------------------------------------------------------------------------
if ($method === 'GET') {
    unset($_SESSION['old_input'], $_SESSION['errors']);
}

// ---------------------------------------------------------------------------
// GET route resolution
// ---------------------------------------------------------------------------
$viewPath = $routes[$routeKey] ?? null;

if ($viewPath === null) {
    http_response_code(404);
    $page       = $url; // original URL for display in 404 view
    $page_title = '404 - Halaman Tidak Ditemukan';
    $viewFile   = __DIR__ . '/../views/errors/404.php';
} else {
    $viewFile = __DIR__ . '/../views/' . $viewPath . '.php';
    if (!file_exists($viewFile)) {
        $page_title = ucfirst(str_replace('/', ' - ', $viewPath)) . ' - SILK-Swarakarna';
        $viewFile   = __DIR__ . '/../views/_placeholder.php';
    }
}

// ---------------------------------------------------------------------------
// Default page title (if not already set by route resolution above)
// ---------------------------------------------------------------------------
if (!isset($page_title)) {
    if ($url === '') {
        $page_title = 'Dashboard - SILK-Swarakarna';
    } else {
        $display    = str_replace(['.', '_'], ' ', $routeKey);
        $page_title = ucfirst($display) . ' - SILK-Swarakarna';
    }
}

// ---------------------------------------------------------------------------
// Render layout + view
// ---------------------------------------------------------------------------
require_once __DIR__ . '/../views/layout/header.php';
require $viewFile;
require_once __DIR__ . '/../views/layout/footer.php';
