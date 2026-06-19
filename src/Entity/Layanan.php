<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use Silk\Exception\ValidationException;
use Silk\Query\LayananQuery;
use Silk\Repository\LayananRepository;

/**
 * Layanan entity (thin facade over LayananRepository).
 */
final class Layanan
{
    private const MAX_NAMA = 100;

    private LayananRepository $repo;
    private LayananQuery $query;

    public function __construct()
    {
        $this->repo  = new LayananRepository();
        $this->query = new LayananQuery();
    }

    public function readForOptions(): array
    {
        return $this->query->findLayananForOptions();
    }

    public function create(array $data): int
    {
        $errors = [];

        if (empty($data['nama_layanan'])) {
            $errors['nama_layanan'] = 'Nama layanan wajib diisi';
        } else {
            $errors += $this->validateMaxLength($data['nama_layanan'], 'nama_layanan', self::MAX_NAMA);
        }

        $errors += $this->validateBiaya($data['biaya'] ?? null);

        if ($errors !== []) {
            throw new ValidationException($errors);
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

        if (array_key_exists('nama_layanan', $data)) {
            if (empty($data['nama_layanan'])) {
                $errors['nama_layanan'] = 'Nama layanan wajib diisi';
            } else {
                $errors += $this->validateMaxLength($data['nama_layanan'], 'nama_layanan', self::MAX_NAMA);
            }
        }

        if (array_key_exists('biaya', $data)) {
            $errors += $this->validateBiaya($data['biaya']);
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

    public function count(): int
    {
        return $this->repo->count();
    }

    private function validateBiaya(mixed $biaya): array
    {
        if ($biaya === null || $biaya === '') {
            return [];
        }
        $value = (int) $biaya;
        if ($value <= 0) {
            return ['biaya' => 'Biaya harus berupa angka positif'];
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
        return ucfirst(str_replace('_', ' ', $field));
    }
}
