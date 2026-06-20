<?php

declare(strict_types=1);

namespace Tests\Repository;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Entity\Dokter;
use Silk\Entity\Layanan;
use Silk\Entity\Pasien;
use Silk\Repository\PemeriksaanRepository;

final class PemeriksaanRepositoryTest extends TestCase
{
    private Database $db;
    private PemeriksaanRepository $repo;

    private array $createdPemeriksaanIds = [];
    private array $createdPasienIds = [];
    private array $createdDokterIds = [];
    private array $createdLayananIds = [];

    protected function setUp(): void
    {
        $this->db   = Database::getInstance();
        $this->repo = new PemeriksaanRepository();
        $this->createdPemeriksaanIds = [];
        $this->createdPasienIds = [];
        $this->createdDokterIds = [];
        $this->createdLayananIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdPemeriksaanIds as $id) {
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

    public function testInsertAndFindRawRoundTrip(): void
    {
        $id = 'TRX-2026' . str_pad((string) random_int(900, 999), 3, '0', STR_PAD_LEFT);
        $this->createdPemeriksaanIds[] = $id;
        $this->repo->insert([
            'id_periksa'        => $id,
            'id_pasien'         => $this->createPasien(),
            'id_dokter'         => $this->createDokter(),
            'id_layanan'        => $this->createLayanan(),
            'tanggal_periksa'   => '2026-06-19',
            'keluhan'           => 'Test',
        ]);

        $row = $this->repo->findRaw($id);
        $this->assertSame($id, $row['id_periksa']);
        $this->assertSame('Menunggu', $row['status_pemeriksaan']);
    }

    public function testInsertSetsStatusMenunggu(): void
    {
        $id = 'TRX-2026' . str_pad((string) random_int(900, 999), 3, '0', STR_PAD_LEFT);
        $this->createdPemeriksaanIds[] = $id;
        $this->repo->insert([
            'id_periksa'        => $id,
            'id_pasien'         => $this->createPasien(),
            'id_dokter'         => $this->createDokter(),
            'id_layanan'        => $this->createLayanan(),
            'tanggal_periksa'   => '2026-06-19',
            'keluhan'           => 'Test',
        ]);

        $row = $this->repo->findRaw($id);
        $this->assertSame('Menunggu', $row['status_pemeriksaan']);
    }

    public function testUpdateStatusReturnsAffected(): void
    {
        $id = $this->createPemeriksaan();
        $affected = $this->repo->updateStatus($id, 'Sedang Diperiksa');
        $this->assertSame(1, $affected);
    }

    public function testDeleteIfNotSelesaiReturnsOne(): void
    {
        $id = $this->createPemeriksaan(); // status Menunggu
        $affected = $this->repo->deleteIfNotSelesai($id);
        $this->assertSame(1, $affected);
    }

    public function testDeleteIfSelesaiReturnsZero(): void
    {
        // Seed TRX-2026001 is Selesai.
        $affected = $this->repo->deleteIfNotSelesai('TRX-2026001');
        $this->assertSame(0, $affected);
    }

    public function testCountReturnsInt(): void
    {
        $this->assertIsInt($this->repo->count());
    }

    public function testCountByDateReturnsInt(): void
    {
        $count = $this->repo->countByDate('2026-06-19');
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    private function createPemeriksaan(): string
    {
        $id = 'TRX-2026' . str_pad((string) random_int(900, 999), 3, '0', STR_PAD_LEFT);
        $this->repo->insert([
            'id_periksa'        => $id,
            'id_pasien'         => $this->createPasien(),
            'id_dokter'         => $this->createDokter(),
            'id_layanan'        => $this->createLayanan(),
            'tanggal_periksa'   => '2026-06-19',
            'keluhan'           => 'Test',
        ]);
        $this->createdPemeriksaanIds[] = $id;
        return $id;
    }

    private function createPasien(): string
    {
        $pasien = new Pasien();
        $id = $pasien->create([
            'nama_pasien'   => 'Test ' . bin2hex(random_bytes(4)),
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
            'nama_layanan' => 'Test ' . bin2hex(random_bytes(4)),
            'biaya'        => 100000,
        ]);
        $this->createdLayananIds[] = $id;
        return $id;
    }
}
