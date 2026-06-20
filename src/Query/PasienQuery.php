<?php

declare(strict_types=1);

namespace Silk\Query;

use Silk\Database;

/**
 * Pasien query: search and code generation.
 * Separated from PasienRepository (CQRS: complex reads here, basic CRUD in repo).
 */
final class PasienQuery
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
            'SELECT * FROM pasien WHERE nama_pasien LIKE ? ORDER BY nama_pasien ASC LIMIT ? OFFSET ?',
            ['%' . $keyword . '%', (int) $limit, (int) $offset]
        );
    }

    public function countSearchByName(string $keyword): int
    {
        $rows = $this->db->query(
            'SELECT COUNT(*) AS n FROM pasien WHERE nama_pasien LIKE ?',
            ['%' . $keyword . '%']
        );
        return (int) $rows[0]['n'];
    }

    public function findPasienForOptions(): array
    {
        return $this->db->query('SELECT id_pasien, nama_pasien FROM pasien ORDER BY id_pasien ASC');
    }

    public function generateKodeOtomatis(): string
    {
        $rows = $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(id_pasien, 4) AS UNSIGNED)) AS max_num
             FROM pasien
             WHERE id_pasien REGEXP '^RM-[0-9]+$'"
        );
        $next = ((int) ($rows[0]['max_num'] ?? 0)) + 1;
        return 'RM-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
