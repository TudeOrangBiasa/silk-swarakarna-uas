<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use Silk\Exception\ValidationException;
use Silk\Query\LayananQuery;
use Silk\Repository\LayananRepository;
use Silk\Validation\Rule\MaxLength;
use Silk\Validation\Rule\PositiveNumber;
use Silk\Validation\Rule\Required;
use Silk\Validation\Validator;

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
        (new Validator())->validate($data, [
            'nama_layanan' => [new Required('Nama layanan wajib diisi'), new MaxLength('Nama layanan maksimal 100 karakter', self::MAX_NAMA)],
            'biaya'        => [new PositiveNumber('Biaya harus berupa angka positif')],
        ]);

        return $this->repo->insert($data);
    }

    public function read(?int $id = null): array
    {
        return $id !== null ? $this->repo->findById($id) : $this->repo->findAll();
    }

    public function update(int $id, array $data): int
    {
        $rules = [];

        if (array_key_exists('nama_layanan', $data)) {
            $rules['nama_layanan'] = [new Required('Nama layanan wajib diisi'), new MaxLength('Nama layanan maksimal 100 karakter', self::MAX_NAMA)];
        }
        if (array_key_exists('biaya', $data)) {
            $rules['biaya'] = [new PositiveNumber('Biaya harus berupa angka positif')];
        }

        (new Validator())->validate($data, $rules);

        return $this->repo->update($id, $data);
    }

    public function delete(int $id): bool
    {
        // Soft delete: set is_deleted=1. FK safety net kept for insurance.
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

    public function restore(int $id): void
    {
        $this->repo->restore($id);
    }

    public function readAllIncludingDeleted(int $limit = 50, int $offset = 0): array
    {
        return $this->repo->findAllIncludingDeleted($limit, $offset);
    }

    public function countAllIncludingDeleted(): int
    {
        return $this->repo->countAllIncludingDeleted();
    }

    public function count(): int
    {
        return $this->repo->count();
    }
}
