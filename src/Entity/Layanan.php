<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use RuntimeException;
use Silk\Query\LayananQuery;
use Silk\Repository\LayananRepository;

/**
 * Layanan entity (thin facade over LayananRepository).
 */
final class Layanan
{
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
        if (empty($data['nama_layanan'])) {
            throw new RuntimeException("Field 'nama_layanan' is required");
        }
        if (!isset($data['biaya']) || (int) $data['biaya'] <= 0) {
            throw new RuntimeException("Field 'biaya' must be a positive integer");
        }
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

    public function count(): int
    {
        return $this->repo->count();
    }
}
