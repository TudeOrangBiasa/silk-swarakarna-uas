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
    public function searchByName(string $keyword): array
    {
        return $this->db->query(
            'SELECT * FROM dokter WHERE nama_dokter LIKE ? ORDER BY nama_dokter ASC',
            ['%' . $keyword . '%']
        );
    }

    public function findDokterForOptions(): array
    {
        return $this->db->query('SELECT id_dokter, nama_dokter, spesialisasi FROM dokter ORDER BY nama_dokter ASC');
    }
}
