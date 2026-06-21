<?php

declare(strict_types=1);

namespace Silk\Repository;

use Silk\Database;

/**
 * Dokter data access. All SQL for the dokter table.
 */
final class DokterRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function insert(array $data): int
    {
        $this->db->execute(
            'INSERT INTO dokter (nama_dokter, spesialisasi, no_izin_praktik, no_hp) VALUES (?, ?, ?, ?)',
            [
                $data['nama_dokter'],
                $data['spesialisasi'] ?? 'THT',
                $data['no_izin_praktik'],
                $data['no_hp'] ?? null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT * FROM dokter WHERE is_deleted = 0 ORDER BY created_at DESC, id_dokter DESC LIMIT ? OFFSET ?',
            [(int) $limit, (int) $offset]
        );
    }

    public function findById(int $id): array
    {
        $rows = $this->db->query('SELECT * FROM dokter WHERE id_dokter = ? AND is_deleted = 0', [$id]);
        return $rows[0] ?? [];
    }

    public function update(int $id, array $data): int
    {
        $fields = [];
        $params = [];
        foreach (['nama_dokter', 'spesialisasi', 'no_izin_praktik', 'no_hp'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = ?";
                $params[] = $data[$col];
            }
        }
        if ($fields === []) return 0;
        $params[] = $id;
        return $this->db->execute(
            'UPDATE dokter SET ' . implode(', ', $fields) . ' WHERE id_dokter = ?',
            $params
        );
    }

    public function delete(int $id): void
    {
        $this->db->execute('UPDATE dokter SET is_deleted = 1 WHERE id_dokter = ?', [$id]);
    }

    public function restore(int $id): void
    {
        $this->db->execute('UPDATE dokter SET is_deleted = 0 WHERE id_dokter = ?', [$id]);
    }

    public function findAllIncludingDeleted(int $limit = 50, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT * FROM dokter ORDER BY created_at DESC, id_dokter DESC LIMIT ? OFFSET ?',
            [(int) $limit, (int) $offset]
        );
    }

    public function count(): int
    {
        $rows = $this->db->query('SELECT COUNT(*) AS n FROM dokter WHERE is_deleted = 0');
        return (int) $rows[0]['n'];
    }

    public function countAllIncludingDeleted(): int
    {
        $rows = $this->db->query('SELECT COUNT(*) AS n FROM dokter');
        return (int) $rows[0]['n'];
    }
}
