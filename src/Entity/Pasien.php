<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use Silk\Exception\ValidationException;
use Silk\Query\PasienQuery;
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
    private const MAX_NAMA  = 100;
    private const MAX_ALAMAT = 255;

    private PasienRepository $repo;
    private PasienQuery $query;

    public function __construct()
    {
        $this->repo  = new PasienRepository();
        $this->query = new PasienQuery();
    }

    public function generateKodeOtomatis(): string
    {
        return $this->query->generateKodeOtomatis();
    }

    /**
     * @param array{nama_pasien: string, tanggal_lahir: string, no_hp: string, alamat: string} $data
     */
    public function create(array $data): string
    {
        $errors = [];

        $errors += $this->validateRequired($data, self::REQUIRED);
        $errors += $this->validateTanggalLahir($data['tanggal_lahir'] ?? '');
        $errors += $this->validateNoHp($data['no_hp'] ?? '');
        $errors += $this->validateMaxLength($data['nama_pasien'] ?? '', 'nama_pasien', self::MAX_NAMA);
        $errors += $this->validateMaxLength($data['alamat'] ?? '', 'alamat', self::MAX_ALAMAT);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $id = $this->query->generateKodeOtomatis();
        $this->repo->insert($id, $data);
        return $id;
    }

    public function read(?string $id = null): array
    {
        return $id !== null ? $this->repo->findById($id) : $this->repo->findAll();
    }

    public function update(string $id, array $data): int
    {
        $errors = [];

        if (array_key_exists('nama_pasien', $data)) {
            if (empty($data['nama_pasien'])) {
                $errors['nama_pasien'] = 'Nama pasien wajib diisi';
            } else {
                $errors += $this->validateMaxLength($data['nama_pasien'], 'nama_pasien', self::MAX_NAMA);
            }
        }

        if (array_key_exists('tanggal_lahir', $data)) {
            if (empty($data['tanggal_lahir'])) {
                $errors['tanggal_lahir'] = 'Tanggal lahir wajib diisi';
            } else {
                $errors += $this->validateTanggalLahir($data['tanggal_lahir']);
            }
        }

        if (array_key_exists('no_hp', $data)) {
            if (empty($data['no_hp'])) {
                $errors['no_hp'] = 'No HP wajib diisi';
            } else {
                $errors += $this->validateNoHp($data['no_hp']);
            }
        }

        if (array_key_exists('alamat', $data)) {
            if (empty($data['alamat'])) {
                $errors['alamat'] = 'Alamat wajib diisi';
            } else {
                $errors += $this->validateMaxLength($data['alamat'], 'alamat', self::MAX_ALAMAT);
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

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
        return $this->query->searchByName($keyword);
    }

    public function readForOptions(): array
    {
        return $this->query->findPasienForOptions();
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

    private function validateTanggalLahir(string $date): array
    {
        if ($date === '') {
            return [];
        }
        if ($date > date('Y-m-d')) {
            return ['tanggal_lahir' => 'Tanggal lahir tidak boleh di masa depan'];
        }
        return [];
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
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }
}
