<?php

declare(strict_types=1);

/** @param array<string, mixed> $context */
function write_log(string $level, string $message, array $context = []): void
{
    $dir = __DIR__ . '/../storage/logs';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $line = sprintf(
        "[%s] %s: %s | context: %s\n",
        date('Y-m-d H:i:s'),
        $level,
        $message,
        json_encode($context, JSON_UNESCAPED_SLASHES)
    );
    file_put_contents($dir . '/error.log', $line, FILE_APPEND | LOCK_EX);
}

function log_error(\Throwable $e, array $context = []): void
{
    $context = array_merge($context, [
        'class' => $e::class,
        'file'  => basename($e->getFile()) . ':' . $e->getLine(),
    ]);
    write_log('ERROR', $e->getMessage(), $context);
}

function log_info(string $message, array $context = []): void
{
    write_log('INFO', $message, $context);
}
