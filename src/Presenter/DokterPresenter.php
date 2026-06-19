<?php

declare(strict_types=1);

namespace Silk\Presenter;

use Silk\Entity\Dokter;

/**
 * Presenter for Dokter views.
 */
final class DokterPresenter
{
    public function __construct(private Dokter $dokter)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getListData(?string $keyword = null): array
    {
        $rows = $keyword !== null && $keyword !== ''
            ? $this->dokter->search($keyword)
            : $this->dokter->read();
        return array_values(array_map([$this, 'formatRow'], $rows));
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormData(?int $id = null): array
    {
        $row = $id !== null ? $this->dokter->read($id) : [];
        if ($row === []) {
            $row = $this->emptyRow();
        }
        return $this->formatRow($row);
    }

    public function getCount(): int
    {
        return $this->dokter->count();
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, mixed>
     */
    private function formatRow(array $r): array
    {
        $r['created_at_fmt'] = format_datetime((string) ($r['created_at'] ?? ''));
        return $r;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRow(): array
    {
        return [
            'id_dokter'        => '',
            'nama_dokter'      => '',
            'spesialisasi'     => 'THT',
            'no_izin_praktik'  => '',
            'no_hp'            => '',
        ];
    }
}
