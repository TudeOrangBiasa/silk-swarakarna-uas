<?php

declare(strict_types=1);

namespace Silk\Repository;

use Silk\Database;

/**
 * Pasien data access. All SQL for the pasien table.
 * No validation, no business rules. Entity handles those.
 */
final class PasienRepository
{
    private Database $db;

    private const NULLABLE_COLS = ['pekerjaan', 'golongan_darah', 'riwayat_penyakit', 'alergi', 'foto'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Normalize empty string to null for nullable columns.
     * HTML form sends "" for unset optional fields; DB expects NULL.
     */
    private function nullIfEmpty(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (string) $value;
    }

    public function insert(string $id, array $data): void
    {
        $this->db->execute(
            'INSERT INTO pasien (id_pasien, nama_pasien, tanggal_lahir, jenis_kelamin, pekerjaan, golongan_darah, riwayat_penyakit, alergi, no_hp, alamat, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $id,
                $data['nama_pasien'],
                $data['tanggal_lahir'],
                $data['jenis_kelamin'],
                $this->nullIfEmpty($data['pekerjaan'] ?? null),
                $this->nullIfEmpty($data['golongan_darah'] ?? null),
                $this->nullIfEmpty($data['riwayat_penyakit'] ?? null),
                $this->nullIfEmpty($data['alergi'] ?? null),
                $data['no_hp'],
                $data['alamat'],
                $this->nullIfEmpty($data['foto'] ?? null),
            ]
        );
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT * FROM pasien WHERE is_deleted = 0 ORDER BY created_at DESC, id_pasien DESC LIMIT ? OFFSET ?',
            [(int) $limit, (int) $offset]
        );
    }

    public function findById(string $id): array
    {
        $rows = $this->db->query('SELECT * FROM pasien WHERE id_pasien = ? AND is_deleted = 0', [$id]);
        return $rows[0] ?? [];
    }

    public function update(string $id, array $data): int
    {
        $fields = [];
        $params = [];
        foreach (['nama_pasien', 'tanggal_lahir', 'jenis_kelamin', 'pekerjaan', 'golongan_darah', 'riwayat_penyakit', 'alergi', 'no_hp', 'alamat', 'foto'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = ?";
                $params[] = in_array($col, self::NULLABLE_COLS, true)
                    ? $this->nullIfEmpty($data[$col])
                    : $data[$col];
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
        $this->db->execute('UPDATE pasien SET is_deleted = 1 WHERE id_pasien = ?', [$id]);
    }

    public function restore(string $id): void
    {
        $this->db->execute('UPDATE pasien SET is_deleted = 0 WHERE id_pasien = ?', [$id]);
    }

    public function findAllIncludingDeleted(int $limit = 50, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT * FROM pasien ORDER BY created_at DESC, id_pasien DESC LIMIT ? OFFSET ?',
            [(int) $limit, (int) $offset]
        );
    }

    public function count(): int
    {
        $rows = $this->db->query('SELECT COUNT(*) AS n FROM pasien WHERE is_deleted = 0');
        return (int) $rows[0]['n'];
    }

    public function countAllIncludingDeleted(): int
    {
        $rows = $this->db->query('SELECT COUNT(*) AS n FROM pasien');
        return (int) $rows[0]['n'];
    }
}
