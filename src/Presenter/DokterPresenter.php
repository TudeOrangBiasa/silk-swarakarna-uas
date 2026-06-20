<?php

declare(strict_types=1);

namespace Silk\Presenter;

use Silk\Entity\Dokter;
use Silk\Query\DokterQuery;
use Silk\Repository\DokterRepository;

/**
 * Presenter for Dokter views.
 */
final class DokterPresenter
{
    private DokterQuery $query;
    private DokterRepository $repo;

    public function __construct(
        private Dokter $dokter,
        ?DokterQuery $query = null,
        ?DokterRepository $repo = null,
    ) {
        $this->query = $query ?? new DokterQuery();
        $this->repo  = $repo ?? new DokterRepository();
    }

    /**
     * @return array{rows: list<array<string, mixed>>, pagination: array{total: int, page: int, per_page: int, total_pages: int, offset: int, has_next: bool, has_prev: bool}}
     */
    public function getListData(?string $keyword = null, int $page = 1, int $perPage = 20): array
    {
        $perPage = max(1, min(100, $perPage));
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $hasKeyword = $keyword !== null && $keyword !== '';
        $rows = $hasKeyword ? $this->query->searchByName($keyword, $perPage, $offset) : $this->repo->findAll($perPage, $offset);
        $total = $hasKeyword ? $this->query->countSearchByName($keyword) : $this->repo->count();
        return [
            'rows' => array_values(array_map([$this, 'formatRow'], $rows)),
            'pagination' => paginate($total, $page, $perPage),
        ];
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

    /**
     * Options for <select> element: value=id_dokter, label="dr. Nama - Spesialisasi".
     *
     * @return list<array{value: string, label: string}>
     */
    public function getOptions(): array
    {
        $rows = $this->dokter->readForOptions();
        return array_map(fn($r) => [
            'value' => (string) $r['id_dokter'],
            'label' => $r['nama_dokter'] . ' - ' . $r['spesialisasi'],
        ], $rows);
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
