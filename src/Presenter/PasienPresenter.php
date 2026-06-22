<?php

declare(strict_types=1);

namespace Silk\Presenter;

use Silk\Entity\Pasien;
use Silk\Query\PasienQuery;
use Silk\Repository\PasienRepository;

/** Pasien views: wraps entity, returns view-ready arrays. */
final class PasienPresenter
{
    private PasienQuery $query;
    private PasienRepository $repo;

    public function __construct(
        private Pasien $pasien,
        ?PasienQuery $query = null,
        ?PasienRepository $repo = null,
    ) {
        $this->query = $query ?? new PasienQuery();
        $this->repo  = $repo ?? new PasienRepository();
    }

    /**
     * @return array{rows: list<array<string, mixed>>, pagination: array{total: int, page: int, per_page: int, total_pages: int, offset: int, has_next: bool, has_prev: bool}}
     */
    public function getListData(?string $keyword = null, int $page = 1, int $perPage = 20, bool $showDeleted = false): array
    {
        $perPage = max(1, min(100, $perPage));
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $hasKeyword = $keyword !== null && $keyword !== '';
        if ($showDeleted) {
            $rows = $hasKeyword ? $this->query->searchByName($keyword, $perPage, $offset) : $this->pasien->readAllIncludingDeleted($perPage, $offset);
            $total = $hasKeyword ? $this->query->countSearchByName($keyword) : $this->pasien->countAllIncludingDeleted();
        } else {
            $rows = $hasKeyword ? $this->query->searchByName($keyword, $perPage, $offset) : $this->repo->findAll($perPage, $offset);
            $total = $hasKeyword ? $this->query->countSearchByName($keyword) : $this->repo->count();
        }
        return [
            'rows' => array_values(array_map([$this, 'formatRow'], $rows)),
            'pagination' => paginate($total, $page, $perPage),
        ];
    }

    /**
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

    /**
     * @return list<array{value: string, label: string}>
     */
    public function getOptions(): array
    {
        $rows = $this->pasien->readForOptions();
        return array_map(fn($r) => [
            'value' => $r['id_pasien'],
            'label' => $r['id_pasien'] . ' - ' . $r['nama_pasien'],
        ], $rows);
    }

    public function getCount(bool $showDeleted = false): int
    {
        return $showDeleted ? $this->pasien->countAllIncludingDeleted() : $this->pasien->count();
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
            'id_pasien'        => '',
            'nama_pasien'      => '',
            'tanggal_lahir'    => '',
            'jenis_kelamin'    => '',
            'pekerjaan'        => '',
            'golongan_darah'   => '',
            'riwayat_penyakit' => '',
            'alergi'           => '',
            'no_hp'            => '',
            'alamat'           => '',
        ];
    }
}
