<?php

declare(strict_types=1);

namespace Tests\Query;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Entity\Pasien;
use Silk\Query\PasienQuery;

final class PasienQueryTest extends TestCase
{
    private Database $db;
    private PasienQuery $query;

    /** @var list<string> */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->db    = Database::getInstance();
        $this->query = new PasienQuery();
        $this->createdIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_pasien = ?', [$id]);
            $this->db->execute('DELETE FROM pasien WHERE id_pasien = ?', [$id]);
        }
    }

    public function testSearchByNameFindsMatch(): void
    {
        $id = $this->createPasien('Andi Search Test');
        $this->createdIds[] = $id;

        $results = $this->query->searchByName('Search Test');
        $this->assertNotEmpty($results);
        $this->assertContains($id, array_column($results, 'id_pasien'));
    }

    public function testSearchByNameNoMatchReturnsEmpty(): void
    {
        $this->assertEmpty($this->query->searchByName('NONEXISTENT_TERM_XYZ123'));
    }

    public function testSearchByNameReturnsMultiple(): void
    {
        $id1 = $this->createPasien('Search Multi A');
        $id2 = $this->createPasien('Search Multi B');
        $this->createdIds = [$id1, $id2];

        $results = $this->query->searchByName('Search Multi');
        $this->assertGreaterThanOrEqual(2, count($results));
    }

    public function testSearchByNameIsCaseInsensitive(): void
    {
        $id = $this->createPasien('Case Test');
        $this->createdIds[] = $id;

        $results = $this->query->searchByName('case test');
        $this->assertNotEmpty($results);
    }

    public function testFindPasienForOptionsReturnsIdAndNama(): void
    {
        $suffix = bin2hex(random_bytes(2));
        $fullName = 'Options Test ' . $suffix;
        $id = $this->createPasienRaw($fullName);
        $this->createdIds[] = $id;

        $options = $this->query->findPasienForOptions();
        $found = false;
        foreach ($options as $opt) {
            if ($opt['id_pasien'] === $id) {
                $this->assertSame($fullName, $opt['nama_pasien']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created pasien not in options');
    }

    public function testGenerateKodeOtomatisReturnsRmFormat(): void
    {
        $kode = $this->query->generateKodeOtomatis();
        $this->assertMatchesRegularExpression('/^RM-\d{3}$/', $kode);
    }

    public function testGenerateKodeOtomatisAfterInsertReturnsNextNumber(): void
    {
        $first = (int) substr($this->query->generateKodeOtomatis(), 3);
        $id = $this->createPasien('Generate Test');
        $this->createdIds[] = $id;
        $next = (int) substr($this->query->generateKodeOtomatis(), 3);
        $this->assertGreaterThan($first, $next);
    }

    private function createPasien(string $nama): string
    {
        return $this->createPasienRaw($nama . ' ' . bin2hex(random_bytes(2)));
    }

    private function createPasienRaw(string $fullName): string
    {
        $pasien = new Pasien();
        return $pasien->create([
            'nama_pasien'   => $fullName,
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'no_hp'         => '081234567890',
            'alamat'        => 'Jl Test',
        ]);
    }
}
