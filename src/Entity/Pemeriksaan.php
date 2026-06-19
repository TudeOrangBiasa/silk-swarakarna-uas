<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use RuntimeException;
use Silk\Database;
use Silk\Exception\ValidationException;
use Silk\Query\PemeriksaanQuery;
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
        $errors = [];

        $errors += $this->validateRequired($data, self::REQUIRED);
        $errors += $this->validateTanggalPeriksa($data['tanggal_periksa'] ?? '');
        $errors += $this->validateMaxLength($data['keluhan'] ?? '', 'keluhan', self::MAX_KELUHAN);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $id = $this->query->generateKodeOtomatis();
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

    private function validateRequired(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $f) {
            if (empty($data[$f])) {
                $errors[$f] = $this->fieldLabel($f) . ' wajib diisi';
            }
        }
        return $errors;
    }

    private function validateTanggalPeriksa(string $date): array
    {
        if ($date === '') {
            return [];
        }
        if ($date > date('Y-m-d')) {
            return ['tanggal_periksa' => 'Tanggal periksa tidak boleh di masa depan'];
        }
        return [];
    }

    private function validateMaxLength(string $value, string $field, int $max): array
    {
        if (mb_strlen($value) > $max) {
            return [$field => $this->fieldLabel($field) . " maksimal {$max} karakter"];
        }
        return [];
    }

    private function validateStatusTransition(string $current, string $new): array
    {
        $allowed = self::TRANSITIONS[$current] ?? [];
        if (!in_array($new, $allowed, true)) {
            return ['status_pemeriksaan' => "Transisi status tidak valid: {$current} -> {$new}"];
        }
        return [];
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'id_pasien' => 'Pasien',
            'id_dokter' => 'Dokter',
            'id_layanan' => 'Layanan',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }
}
