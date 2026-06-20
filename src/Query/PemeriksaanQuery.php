<?php

declare(strict_types=1);

namespace Silk\Query;

use Silk\Database;

/**
 * Pemeriksaan query: complex reads (JOIN, latest, status check, date filter, generate).
 * Separated from PemeriksaanRepository (CQRS).
 */
final class PemeriksaanQuery
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

    public function findAllJoined(?string $keyword = null, int $limit = 50, int $offset = 0): array
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
        $sql .= ' ORDER BY p.tanggal_periksa DESC, p.created_at DESC LIMIT ? OFFSET ?';
        $params[] = (int) $limit;
        $params[] = (int) $offset;
        return $this->db->query($sql, $params);
    }

    public function countAllJoined(?string $keyword = null): int
    {
        $sql = 'SELECT COUNT(*) AS n FROM pemeriksaan p JOIN pasien ps ON p.id_pasien = ps.id_pasien';
        $params = [];
        if ($keyword !== null && $keyword !== '') {
            $sql .= ' WHERE ps.nama_pasien LIKE ?';
            $params[] = '%' . $keyword . '%';
        }
        $rows = $this->db->query($sql, $params);
        return (int) $rows[0]['n'];
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
        // Try prepared statement first. If LIMIT ? fails with native prepares,
        // revert to (int)$limit + interpolation (already safe).
        return $this->db->query(
            "SELECT p.*, ps.nama_pasien, d.nama_dokter, l.nama_layanan, l.biaya
             FROM pemeriksaan p
             JOIN pasien ps ON p.id_pasien = ps.id_pasien
             JOIN dokter d ON p.id_dokter = d.id_dokter
             JOIN layanan l ON p.id_layanan = l.id_layanan
             ORDER BY p.created_at DESC, p.id_periksa DESC
             LIMIT ?",
            [(int) $limit]
        );
    }

    public function findStatusForUpdate(string $id): ?string
    {
        $rows = $this->db->query(
            'SELECT status_pemeriksaan FROM pemeriksaan WHERE id_periksa = ? FOR UPDATE',
            [$id]
        );
        return $rows[0]['status_pemeriksaan'] ?? null;
    }

}
