<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use RuntimeException;
use Silk\Database;
use Silk\Repository\PemeriksaanRepository;

/**
 * Pemeriksaan entity (thin facade over PemeriksaanRepository).
 *
 * Owns: state machine (TRANSITIONS), transaction orchestration
 * for status transitions (race-safe via SELECT FOR UPDATE), validation.
 *
 * Delegates SQL to PemeriksaanRepository.
 */
final class Pemeriksaan
{
    /** Allowed status transitions: from => [allowed_to_values]. */
    private const TRANSITIONS = [
        'Menunggu'         => ['Sedang Diperiksa', 'Selesai'],
        'Sedang Diperiksa' => ['Menunggu', 'Selesai'],
        'Selesai'          => [], // terminal
    ];

    private const REQUIRED = ['id_pasien', 'id_dokter', 'id_layanan', 'tanggal_periksa', 'keluhan'];

    private PemeriksaanRepository $repo;
    private Database $db;

    public function __construct()
    {
        $this->repo = new PemeriksaanRepository();
        $this->db   = Database::getInstance();
    }

    public function generateKodeOtomatis(): string
    {
        return $this->repo->generateKodeOtomatis();
    }

    /**
     * @param array{id_pasien: string, id_dokter: int, id_layanan: int, tanggal_periksa: string, keluhan: string} $data
     */
    public function create(array $data): string
    {
        $this->validateRequired($data, self::REQUIRED);
        $id = $this->repo->generateKodeOtomatis();
        $data['id_periksa'] = $id;
        $this->repo->insert($data);
        return $id;
    }

    public function read(string $id): array
    {
        return $this->repo->findRaw($id);
    }

    public function readWithJoin(?string $keyword = null): array
    {
        return $this->repo->findAllJoined($keyword);
    }

    public function getById(string $id): array
    {
        return $this->repo->findByIdJoined($id);
    }

    public function readLatest(int $limit = 5): array
    {
        return $this->repo->findLatest($limit);
    }

    /**
     * Update status with state machine validation. Race-safe via SELECT FOR UPDATE.
     */
    public function updateStatus(string $id, string $newStatus): int
    {
        $this->db->beginTransaction();
        try {
            $current = $this->repo->findStatusForUpdate($id);
            if ($current === null) {
                throw new RuntimeException("Pemeriksaan {$id} not found");
            }
            $this->validateTransition($current, $newStatus);
            $n = $this->repo->updateStatus($id, $newStatus);
            $this->db->commit();
            return $n;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        return $this->repo->deleteIfNotSelesai($id) > 0;
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    public function countByDate(string $date): int
    {
        return $this->repo->countByDate($date);
    }

    private function validateRequired(array $data, array $fields): void
    {
        foreach ($fields as $f) {
            if (empty($data[$f])) {
                throw new RuntimeException("Field '{$f}' is required");
            }
        }
    }

    private function validateTransition(string $current, string $new): void
    {
        $allowed = self::TRANSITIONS[$current] ?? [];
        if (!in_array($new, $allowed, true)) {
            throw new RuntimeException("Invalid status transition: {$current} -> {$new}");
        }
    }
}
