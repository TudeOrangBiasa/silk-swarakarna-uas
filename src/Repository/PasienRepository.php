<?php

declare(strict_types=1);

namespace Silk\Repository;

use Silk\Database;

/**
 * Pasien data access. All SQL for the pasien table.
 * No validation, no business rules — entity handles those.
 */
final class PasienRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function insert(string $id, array $data): void
    {
        $this->db->execute(
            'INSERT INTO pasien (id_pasien, nama_pasien, tanggal_lahir, no_hp, alamat) VALUES (?, ?, ?, ?, ?)',
            [$id, $data['nama_pasien'], $data['tanggal_lahir'], $data['no_hp'], $data['alamat']]
        );
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT * FROM pasien ORDER BY created_at DESC, id_pasien DESC LIMIT ? OFFSET ?',
            [(int) $limit, (int) $offset]
        );
    }

    public function findById(string $id): array
    {
        $rows = $this->db->query('SELECT * FROM pasien WHERE id_pasien = ?', [$id]);
        return $rows[0] ?? [];
    }

    public function update(string $id, array $data): int
    {
        $fields = [];
        $params = [];
        foreach (['nama_pasien', 'tanggal_lahir', 'no_hp', 'alamat'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = ?";
                $params[] = $data[$col];
            }
        }
        if ($fields === []) return 0;
        $params[] = $id;
        return $this->db->execute(
            'UPDATE pasien SET ' . implode(', ', $fields) . ' WHERE id_pasien = ?',
            $params
        );
    }

    public function delete(string $id): void
    {
        $this->db->execute('DELETE FROM pasien WHERE id_pasien = ?', [$id]);
    }

    public function count(): int
    {
        $rows = $this->db->query('SELECT COUNT(*) AS n FROM pasien');
        return (int) $rows[0]['n'];
    }
}
