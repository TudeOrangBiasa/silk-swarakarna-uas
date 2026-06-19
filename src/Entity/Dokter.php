<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use RuntimeException;
use Silk\Query\DokterQuery;
use Silk\Repository\DokterRepository;

/**
 * Dokter entity (thin facade over DokterRepository).
 */
final class Dokter
{
    private const REQUIRED = ['nama_dokter', 'no_izin_praktik'];

    private DokterRepository $repo;
    private DokterQuery $query;

    public function __construct()
    {
        $this->repo  = new DokterRepository();
        $this->query = new DokterQuery();
    }

    public function create(array $data): int
    {
        $this->validateRequired($data, self::REQUIRED);
        return $this->repo->insert($data);
    }

    public function read(?int $id = null): array
    {
        return $id !== null ? $this->repo->findById($id) : $this->repo->findAll();
    }

    public function update(int $id, array $data): int
    {
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

    private function validateRequired(array $data, array $fields): void
    {
        foreach ($fields as $f) {
            if (empty($data[$f])) {
                throw new RuntimeException("Field '{$f}' is required");
            }
        }
    }
}
