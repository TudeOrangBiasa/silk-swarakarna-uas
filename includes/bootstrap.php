<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
