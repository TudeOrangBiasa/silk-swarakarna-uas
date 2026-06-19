<?php

declare(strict_types=1);

/**
 * Format ISO date (Y-m-d) to Indonesian display (d/m/Y).
 *
 * @param string $iso Date in Y-m-d format
 * @return string Display in d/m/Y, or original string if parse fails
 */
function format_tanggal(string $iso): string
{
    if ($iso === '') {
        return '';
    }
    $d = DateTime::createFromFormat('Y-m-d', $iso);
    if (!$d) {
        return $iso;
    }
    return $d->format('d/m/Y');
}

/**
 * Format integer rupiah to "Rp 250.000" (Indonesian thousand sep).
 *
 * @param int $n Amount in IDR
 */
function format_rupiah(int $n): string
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/**
 * Format ISO datetime (Y-m-d H:i:s) to Indonesian display (d/m/Y H:i).
 *
 * @param string $iso Datetime in Y-m-d H:i:s format
 */
function format_datetime(string $iso): string
{
    if ($iso === '') {
        return '';
    }
    try {
        $d = new DateTime($iso);
        return $d->format('d/m/Y H:i');
    } catch (\Throwable) {
        return $iso;
    }
}

/**
 * Get old POST input value (for form repopulation after validation error).
 * Falls back to empty string.
 */
function old_input(string $key, string $default = ''): string
{
    return (string) ($_SESSION['old_input'][$key] ?? $default);
}

/**
 * Check if a field has a stored error.
 */
function has_error(string $key): bool
{
    return isset($_SESSION['errors'][$key]);
}

/**
 * Get error message for a field (if any).
 */
function error_for(string $key): ?string
{
    return $_SESSION['errors'][$key] ?? null;
}

/**
 * Consume and return the flash message (success or error).
 * Returns null if no flash. After calling, the session keys are cleared.
 *
 * @return array{type: 'success'|'error', message: string}|null
 */
function flash_message(): ?array
{
    if (isset($_SESSION['flash_success'])) {
        $msg = ['type' => 'success', 'message' => (string) $_SESSION['flash_success']];
        unset($_SESSION['flash_success'], $_SESSION['old_input'], $_SESSION['errors']);
        return $msg;
    }
    if (isset($_SESSION['flash_error'])) {
        $msg = ['type' => 'error', 'message' => (string) $_SESSION['flash_error']];
        unset($_SESSION['flash_error'], $_SESSION['old_input'], $_SESSION['errors']);
        return $msg;
    }
    return null;
}

/**
 * Get a query string parameter with default.
 */
function query_param(string $key, string $default = ''): string
{
    return (string) ($_GET[$key] ?? $default);
}
