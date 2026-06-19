<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use RuntimeException;
use Silk\Repository\PasienRepository;

/**
 * Pasien entity (thin facade over PasienRepository).
 *
 * Business rules live here: required-field validation, error translation
 * (FK violation -> false). SQL access delegated to PasienRepository.
 */
final class Pasien
{
    private const REQUIRED = ['nama_pasien', 'tanggal_lahir', 'no_hp', 'alamat'];

    private PasienRepository $repo;

    public function __construct()
    {
        $this->repo = new PasienRepository();
    }

    public function generateKodeOtomatis(): string
    {
        return $this->repo->generateKodeOtomatis();
    }

    /**
     * @param array{nama_pasien: string, tanggal_lahir: string, no_hp: string, alamat: string} $data
     */
    public function create(array $data): string
    {
        $this->validateRequired($data, self::REQUIRED);
        $id = $this->repo->generateKodeOtomatis();
        $this->repo->insert($id, $data);
        return $id;
    }

    public function read(?string $id = null): array
    {
        return $id !== null ? $this->repo->findById($id) : $this->repo->findAll();
    }

    public function update(string $id, array $data): int
    {
        return $this->repo->update($id, $data);
    }

    public function delete(string $id): bool
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
        return $this->repo->searchByName($keyword);
    }

    public function count(): int
    {
        return $this->repo->count();
    }

    private function validateRequired(array $data, array $fields): void
    {
        foreach ($fields as $f) {
            if (empty($data[$f])) {
                throw new RuntimeException("Field '{$f}' is required");
            }
        }
    }
}
