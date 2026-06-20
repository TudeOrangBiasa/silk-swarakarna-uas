<?php

declare(strict_types=1);

namespace Tests\Query;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Entity\Dokter;
use Silk\Query\DokterQuery;

final class DokterQueryTest extends TestCase
{
    private Database $db;
    private DokterQuery $query;

    /** @var list<int> */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->db    = Database::getInstance();
        $this->query = new DokterQuery();
        $this->createdIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_dokter = ?', [$id]);
            $this->db->execute('DELETE FROM dokter WHERE id_dokter = ?', [$id]);
        }
    }

    public function testSearchByNameFindsMatch(): void
    {
        $id = $this->createDokter('Andi Search Test');
        $this->createdIds[] = $id;

        $results = $this->query->searchByName('Search Test');
        $this->assertNotEmpty($results);
    }

    public function testSearchByNameNoMatchReturnsEmpty(): void
    {
        $this->assertEmpty($this->query->searchByName('NONEXISTENT_TERM_XYZ123'));
    }

    public function testFindDokterForOptionsReturnsIdNamaSpesialisasi(): void
    {
        $id = $this->createDokter('Options Test');
        $this->createdIds[] = $id;

        $options = $this->query->findDokterForOptions();
        $found = false;
        foreach ($options as $opt) {
            if ((int) $opt['id_dokter'] === $id) {
                $this->assertSame('THT', $opt['spesialisasi']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created dokter not in options');
    }

    private function createDokter(string $nama): int
    {
        $dokter = new Dokter();
        return $dokter->create([
            'nama_dokter'     => 'dr. ' . $nama . ' ' . bin2hex(random_bytes(2)),
            'no_izin_praktik' => 'SIP-' . bin2hex(random_bytes(4)),
            'spesialisasi'    => 'THT',
            'no_hp'           => '081234567891',
        ]);
    }
}
