<?php

declare(strict_types=1);

namespace Silk\Entity;

use PDOException;
use Silk\Database;
use Silk\Exception\ValidationException;
use Silk\Query\PasienQuery;
use Silk\Repository\PasienRepository;
use Silk\Validation\Rule\DateNotFuture;
use Silk\Validation\Rule\Enum;
use Silk\Validation\Rule\MaxLength;
use Silk\Validation\Rule\PhoneFormat;
use Silk\Validation\Rule\Required;
use Silk\Validation\Validator;

/**
 * Pasien entity (thin facade over PasienRepository).
 *
 * Business rules live here: required-field validation, error translation
 * (FK violation -> false). SQL access delegated to PasienRepository.
 */
final class Pasien
{
    private const MAX_NAMA      = 100;
    private const MAX_ALAMAT    = 255;
    private const MAX_PEKERJAAN = 100;
    private const JK_OPTIONS    = ['L', 'P'];
    private const GD_OPTIONS    = ['A', 'B', 'AB', 'O'];

    private PasienRepository $repo;
    private PasienQuery $query;
    private Database $db;

    public function __construct()
    {
        $this->repo  = new PasienRepository();
        $this->query = new PasienQuery();
        $this->db    = Database::getInstance();
    }

    public function generateKodeOtomatis(): string
    {
        return $this->query->generateKodeOtomatis();
    }

    /**
     * @param array{nama_pasien: string, tanggal_lahir: string, jenis_kelamin: string, pekerjaan?: string, golongan_darah?: string, riwayat_penyakit?: string, alergi?: string, no_hp: string, alamat: string} $data
     */
    public function create(array $data): string
    {
        (new Validator())->validate($data, [
            'nama_pasien'      => [new Required('Nama pasien wajib diisi'),     new MaxLength('Nama pasien maksimal 100 karakter', self::MAX_NAMA)],
            'tanggal_lahir'    => [new Required('Tanggal lahir wajib diisi'),   new DateNotFuture('Tanggal lahir tidak boleh di masa depan')],
            'jenis_kelamin'    => [new Required('Jenis kelamin wajib diisi'),   new Enum(self::JK_OPTIONS, 'Jenis kelamin harus Laki-laki (L) atau Perempuan (P)')],
            'pekerjaan'        => [new MaxLength('Pekerjaan maksimal 100 karakter', self::MAX_PEKERJAAN)],
            'golongan_darah'   => [new Enum(self::GD_OPTIONS, 'Golongan darah harus A, B, AB, atau O')],
            'no_hp'            => [new Required('No HP wajib diisi'),           new PhoneFormat('No HP harus 10-15 digit angka')],
            'alamat'           => [new Required('Alamat wajib diisi'),          new MaxLength('Alamat maksimal 255 karakter', self::MAX_ALAMAT)],
        ]);

        $maxAttempts = 3;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $this->db->beginTransaction();
            try {
                $id = $this->query->generateKodeOtomatis();
                $this->repo->insert($id, $data);
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

    public function read(?string $id = null): array
    {
        return $id !== null ? $this->repo->findById($id) : $this->repo->findAll();
    }

    public function update(string $id, array $data): int
    {
        $rules = [];

        if (array_key_exists('nama_pasien', $data)) {
            $rules['nama_pasien'] = [new Required('Nama pasien wajib diisi'), new MaxLength('Nama pasien maksimal 100 karakter', self::MAX_NAMA)];
        }
        if (array_key_exists('tanggal_lahir', $data)) {
            $rules['tanggal_lahir'] = [new Required('Tanggal lahir wajib diisi'), new DateNotFuture('Tanggal lahir tidak boleh di masa depan')];
        }
        if (array_key_exists('jenis_kelamin', $data)) {
            $rules['jenis_kelamin'] = [new Required('Jenis kelamin wajib diisi'), new Enum(self::JK_OPTIONS, 'Jenis kelamin harus Laki-laki (L) atau Perempuan (P)')];
        }
        if (array_key_exists('pekerjaan', $data)) {
            $rules['pekerjaan'] = [new MaxLength('Pekerjaan maksimal 100 karakter', self::MAX_PEKERJAAN)];
        }
        if (array_key_exists('golongan_darah', $data)) {
            $rules['golongan_darah'] = [new Enum(self::GD_OPTIONS, 'Golongan darah harus A, B, AB, atau O')];
        }
        if (array_key_exists('no_hp', $data)) {
            $rules['no_hp'] = [new Required('No HP wajib diisi'), new PhoneFormat('No HP harus 10-15 digit angka')];
        }
        if (array_key_exists('alamat', $data)) {
            $rules['alamat'] = [new Required('Alamat wajib diisi'), new MaxLength('Alamat maksimal 255 karakter', self::MAX_ALAMAT)];
        }

        (new Validator())->validate($data, $rules);

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
}
