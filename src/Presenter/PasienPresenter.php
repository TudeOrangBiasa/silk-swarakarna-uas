<?php

declare(strict_types=1);

namespace Silk\Presenter;

use Silk\Entity\Pasien;

/**
 * Presenter for Pasien views.
 *
 * Wraps Pasien entity, returns view-ready arrays.
 * Formatting (dates, phone) applied here so views stay clean.
 */
final class PasienPresenter
{
    public function __construct(private Pasien $pasien)
    {
    }

    /**
     * List data for the table view. Optionally filtered by keyword.
     *
     * @return list<array<string, mixed>>
     */
    public function getListData(?string $keyword = null): array
    {
        $rows = $keyword !== null && $keyword !== ''
            ? $this->pasien->search($keyword)
            : $this->pasien->read();
        return array_values(array_map([$this, 'formatRow'], $rows));
    }

    /**
     * Form data. With $id, pre-filled from existing row. Without, returns empty row.
     *
     * @return array<string, mixed>
     */
    public function getFormData(?string $id = null): array
    {
        $row = $id !== null ? $this->pasien->read($id) : [];
        if ($row === []) {
            $row = $this->emptyRow();
        }
        return $this->formatRow($row);
    }

    public function getCount(): int
    {
        return $this->pasien->count();
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, mixed>
     */
    private function formatRow(array $r): array
    {
        $r['tanggal_lahir_fmt'] = format_tanggal((string) ($r['tanggal_lahir'] ?? ''));
        $r['created_at_fmt']    = format_datetime((string) ($r['created_at'] ?? ''));
        return $r;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRow(): array
    {
        return [
            'id_pasien'      => '',
            'nama_pasien'    => '',
            'tanggal_lahir'  => '',
            'no_hp'          => '',
            'alamat'         => '',
        ];
    }
}
