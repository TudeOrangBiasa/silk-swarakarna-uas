<?php

declare(strict_types=1);

/*
 * Load .env file if it exists, parse into $_ENV and getenv().
 * Fall back to getenv() for hosting environments without .env.
 */

$envFile = __DIR__ . '/../.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        $key   = trim($parts[0]);
        $value = trim($parts[1]);
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Define constants with fallback to getenv()
define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'silk_swarakarna');
define('DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '');
define('APP_URL', $_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost:8000');
define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: 'true') === 'true');
