<?php

declare(strict_types=1);

namespace Silk\Presenter;

use Silk\Entity\Pemeriksaan;

/**
 * Presenter for Pemeriksaan views.
 *
 * Adds status badge HTML, formatted date and biaya (from JOIN).
 */
final class PemeriksaanPresenter
{
    /** Map status ke Bootstrap 5 badge class. */
    private const STATUS_BADGE = [
        'Menunggu'         => 'bg-warning text-dark',
        'Sedang Diperiksa' => 'bg-info text-dark',
        'Selesai'          => 'bg-success',
    ];

    /** Allowed status values for the form select. */
    private const STATUS_OPTIONS = ['Menunggu', 'Sedang Diperiksa', 'Selesai'];

    public function __construct(private Pemeriksaan $pemeriksaan)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getListData(?string $keyword = null): array
    {
        $rows = $this->pemeriksaan->readWithJoin($keyword);
        return array_values(array_map([$this, 'formatRow'], $rows));
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormData(?string $id = null): array
    {
        $row = $id !== null ? $this->pemeriksaan->getById($id) : [];
        if ($row === []) {
            $row = $this->emptyRow();
        }
        return $this->formatRow($row);
    }

    public function getCount(): int
    {
        return $this->pemeriksaan->count();
    }

    public function getCountByDate(string $date): int
    {
        return $this->pemeriksaan->countByDate($date);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getLatest(int $limit = 5): array
    {
        return array_values(array_map([$this, 'formatRow'], $this->pemeriksaan->readLatest($limit)));
    }

    /**
     * Status options for the form select dropdown.
     *
     * @return list<string>
     */
    public function getStatusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    /**
     * Allowed transitions for the status select. Returns the list of statuses
     * the given current status can transition to.
     *
     * @return list<string>
     */
    public function getAllowedTransitions(string $currentStatus): array
    {
        $map = [
            'Menunggu'         => ['Sedang Diperiksa', 'Selesai'],
            'Sedang Diperiksa' => ['Menunggu', 'Selesai'],
            'Selesai'          => [],
        ];
        return $map[$currentStatus] ?? [];
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, mixed>
     */
    private function formatRow(array $r): array
    {
        $r['tanggal_periksa_fmt'] = format_tanggal((string) ($r['tanggal_periksa'] ?? ''));
        $r['biaya_fmt']           = isset($r['biaya']) ? format_rupiah((int) $r['biaya']) : '';
        $r['status_badge_html']   = isset($r['status_pemeriksaan'])
            ? $this->statusBadge((string) $r['status_pemeriksaan'])
            : '';
        $r['created_at_fmt']      = format_datetime((string) ($r['created_at'] ?? ''));
        return $r;
    }

    private function statusBadge(string $status): string
    {
        $cls = self::STATUS_BADGE[$status] ?? 'bg-secondary';
        return sprintf(
            '<span class="badge rounded-pill %s">%s</span>',
            $cls,
            htmlspecialchars($status)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRow(): array
    {
        return [
            'id_periksa'        => '',
            'id_pasien'         => '',
            'nama_pasien'       => '',
            'id_dokter'         => '',
            'nama_dokter'       => '',
            'id_layanan'        => '',
            'nama_layanan'      => '',
            'tanggal_periksa'   => date('Y-m-d'),
            'keluhan'           => '',
            'status_pemeriksaan' => 'Menunggu',
        ];
    }
}
