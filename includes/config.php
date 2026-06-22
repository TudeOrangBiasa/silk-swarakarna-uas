<?php

declare(strict_types=1);

// Hardcoded defaults. Source of truth.
// Override via: (1) shell env, (2) DDEV auto-injected env, (3) optional .env file.
$config = [
    'DB_HOST'   => '127.0.0.1',
    'DB_PORT'   => '3306',
    'DB_NAME'   => 'silk_swarakarna',
    'DB_USER'   => 'root',
    'DB_PASS'   => '',
    'APP_URL'   => 'http://localhost:8000',
    'APP_DEBUG' => true,
    'APP_ENV'   => 'development',
];

// .env is optional. If present, load values into $_ENV + getenv().
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        [$key, $value] = [trim($parts[0]), trim($parts[1])];
        if (array_key_exists($key, $config)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Env var (shell, DDEV, or .env) overrides default. Empty string treated as unset.
foreach ($config as $key => $default) {
    $envValue = $_ENV[$key] ?? getenv($key);
    $final = ($envValue === false || $envValue === '') ? $default : $envValue;
    if ($key === 'APP_DEBUG') {
        $final = $final === true || $final === 'true';
    }
    define($key, $final);
}

define('BASE_URL', APP_URL);
