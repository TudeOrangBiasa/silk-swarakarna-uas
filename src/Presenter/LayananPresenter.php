<?php

declare(strict_types=1);

namespace Silk\Presenter;

use Silk\Entity\Layanan;

/**
 * Presenter for Layanan views.
 */
final class LayananPresenter
{
    public function __construct(private Layanan $layanan)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getListData(): array
    {
        return array_values(array_map([$this, 'formatRow'], $this->layanan->read()));
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormData(?int $id = null): array
    {
        $row = $id !== null ? $this->layanan->read($id) : [];
        if ($row === []) {
            $row = $this->emptyRow();
        }
        return $this->formatRow($row);
    }

    public function getCount(): int
    {
        return $this->layanan->count();
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, mixed>
     */
    private function formatRow(array $r): array
    {
        $r['biaya_fmt']    = isset($r['biaya']) ? format_rupiah((int) $r['biaya']) : '';
        $r['created_fmt']  = format_datetime((string) ($r['created_at'] ?? ''));
        return $r;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRow(): array
    {
        return [
            'id_layanan'   => '',
            'nama_layanan' => '',
            'biaya'        => 0,
        ];
    }
}
