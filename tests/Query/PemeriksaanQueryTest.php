<?php

declare(strict_types=1);

namespace Tests\Query;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Entity\Dokter;
use Silk\Entity\Layanan;
use Silk\Entity\Pasien;
use Silk\Entity\Pemeriksaan;
use Silk\Query\PemeriksaanQuery;

final class PemeriksaanQueryTest extends TestCase
{
    private Database $db;
    private PemeriksaanQuery $query;

    /** @var list<string> */
    private array $createdPemeriksaanIds = [];

    /** @var list<string> */
    private array $createdPasienIds = [];

    /** @var list<int> */
    private array $createdDokterIds = [];

    /** @var list<int> */
    private array $createdLayananIds = [];

    protected function setUp(): void
    {
        $this->db    = Database::getInstance();
        $this->query = new PemeriksaanQuery();
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

    public function testGenerateKodeOtomatisReturnsTrxFormat(): void
    {
        $kode = $this->query->generateKodeOtomatis();
        $this->assertMatchesRegularExpression('/^TRX-\d{4}\d{3}$/', $kode);
    }

    public function testGenerateKodeOtomatisUsesCurrentYear(): void
    {
        $kode = $this->query->generateKodeOtomatis();
        $this->assertStringStartsWith('TRX-' . date('Y'), $kode);
    }

    public function testGenerateKodeOtomatisIncrementsAfterInsert(): void
    {
        $before = $this->query->generateKodeOtomatis();
        $id = $this->createPemeriksaan();
        $this->createdPemeriksaanIds[] = $id;
        $after = $this->query->generateKodeOtomatis();
        $this->assertNotSame($before, $after);
    }

    public function testFindAllJoinedReturnsJoinData(): void
    {
        $id = $this->createPemeriksaan();
        $this->createdPemeriksaanIds[] = $id;

        $rows = $this->query->findAllJoined(null, null, null, null, 50, 0);
        $found = null;
        foreach ($rows as $r) {
            if ($r['id_periksa'] === $id) {
                $found = $r;
                break;
            }
        }
        $this->assertNotNull($found);
        $this->assertArrayHasKey('nama_pasien', $found);
        $this->assertArrayHasKey('nama_dokter', $found);
        $this->assertArrayHasKey('nama_layanan', $found);
        $this->assertArrayHasKey('biaya', $found);
    }

    public function testFindAllJoinedWithKeywordFilter(): void
    {
        $id = $this->createPemeriksaan();
        $this->createdPemeriksaanIds[] = $id;

        $pasien = new Pasien();
        $nama = $pasien->read($this->lastCreatedPasien)['nama_pasien'];

        $rows = $this->query->findAllJoined($nama, null, null, null, 50, 0);
        $this->assertNotEmpty($rows);
        $ids = array_column($rows, 'id_periksa');
        $this->assertContains($id, $ids);
    }

    public function testFindAllJoinedWithStatusFilter(): void
    {
        $id = $this->createPemeriksaan(); // status Menunggu
        $this->createdPemeriksaanIds[] = $id;

        $rows = $this->query->findAllJoined(null, 'Menunggu', null, null, 50, 0);
        $ids = array_column($rows, 'id_periksa');
        $this->assertContains($id, $ids);
    }

    public function testFindAllJoinedWithDateRangeFilter(): void
    {
        $id = $this->createPemeriksaan();
        $this->createdPemeriksaanIds[] = $id;

        $rows = $this->query->findAllJoined(null, null, date('Y-m-d'), date('Y-m-d'), 50, 0);
        $ids = array_column($rows, 'id_periksa');
        $this->assertContains($id, $ids);
    }

    public function testCountAllJoinedMatchesFindAllJoined(): void
    {
        $id1 = $this->createPemeriksaan();
        $this->createdPemeriksaanIds[] = $id1;
        $id2 = $this->createPemeriksaan();
        $this->createdPemeriksaanIds[] = $id2;
        $id3 = $this->createPemeriksaan();
        $this->createdPemeriksaanIds[] = $id3;

        $count = $this->query->countAllJoined(null);
        $rows  = $this->query->findAllJoined(null, null, null, null, 50, 0);
        $this->assertSame(count($rows), $count);
    }

    public function testGetDateRangeTotalReturnsSum(): void
    {
        $id = $this->createPemeriksaan(); // biaya = 100000
        $this->createdPemeriksaanIds[] = $id;

        $total = $this->query->getDateRangeTotal(date('Y-m-d'), date('Y-m-d'));
        $this->assertGreaterThanOrEqual(100000, $total);
    }

    public function testGetDateRangeTotalWithNoDataReturnsZero(): void
    {
        $total = $this->query->getDateRangeTotal('2020-01-01', '2020-01-01');
        $this->assertSame(0, $total);
    }

    public function testFindAllJoinedWithDoctorNameKeyword(): void
    {
        $suffix = bin2hex(random_bytes(4));

        $pasien = new Pasien();
        $pasienId = $pasien->create([
            'nama_pasien'   => 'Query DocTest Pasien ' . $suffix,
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'no_hp'         => '081234567890',
            'alamat'        => 'Jl Test',
        ]);
        $this->createdPasienIds[] = $pasienId;

        $uniqueDokterName = 'dr. Query DocTest ' . $suffix;
        $dokter = new Dokter();
        $dokterId = $dokter->create([
            'nama_dokter'     => $uniqueDokterName,
            'no_izin_praktik' => 'SIP-' . bin2hex(random_bytes(4)),
            'spesialisasi'    => 'THT',
            'no_hp'           => '081234567891',
        ]);
        $this->createdDokterIds[] = $dokterId;

        $layanan = new Layanan();
        $layananId = $layanan->create([
            'nama_layanan' => 'Query DocTest ' . $suffix,
            'biaya'        => 100000,
        ]);
        $this->createdLayananIds[] = $layananId;

        $pemeriksaan = new Pemeriksaan();
        $id = $pemeriksaan->create([
            'id_pasien'       => $pasienId,
            'id_dokter'       => $dokterId,
            'id_layanan'      => $layananId,
            'tanggal_periksa' => date('Y-m-d'),
            'keluhan'         => 'Test doctor search',
        ]);
        $this->createdPemeriksaanIds[] = $id;

        $rows = $this->query->findAllJoined($uniqueDokterName, null, null, null, 50, 0);
        $this->assertNotEmpty($rows);
        $ids = array_column($rows, 'id_periksa');
        $this->assertContains($id, $ids);

        $count = $this->query->countAllJoined($uniqueDokterName, null, null, null);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByIdJoinedReturnsFullRow(): void
    {
        $id = $this->createPemeriksaan();
        $this->createdPemeriksaanIds[] = $id;

        $row = $this->query->findByIdJoined($id);
        $this->assertSame($id, $row['id_periksa']);
        $this->assertArrayHasKey('nama_pasien', $row);
        $this->assertArrayHasKey('nama_dokter', $row);
    }

    public function testFindByIdJoinedNonExistentReturnsEmpty(): void
    {
        $this->assertEmpty($this->query->findByIdJoined('TRX-99999'));
    }

    public function testFindLatestReturnsRows(): void
    {
        $rows = $this->query->findLatest(5);
        $this->assertLessThanOrEqual(5, count($rows));
    }

    public function testFindStatusForUpdateReturnsStatus(): void
    {
        $id = $this->createPemeriksaan();
        $this->createdPemeriksaanIds[] = $id;

        $status = $this->query->findStatusForUpdate($id);
        $this->assertSame('Menunggu', $status);
    }

    public function testFindStatusForUpdateNonExistentReturnsNull(): void
    {
        $this->assertNull($this->query->findStatusForUpdate('TRX-99999'));
    }

    private string $lastCreatedPasien = '';

    private function createPemeriksaan(): string
    {
        $pasien = new Pasien();
        $pasienId =         $pasien->create([
            'nama_pasien'   => 'Query Test ' . bin2hex(random_bytes(2)),
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'no_hp'         => '081234567890',
            'alamat'        => 'Jl Test',
        ]);
        $this->createdPasienIds[] = $pasienId;
        $this->lastCreatedPasien = $pasienId;

        $dokter = new Dokter();
        $dokterId = $dokter->create([
            'nama_dokter'     => 'dr. Query ' . bin2hex(random_bytes(2)),
            'no_izin_praktik' => 'SIP-' . bin2hex(random_bytes(4)),
            'spesialisasi'    => 'THT',
            'no_hp'           => '081234567891',
        ]);
        $this->createdDokterIds[] = $dokterId;

        $layanan = new Layanan();
        $layananId = $layanan->create([
            'nama_layanan' => 'Query ' . bin2hex(random_bytes(2)),
            'biaya'        => 100000,
        ]);
        $this->createdLayananIds[] = $layananId;

        $pemeriksaan = new Pemeriksaan();
        return $pemeriksaan->create([
            'id_pasien'       => $pasienId,
            'id_dokter'       => $dokterId,
            'id_layanan'      => $layananId,
            'tanggal_periksa' => date('Y-m-d'),
            'keluhan'         => 'Test',
        ]);
    }
}
