<?php

declare(strict_types=1);

namespace Silk\Repository;

use Silk\Database;

/**
 * Pemeriksaan data access. All SQL for the pemeriksaan table.
 * No state machine, no transaction — entity handles those.
 */
final class PemeriksaanRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function generateKodeOtomatis(): string
    {
        $year = date('Y');
        $prefix = "TRX-{$year}";
        $rows = $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(id_periksa, 9) AS UNSIGNED)) AS max_num
             FROM pemeriksaan
             WHERE id_periksa LIKE ?",
            [$prefix . '%']
        );
        $next = ((int) ($rows[0]['max_num'] ?? 0)) + 1;
        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
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

    public function findStatusForUpdate(string $id): ?string
    {
        $rows = $this->db->query(
            'SELECT status_pemeriksaan FROM pemeriksaan WHERE id_periksa = ? FOR UPDATE',
            [$id]
        );
        return $rows[0]['status_pemeriksaan'] ?? null;
    }

    public function findAllJoined(?string $keyword = null): array
    {
        $sql = 'SELECT p.*,
                  ps.nama_pasien,
                  d.nama_dokter, d.spesialisasi,
                  l.nama_layanan, l.biaya
                FROM pemeriksaan p
                JOIN pasien ps ON p.id_pasien = ps.id_pasien
                JOIN dokter d ON p.id_dokter = d.id_dokter
                JOIN layanan l ON p.id_layanan = l.id_layanan';
        $params = [];
        if ($keyword !== null && $keyword !== '') {
            $sql .= ' WHERE ps.nama_pasien LIKE ?';
            $params[] = '%' . $keyword . '%';
        }
        $sql .= ' ORDER BY p.tanggal_periksa DESC, p.created_at DESC';
        return $this->db->query($sql, $params);
    }

    public function findByIdJoined(string $id): array
    {
        $rows = $this->db->query(
            'SELECT p.*,
                ps.nama_pasien,
                d.nama_dokter, d.spesialisasi,
                l.nama_layanan, l.biaya
              FROM pemeriksaan p
              JOIN pasien ps ON p.id_pasien = ps.id_pasien
              JOIN dokter d ON p.id_dokter = d.id_dokter
              JOIN layanan l ON p.id_layanan = l.id_layanan
              WHERE p.id_periksa = ?',
            [$id]
        );
        return $rows[0] ?? [];
    }

    public function findLatest(int $limit): array
    {
        return $this->db->query(
            "SELECT p.*, ps.nama_pasien, d.nama_dokter, l.nama_layanan, l.biaya
             FROM pemeriksaan p
             JOIN pasien ps ON p.id_pasien = ps.id_pasien
             JOIN dokter d ON p.id_dokter = d.id_dokter
             JOIN layanan l ON p.id_layanan = l.id_layanan
             ORDER BY p.created_at DESC, p.id_periksa DESC
             LIMIT " . (int) $limit
        );
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
