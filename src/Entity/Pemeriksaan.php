<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use RuntimeException;
use Silk\Database;
use Silk\Exception\ValidationException;
use Silk\Query\PemeriksaanQuery;
use Silk\Repository\PemeriksaanRepository;
use Silk\Validation\Rule\DateNotFuture;
use Silk\Validation\Rule\MaxLength;
use Silk\Validation\Rule\Required;
use Silk\Validation\Validator;

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

    private const MAX_KELUHAN = 1000;

    private PemeriksaanRepository $repo;
    private PemeriksaanQuery $query;
    private Database $db;

    public function __construct()
    {
        $this->repo  = new PemeriksaanRepository();
        $this->query = new PemeriksaanQuery();
        $this->db    = Database::getInstance();
    }

    public function generateKodeOtomatis(): string
    {
        return $this->query->generateKodeOtomatis();
    }

    /**
     * @param array{id_pasien: string, id_dokter: int, id_layanan: int, tanggal_periksa: string, keluhan: string} $data
     */
    public function create(array $data): string
    {
        (new Validator())->validate($data, [
            'id_pasien'       => [new Required('Pasien wajib diisi')],
            'id_dokter'       => [new Required('Dokter wajib diisi')],
            'id_layanan'      => [new Required('Layanan wajib diisi')],
            'tanggal_periksa' => [new Required('Tanggal periksa wajib diisi'), new DateNotFuture('Tanggal periksa tidak boleh di masa depan')],
            'keluhan'         => [new Required('Keluhan wajib diisi'), new MaxLength('Keluhan maksimal 1000 karakter', self::MAX_KELUHAN)],
        ]);

        $maxAttempts = 3;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $this->db->beginTransaction();
            try {
                $id = $this->query->generateKodeOtomatis();
                $data['id_periksa'] = $id;
                $this->repo->insert($data);
                $this->db->commit();
                return $id;
            } catch (PDOException $e) {
                $this->db->rollBack();
                if (!self::isDuplicateKeyError($e)) throw $e;
                if ($attempt === $maxAttempts) {
                    throw new \RuntimeException("Gagal generate id unik setelah {$maxAttempts}x percobaan");
                }
            }
        }
    }

    private static function isDuplicateKeyError(PDOException $e): bool
    {
        return str_contains($e->getMessage(), 'Duplicate');
    }

    public function read(string $id): array
    {
        return $this->repo->findRaw($id);
    }

    public function readWithJoin(?string $keyword = null): array
    {
        return $this->query->findAllJoined($keyword);
    }

    public function getById(string $id): array
    {
        return $this->query->findByIdJoined($id);
    }

    public function readLatest(int $limit = 5): array
    {
        return $this->query->findLatest($limit);
    }

    /**
     * Update status with state machine validation. Race-safe via SELECT FOR UPDATE.
     */
    public function updateStatus(string $id, string $newStatus): int
    {
        $this->db->beginTransaction();
        try {
            $current = $this->query->findStatusForUpdate($id);
            if ($current === null) {
                throw new RuntimeException("Pemeriksaan {$id} not found");
            }
            $statusErrors = $this->validateStatusTransition($current, $newStatus);
            if ($statusErrors !== []) {
                throw new ValidationException($statusErrors);
            }
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

    /**
     * Get allowed status transitions for the given current status.
     *
     * @return list<string>
     */
    public function getAllowedTransitions(string $currentStatus): array
    {
        return self::TRANSITIONS[$currentStatus] ?? [];
    }

    private function validateStatusTransition(string $current, string $new): array
    {
        $allowed = self::TRANSITIONS[$current] ?? [];
        if (!in_array($new, $allowed, true)) {
            return ['status_pemeriksaan' => "Transisi status tidak valid: {$current} -> {$new}"];
        }
        return [];
    }
}
