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
