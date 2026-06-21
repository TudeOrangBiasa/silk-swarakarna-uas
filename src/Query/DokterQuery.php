<?php

declare(strict_types=1);

namespace Silk\Query;

use Silk\Database;

final class DokterQuery
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Search by name (LIKE %keyword%). Leading wildcard means B-tree index
     * is not used; for >10k rows consider FULLTEXT index or prefix search.
     */
    public function searchByName(string $keyword, int $limit = 50, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT * FROM dokter WHERE is_deleted = 0 AND nama_dokter LIKE ? ORDER BY nama_dokter ASC LIMIT ? OFFSET ?',
            ['%' . $keyword . '%', (int) $limit, (int) $offset]
        );
    }

    public function countSearchByName(string $keyword): int
    {
        $rows = $this->db->query(
            'SELECT COUNT(*) AS n FROM dokter WHERE is_deleted = 0 AND nama_dokter LIKE ?',
            ['%' . $keyword . '%']
        );
        return (int) $rows[0]['n'];
    }

    public function findDokterForOptions(): array
    {
        return $this->db->query('SELECT id_dokter, nama_dokter, spesialisasi FROM dokter WHERE is_deleted = 0 ORDER BY nama_dokter ASC');
    }
}
