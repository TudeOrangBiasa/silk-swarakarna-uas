<?php

declare(strict_types=1);

namespace Tests\Entity;

use Silk\Database;
use Silk\Entity\Dokter;
use Silk\Entity\Layanan;
use Silk\Entity\Pasien;
use Silk\Entity\Pemeriksaan;
use Silk\Exception\ValidationException;
use Tests\EntityTestCase;

/**
 * Pemeriksaan entity tests.
 *
 * Covers state machine, race-safe updateStatus, date validation, FK
 * constraints. Uses createdIds for Pasien/Dokter/Layanan test data and
 * separate createdIds for Pemeriksaan.
 *
 * Seed data (database/silk_swarakarna.sql):
 *   TRX-2026001: RM-001, dokter 1, layanan 1, 2026-06-18, Selesai
 *   TRX-2026002: RM-002, dokter 2, layanan 3, 2026-06-19, Sedang Diperiksa
 */
final class PemeriksaanTest extends EntityTestCase
{
    private Database $db;
    private Pemeriksaan $pemeriksaan;

    /** @var list<string> Pemeriksaan IDs created during test. */
    private array $createdIds = [];

    /** @var list<string> Pasien IDs as FK dependencies. */
    private array $createdPasienIds = [];

    /** @var list<int> Dokter IDs as FK dependencies. */
    private array $createdDokterIds = [];

    /** @var list<int> Layanan IDs as FK dependencies. */
    private array $createdLayananIds = [];

