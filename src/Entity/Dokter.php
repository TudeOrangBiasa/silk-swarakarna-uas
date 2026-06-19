<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use Silk\Exception\ValidationException;
use Silk\Query\DokterQuery;
use Silk\Repository\DokterRepository;

/**
 * Dokter entity (thin facade over DokterRepository).
 */
final class Dokter
{
    private const REQUIRED = ['nama_dokter', 'no_izin_praktik'];
    private const MAX_NAMA  = 100;
    private const MAX_IZIN  = 50;
    private const MAX_SPESIALISASI = 100;

    private DokterRepository $repo;
    private DokterQuery $query;

    public function __construct()
    {
        $this->repo  = new DokterRepository();
        $this->query = new DokterQuery();
    }

    public function create(array $data): int
    {
        $errors = [];

        $errors += $this->validateRequired($data, self::REQUIRED);
        $errors += $this->validateMaxLength($data['nama_dokter'] ?? '', 'nama_dokter', self::MAX_NAMA);
        $errors += $this->validateMaxLength($data['no_izin_praktik'] ?? '', 'no_izin_praktik', self::MAX_IZIN);
        $errors += $this->validateNoHp($data['no_hp'] ?? '');
        $errors += $this->validateMaxLength($data['spesialisasi'] ?? '', 'spesialisasi', self::MAX_SPESIALISASI);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        // Default spesialisasi jika tidak ada
        if (!isset($data['spesialisasi']) || $data['spesialisasi'] === '') {
            $data['spesialisasi'] = 'THT';
        }

        return $this->repo->insert($data);
    }

    public function read(?int $id = null): array
    {
        return $id !== null ? $this->repo->findById($id) : $this->repo->findAll();
    }

    public function update(int $id, array $data): int
    {
        $errors = [];

        if (array_key_exists('nama_dokter', $data)) {
            if (empty($data['nama_dokter'])) {
                $errors['nama_dokter'] = 'Nama dokter wajib diisi';
            } else {
                $errors += $this->validateMaxLength($data['nama_dokter'], 'nama_dokter', self::MAX_NAMA);
            }
        }

        if (array_key_exists('no_izin_praktik', $data)) {
            if (empty($data['no_izin_praktik'])) {
                $errors['no_izin_praktik'] = 'No izin praktik wajib diisi';
            } else {
                $errors += $this->validateMaxLength($data['no_izin_praktik'], 'no_izin_praktik', self::MAX_IZIN);
            }
        }

        if (array_key_exists('no_hp', $data)) {
            if (!empty($data['no_hp'])) {
                $errors += $this->validateNoHp($data['no_hp']);
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $this->repo->update($id, $data);
    }

    public function delete(int $id): bool
    {
        try {
            $this->repo->delete($id);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'foreign key')) {
                return false;
            }
            throw $e;
        }
    }

    public function search(string $keyword): array
    {
        return $this->query->searchByName($keyword);
    }

    public function readForOptions(): array
    {
        return $this->query->findDokterForOptions();
    }

    public function count(): int
    {
        return $this->repo->count();
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

    private function validateNoHp(string $phone): array
    {
        if ($phone === '') {
            return [];
        }
        if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            return ['no_hp' => 'No HP harus 10-15 digit angka'];
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

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'no_hp' => 'No HP',
            'no_izin_praktik' => 'No izin praktik',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }
}
