<?php

declare(strict_types=1);

namespace Silk\Repository;

use Silk\Database;

/**
 * Pemeriksaan data access. All SQL for the pemeriksaan table.
 * No state machine, no transaction. Entity handles those.
 */
final class PemeriksaanRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function insert(array $data): void
    {
        $this->db->execute(
            'INSERT INTO pemeriksaan (id_periksa, id_pasien, id_dokter, id_layanan, tanggal_periksa, keluhan, status_pemeriksaan) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['id_periksa'],
                $data['id_pasien'],
                (int) $data['id_dokter'],
                (int) $data['id_layanan'],
                $data['tanggal_periksa'],
                $data['keluhan'],
                'Menunggu',
            ]
        );
    }

    public function findRaw(string $id): array
    {
        $rows = $this->db->query('SELECT * FROM pemeriksaan WHERE id_periksa = ?', [$id]);
        return $rows[0] ?? [];
    }

    public function updateStatus(string $id, string $newStatus): int
    {
        return $this->db->execute(
            'UPDATE pemeriksaan SET status_pemeriksaan = ? WHERE id_periksa = ?',
            [$newStatus, $id]
        );
    }

    public function deleteIfNotSelesai(string $id): int
    {
        return $this->db->execute(
            'DELETE FROM pemeriksaan WHERE id_periksa = ? AND status_pemeriksaan != ?',
            [$id, 'Selesai']
        );
    }

    public function count(): int
    {
        $rows = $this->db->query('SELECT COUNT(*) AS n FROM pemeriksaan');
        return (int) $rows[0]['n'];
    }

    public function countByDate(string $date): int
    {
        $rows = $this->db->query(
            'SELECT COUNT(*) AS n FROM pemeriksaan WHERE tanggal_periksa = ?',
            [$date]
        );
        return (int) $rows[0]['n'];
    }
}
