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

        $fotoPath = $this->handleFileUpload();
        if ($fotoPath !== null) {
            $data['foto'] = $fotoPath;
        }

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

        // Handle file upload: save new → update DB → unlink old only after DB success
        $fotoPath = $this->handleFileUpload();
        $oldFoto = null;
        if ($fotoPath !== null) {
            $existing = $this->repo->findById($id);
            if ($existing !== [] && ($existing['foto'] ?? null) !== null) {
                $oldFoto = $existing['foto'];
            }
            $data['foto'] = $fotoPath;
        }

        $affected = $this->repo->update($id, $data);
        if ($affected > 0 && $oldFoto !== null) {
            $this->unlinkOldFoto($oldFoto);
        }
        return $affected;
    }

    private static function isDuplicateKeyError(PDOException $e): bool
    {
        return str_contains($e->getMessage(), 'Duplicate');
    }

    /**
     * Handle file upload from $_FILES['foto'].
     * Validates mime type (jpeg/png/webp), max 2MB, saves to upload dir.
     * Returns relative path string, or null if no file uploaded.
     *
     * @throws ValidationException on invalid mime, oversize, or move failure
     */
    private function handleFileUpload(): ?string
    {
        if (!isset($_FILES['foto'])) {
            return null;
        }

        $error = $_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        // Any other upload error from PHP
        if ($error !== UPLOAD_ERR_OK) {
            throw new ValidationException(['foto' => 'Gagal mengunggah foto. Coba lagi.']);
        }

        $file = $_FILES['foto'];

        // Size check: 2MB
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new ValidationException(['foto' => 'Ukuran foto maksimal 2MB.']);
        }

        // Mime check via getimagesize (stdlib, reads actual image header)
        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            throw new ValidationException(['foto' => 'File harus berupa gambar (JPG/PNG/WebP).']);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($info['mime'], $allowed, true)) {
            throw new ValidationException(['foto' => 'Format foto harus JPG, PNG, atau WebP.']);
        }

        // Extension from mime
        $ext = match ($info['mime']) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => throw new \LogicException('Unreachable: unhandled mime ' . $info['mime']),
        };

        // Random filename
        $hash = bin2hex(random_bytes(16));
        $filename = "{$hash}.{$ext}";
        $relativePath = "assets/uploads/pasien/{$filename}";

        // Ensure upload dir exists
        $uploadDir = __DIR__ . '/../../public/assets/uploads/pasien';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $destPath = "{$uploadDir}/{$filename}";
        if (@move_uploaded_file($file['tmp_name'], $destPath)) {
            return $relativePath;
        }

        throw new ValidationException(['foto' => 'Gagal menyimpan foto. Coba lagi.']);
    }

    /**
     * Delete old foto file from disk.
     */
    private function unlinkOldFoto(string $relativePath): void
    {
        $absPath = __DIR__ . '/../../public/' . ltrim($relativePath, '/');
        if (file_exists($absPath)) {
            @unlink($absPath);
        }
    }

    public function read(?string $id = null): array
    {
        return $id !== null ? $this->repo->findById($id) : $this->repo->findAll();
    }

    public function delete(string $id): bool
    {
        // Soft delete: set is_deleted=1. Foto tetap dipertahankan.
        $this->repo->delete($id);
        return true;
    }

    public function restore(string $id): void
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
