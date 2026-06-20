<?php

declare(strict_types=1);

namespace Silk\Repository;

use Silk\Database;

/**
 * Layanan data access. All SQL for the layanan table.
 */
final class LayananRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function insert(array $data): int
    {
        $this->db->execute(
            'INSERT INTO layanan (nama_layanan, biaya) VALUES (?, ?)',
            [$data['nama_layanan'], (int) $data['biaya']]
        );
        return (int) $this->db->lastInsertId();
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT * FROM layanan ORDER BY created_at DESC, id_layanan DESC LIMIT ? OFFSET ?',
            [(int) $limit, (int) $offset]
        );
    }

    public function findById(int $id): array
    {
        $rows = $this->db->query('SELECT * FROM layanan WHERE id_layanan = ?', [$id]);
        return $rows[0] ?? [];
    }

    public function update(int $id, array $data): int
    {
        $fields = [];
        $params = [];
        foreach (['nama_layanan', 'biaya'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = ?";
                $params[] = $col === 'biaya' ? (int) $data[$col] : $data[$col];
            }
        }
        if ($fields === []) return 0;
        $params[] = $id;
        return $this->db->execute(
            'UPDATE layanan SET ' . implode(', ', $fields) . ' WHERE id_layanan = ?',
            $params
        );
    }

    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM layanan WHERE id_layanan = ?', [$id]);
    }

    public function count(): int
    {
        $rows = $this->db->query('SELECT COUNT(*) AS n FROM layanan');
        return (int) $rows[0]['n'];
    }
}
