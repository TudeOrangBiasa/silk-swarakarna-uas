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

    /** @return array{0: string, 1: list<mixed>} */
    private function buildFilter(?string $keyword, ?string $status, ?string $startDate, ?string $endDate): array
    {
        $clauses = [];
        $params = [];
        if ($keyword !== null && $keyword !== '') {
            $clauses[] = 'ps.nama_pasien LIKE ?';
            $params[] = '%' . $keyword . '%';
        }
        if ($status !== null && $status !== '') {
            $clauses[] = 'p.status_pemeriksaan = ?';
            $params[] = $status;
        }
        if ($startDate !== null && $startDate !== '') {
            $clauses[] = 'p.tanggal_periksa >= ?';
            $params[] = $startDate;
        }
        if ($endDate !== null && $endDate !== '') {
            $clauses[] = 'p.tanggal_periksa <= ?';
            $params[] = $endDate;
        }
        $where = $clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses);
        return [$where, $params];
    }

    public function findAllJoined(
        ?string $keyword = null,
        ?string $status = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 50,
        int $offset = 0
    ): array {
        [$where, $params] = $this->buildFilter($keyword, $status, $startDate, $endDate);
        $sql = 'SELECT p.*,
                  ps.nama_pasien,
                  d.nama_dokter, d.spesialisasi,
                  l.nama_layanan, l.biaya
                FROM pemeriksaan p
                JOIN pasien ps ON p.id_pasien = ps.id_pasien
                JOIN dokter d ON p.id_dokter = d.id_dokter
                JOIN layanan l ON p.id_layanan = l.id_layanan' . $where . '
                ORDER BY p.tanggal_periksa DESC, p.created_at DESC LIMIT ? OFFSET ?';
        $params[] = (int) $limit;
        $params[] = (int) $offset;
        return $this->db->query($sql, $params);
    }

    public function countAllJoined(
        ?string $keyword = null,
        ?string $status = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): int {
        [$where, $params] = $this->buildFilter($keyword, $status, $startDate, $endDate);
        $sql = 'SELECT COUNT(*) AS n FROM pemeriksaan p JOIN pasien ps ON p.id_pasien = ps.id_pasien' . $where;
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

    public function getCountByMonth(int $year): array
    {
        $rows = $this->db->query(
            'SELECT MONTH(tanggal_periksa) AS bulan, COUNT(*) AS n
             FROM pemeriksaan
             WHERE YEAR(tanggal_periksa) = ?
             GROUP BY MONTH(tanggal_periksa)',
            [$year]
        );
        $byMonth = array_fill(1, 12, 0);
        foreach ($rows as $r) $byMonth[(int) $r['bulan']] = (int) $r['n'];
        return $byMonth;
    }

    public function getTopLayanan(int $limit = 5): array
    {
        return $this->db->query(
            'SELECT l.nama_layanan, COUNT(*) AS n
             FROM pemeriksaan p
             JOIN layanan l ON p.id_layanan = l.id_layanan
             GROUP BY l.id_layanan, l.nama_layanan
             ORDER BY n DESC
             LIMIT ?',
            [(int) $limit]
        );
    }

    public function getDokterStats(): array
    {
        return $this->db->query(
            'SELECT d.nama_dokter, d.spesialisasi, COUNT(*) AS n
             FROM pemeriksaan p
             JOIN dokter d ON p.id_dokter = d.id_dokter
             GROUP BY d.id_dokter, d.nama_dokter, d.spesialisasi
             ORDER BY n DESC'
        );
    }
}