    protected function setUp(): void
    {
        $this->db          = Database::getInstance();
        $this->pemeriksaan = new Pemeriksaan();
        $this->createdIds = [];
        $this->createdPasienIds = [];
        $this->createdDokterIds = [];
        $this->createdLayananIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_periksa = ?', [$id]);
        }
        foreach ($this->createdPasienIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_pasien = ?', [$id]);
            $this->db->execute('DELETE FROM pasien WHERE id_pasien = ?', [$id]);
        }
        foreach ($this->createdDokterIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_dokter = ?', [$id]);
            $this->db->execute('DELETE FROM dokter WHERE id_dokter = ?', [$id]);
        }
        foreach ($this->createdLayananIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_layanan = ?', [$id]);
            $this->db->execute('DELETE FROM layanan WHERE id_layanan = ?', [$id]);
        }
    }

    public function testGenerateKodeOtomatisReturnsTrxFormat(): void
    {
        $kode = $this->pemeriksaan->generateKodeOtomatis();
        $this->assertMatchesRegularExpression('/^TRX-\d{4}\d{3}$/', $kode);
    }

    public function testCreateValidReturnsId(): void
    {
        $id = $this->pemeriksaan->create($this->validData());
        $this->createdIds[] = $id;
        $this->assertIsString($id);
        $this->assertStringStartsWith('TRX-', $id);
    }

    public function testCreatePersistsData(): void
    {
        $pasienId  = $this->createPasien();
        $dokterId  = $this->createDokter();
        $layananId = $this->createLayanan();

        $data = $this->validData($pasienId, $dokterId, $layananId);
        $id   = $this->pemeriksaan->create($data);
        $this->createdIds[] = $id;

        $saved = $this->pemeriksaan->read($id);
        $this->assertSame($id, $saved['id_periksa']);
        $this->assertSame('Menunggu', $saved['status_pemeriksaan']);
    }

    public function testCreateMissingIdPasienThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['id_pasien'] = '';
        $this->expectValidationException(fn() => $this->pemeriksaan->create($data), ['id_pasien']);
    }

    public function testCreateFutureTanggalPeriksaThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['tanggal_periksa'] = '2030-01-01';
        $this->expectValidationException(fn() => $this->pemeriksaan->create($data), ['tanggal_periksa']);
    }

    public function testUpdateStatusValidTransition(): void
    {
        $id = $this->pemeriksaan->create($this->validData());
        $this->createdIds[] = $id;
        $affected = $this->pemeriksaan->updateStatus($id, 'Sedang Diperiksa');
        $this->assertSame(1, $affected);
    }

    public function testUpdateStatusInvalidTransitionThrows(): void
    {
        // Seed TRX-2026001 has status Selesai; Menunggu is invalid.
        $this->expectValidationException(
            fn() => $this->pemeriksaan->updateStatus('TRX-2026001', 'Menunggu'),
            ['status_pemeriksaan']
        );
    }

    public function testUpdateStatusNonExistentThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->pemeriksaan->updateStatus('TRX-999999', 'Menunggu');
    }

    public function testDeleteNonSelesaiReturnsTrue(): void
    {
        // Create one with status Menunggu, then delete.
        $id = $this->pemeriksaan->create($this->validData());
        $this->createdIds[] = $id;
        $this->assertTrue($this->pemeriksaan->delete($id));
    }

    public function testDeleteSelesaiReturnsFalse(): void
    {
        // Seed TRX-2026001 is Selesai (immutable).
        $this->assertFalse($this->pemeriksaan->delete('TRX-2026001'));
    }

    public function testGetAllowedTransitionsForMenunggu(): void
    {
        $this->assertSame(
            ['Sedang Diperiksa', 'Selesai'],
            $this->pemeriksaan->getAllowedTransitions('Menunggu')
        );
    }

    public function testGetAllowedTransitionsForSelesaiIsEmpty(): void
    {
        $this->assertSame([], $this->pemeriksaan->getAllowedTransitions('Selesai'));
    }

    public function testCountReturnsInt(): void
    {
        $this->assertIsInt($this->pemeriksaan->count());
    }

    public function testCountByDateReturnsInt(): void
    {
        $today = date('Y-m-d');
        $id    = $this->pemeriksaan->create($this->validData());
        $this->createdIds[] = $id;

        $count = $this->pemeriksaan->countByDate($today);
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testReadLatestReturnsRows(): void
    {
        $latest = $this->pemeriksaan->readLatest(5);
        $this->assertIsArray($latest);
    }

    public function testReadWithJoinReturnsJoinData(): void
    {
        $rows = $this->pemeriksaan->readWithJoin();
        $this->assertIsArray($rows);
        if ($rows !== []) {
            $this->assertArrayHasKey('nama_pasien', $rows[0]);
            $this->assertArrayHasKey('nama_dokter', $rows[0]);
        }
    }

    private function createPasien(): string
    {
        $pasien = new Pasien();
        $id = $pasien->create([
            'nama_pasien'   => 'Test Pasien ' . bin2hex(random_bytes(4)),
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'no_hp'         => '081234567890',
            'alamat'        => 'Jl Test',
        ]);
        $this->createdPasienIds[] = $id;
        return $id;
    }

    private function createDokter(): int
    {
        $dokter = new Dokter();
        $id = $dokter->create([
            'nama_dokter'     => 'dr. Test ' . bin2hex(random_bytes(4)),
            'no_izin_praktik' => 'SIP-' . bin2hex(random_bytes(4)),
            'spesialisasi'    => 'THT',
            'no_hp'           => '081234567891',
        ]);
        $this->createdDokterIds[] = $id;
        return $id;
    }

    private function createLayanan(): int
    {
        $layanan = new Layanan();
        $id = $layanan->create([
            'nama_layanan' => 'Test Layanan ' . bin2hex(random_bytes(4)),
            'biaya'        => 100000,
        ]);
        $this->createdLayananIds[] = $id;
        return $id;
    }

    /**
     * @return array{id_pasien: string, id_dokter: int, id_layanan: int, tanggal_periksa: string, keluhan: string}
     */
    private function validData(?string $pasienId = null, ?int $dokterId = null, ?int $layananId = null): array
    {
        return [
            'id_pasien'       => $pasienId ?? $this->createPasien(),
            'id_dokter'       => $dokterId ?? $this->createDokter(),
            'id_layanan'      => $layananId ?? $this->createLayanan(),
            'tanggal_periksa' => date('Y-m-d'),
            'keluhan'         => 'Test keluhan ' . bin2hex(random_bytes(2)),
        ];
    }
}
