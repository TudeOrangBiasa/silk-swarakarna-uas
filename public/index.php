<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

use Silk\Exception\ValidationException;

/**
 * Set flash + errors + old_input, then redirect to referer (or /).
 * Used by POST handler catch blocks and class_exists fallback.
 */
function redirectBackWithError(string $flashMessage, array $errors = []): never
{
    $_SESSION['flash_error'] = $flashMessage;
    if ($errors !== []) {
        $_SESSION['errors'] = $errors;
    }
    $_SESSION['old_input'] = $_POST;
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

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
    'dokter'                => ['class' => 'Dokter',       'method' => 'create',  'id_type' => 'int'],
    'dokter.update'         => ['class' => 'Dokter',       'method' => 'update',  'id_type' => 'int'],
    'layanan'               => ['class' => 'Layanan',      'method' => 'create',  'id_type' => 'int'],
    'layanan.update'        => ['class' => 'Layanan',      'method' => 'update',  'id_type' => 'int'],
    'pemeriksaan'           => ['class' => 'Pemeriksaan',  'method' => 'create'],
    'pemeriksaan.update_status' => ['class' => 'Pemeriksaan',  'method' => 'updateStatus'],
    'pasien.delete'         => ['class' => 'Pasien',       'method' => 'delete'],
    'dokter.delete'         => ['class' => 'Dokter',       'method' => 'delete',  'id_type' => 'int'],
    'layanan.delete'        => ['class' => 'Layanan',      'method' => 'delete',  'id_type' => 'int'],
    'pemeriksaan.delete'    => ['class' => 'Pemeriksaan',  'method' => 'delete'],
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
            if (isset($action['id_type']) && $action['id_type'] === 'int' && $id !== null) {
                $id = (int) $id;
            }
            $newStatus = $_POST['status_pemeriksaan'] ?? null;
            $args      = match ($action['method']) {
                'create'       => [$_POST],
                'update'       => [$id, $_POST],
                'updateStatus' => [$id, $newStatus],
                'delete'       => [$id],
                default        => [],
            };
            if (in_array($action['method'], ['update', 'updateStatus', 'delete'], true) && empty($id)) {
                redirectBackWithError('ID tidak valid');
            }
            $result = $instance->{$action['method']}(...$args);
            // Success: redirect to the entity list page
            $entity = explode('.', $routeKey)[0];
            $_SESSION['flash_success'] = $action['method'] === 'delete'
                ? ucfirst($entity) . ' berhasil dihapus.'
                : ucfirst($entity) . ' berhasil disimpan.';
            header('Location: /' . ($entity ?: ''));
            exit;
        } catch (ValidationException $e) {
            redirectBackWithError('Validasi gagal, periksa input', $e->getErrors());
        } catch (\Throwable $e) {
            redirectBackWithError($e->getMessage());
        }
    } else {
        redirectBackWithError('Class ' . $action['class'] . ' not yet implemented.');
    }
}

// Logout: clear session and redirect to home
if ($url === 'logout' && $method === 'GET') {
    $_SESSION = [];
    session_destroy();
    header('Location: /');
    exit;
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
// Render layout + view (output-buffered so we can clear session after render)
// ---------------------------------------------------------------------------
ob_start();
require_once __DIR__ . '/../views/layout/header.php';
require $viewFile;
require_once __DIR__ . '/../views/layout/footer.php';
$output = ob_get_clean();

// View has been rendered; old_input and errors are no longer needed.
unset($_SESSION['old_input'], $_SESSION['errors']);

echo $output;
