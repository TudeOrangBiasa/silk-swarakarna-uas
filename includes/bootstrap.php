<?php

declare(strict_types=1);

// Security headers (must be set before any output)
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'self\'; style-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net; script-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net; font-src \'self\' https://cdn.jsdelivr.net data:; img-src \'self\' data:; connect-src \'self\' https://cdn.jsdelivr.net; form-action \'self\'; base-uri \'self\'; frame-ancestors \'none\'');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Session config (more secure defaults)
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/logger.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default timezone: Indonesia (WIB) per spec
date_default_timezone_set('Asia/Jakarta');

// Convert PHP errors (notices, warnings, deprecations) to ErrorException
// so they can be caught upstream like any other exception.
set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if ((error_reporting() & $errno) === 0) {
        return false; // respect error_reporting() level
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Error reporting
if (defined('APP_DEBUG') && APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Composer autoloader
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die('<strong>SILK-Swarakarna:</strong> Autoloader not found. Run <code>composer install</code> to generate autoloader.');
}

/**
 * Return the absolute project root path.
 */
function base_path(string $path = ''): string
{
    $root = dirname(__DIR__);
    return $path ? $root . '/' . ltrim($path, '/') : $root;
}
