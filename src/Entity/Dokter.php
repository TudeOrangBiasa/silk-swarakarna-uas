<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use Silk\Exception\ValidationException;
use Silk\Query\DokterQuery;
use Silk\Repository\DokterRepository;
use Silk\Validation\Rule\MaxLength;
use Silk\Validation\Rule\PhoneFormat;
use Silk\Validation\Rule\Required;
use Silk\Validation\Validator;

/**
 * Dokter entity (thin facade over DokterRepository).
 */
final class Dokter
{
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
        (new Validator())->validate($data, [
            'nama_dokter'      => [new Required('Nama dokter wajib diisi'), new MaxLength('Nama dokter maksimal 100 karakter', self::MAX_NAMA)],
            'no_izin_praktik'  => [new Required('No izin praktik wajib diisi'), new MaxLength('No izin praktik maksimal 50 karakter', self::MAX_IZIN)],
            'no_hp'            => [new PhoneFormat('No HP harus 10-15 digit angka')],
            'spesialisasi'     => [new MaxLength('Spesialisasi maksimal 100 karakter', self::MAX_SPESIALISASI)],
        ]);

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
        $rules = [];

        if (array_key_exists('nama_dokter', $data)) {
            $rules['nama_dokter'] = [new Required('Nama dokter wajib diisi'), new MaxLength('Nama dokter maksimal 100 karakter', self::MAX_NAMA)];
        }
        if (array_key_exists('no_izin_praktik', $data)) {
            $rules['no_izin_praktik'] = [new Required('No izin praktik wajib diisi'), new MaxLength('No izin praktik maksimal 50 karakter', self::MAX_IZIN)];
        }
        if (array_key_exists('no_hp', $data)) {
            $rules['no_hp'] = [new PhoneFormat('No HP harus 10-15 digit angka')];
        }

        (new Validator())->validate($data, $rules);

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
}
