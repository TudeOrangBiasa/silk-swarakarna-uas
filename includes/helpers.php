<?php

declare(strict_types=1);

/**
 * Format ISO date (Y-m-d) to Indonesian display (d/m/Y).
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

/** Format integer rupiah with Indonesian thousand sep. */
function format_rupiah(int $n): string
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/** Format ISO datetime to Indonesian display (d/m/Y H:i). */
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

/** Get old POST input value (form repopulation). */
function old_input(string $key, string $default = ''): string
{
    return (string) ($_SESSION['old_input'][$key] ?? $default);
}

/** Check if a field has a stored error. */
function has_error(string $key): bool
{
    return isset($_SESSION['errors'][$key]);
}

/** Get error message for a field. */
function error_for(string $key): ?string
{
    return $_SESSION['errors'][$key] ?? null;
}

/** Consume and return flash message (success or error). Clears session keys. */
function flash_message(): ?array
{
    if (isset($_SESSION['flash_success'])) {
        $msg = ['type' => 'success', 'message' => (string) $_SESSION['flash_success']];
        unset($_SESSION['flash_success']);
        return $msg;
    }
    if (isset($_SESSION['flash_error'])) {
        $msg = ['type' => 'error', 'message' => (string) $_SESSION['flash_error']];
        unset($_SESSION['flash_error']);
        return $msg;
    }
    return null;
}

/** Get a query string parameter with default. */
function query_param(string $key, string $default = ''): string
{
    return (string) ($_GET[$key] ?? $default);
}

/** Calculate pagination metadata. */
function paginate(int $total, int $currentPage, int $perPage = 20): array
{
    $perPage = max(1, min(100, $perPage));
    $currentPage = max(1, $currentPage);
    $totalPages = $total === 0 ? 0 : (int) ceil($total / $perPage);
    $offset = ($currentPage - 1) * $perPage;
    return [
        'total' => $total,
        'page' => $currentPage,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_next' => $currentPage < $totalPages,
        'has_prev' => $currentPage > 1,
    ];
}
