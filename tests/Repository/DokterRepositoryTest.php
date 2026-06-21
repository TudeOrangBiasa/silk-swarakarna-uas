<?php

declare(strict_types=1);

namespace Tests\Repository;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Repository\DokterRepository;

final class DokterRepositoryTest extends TestCase
{
    private Database $db;
    private DokterRepository $repo;
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->db   = Database::getInstance();
        $this->repo = new DokterRepository();
        $this->createdIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_dokter = ?', [$id]);
            $this->db->execute('DELETE FROM dokter WHERE id_dokter = ?', [$id]);
        }
    }

    public function testInsertAndFindByIdRoundTrip(): void
    {
        $this->repo->insert([
            'nama_dokter'     => 'dr. Test Repo',
            'spesialisasi'    => 'THT',
            'no_izin_praktik' => 'SIP-99999',
            'no_hp'           => '081234567890',
        ]);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $row = $this->repo->findById($id);
        $this->assertSame($id, (int) $row['id_dokter']);
        $this->assertSame('dr. Test Repo', $row['nama_dokter']);
        $this->assertSame('THT', $row['spesialisasi']);
    }

    public function testFindByIdNonExistentReturnsEmpty(): void
    {
        $this->assertEmpty($this->repo->findById(99999));
    }

    public function testFindAllReturnsMultiple(): void
    {
        $this->repo->insert(['nama_dokter' => 'A', 'spesialisasi' => 'THT', 'no_izin_praktik' => 'SIP-A']);
        $id1 = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id1;
        $this->repo->insert(['nama_dokter' => 'B', 'spesialisasi' => 'THT', 'no_izin_praktik' => 'SIP-B']);
        $id2 = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id2;

        $rows = $this->repo->findAll();
        $this->assertGreaterThanOrEqual(2, count($rows));
    }

    public function testUpdateAffectsOneRow(): void
    {
        $this->repo->insert(['nama_dokter' => 'A', 'spesialisasi' => 'THT', 'no_izin_praktik' => 'SIP-A']);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $affected = $this->repo->update($id, ['nama_dokter' => 'Updated']);
        $this->assertSame(1, $affected);
        $this->assertSame('Updated', $this->repo->findById($id)['nama_dokter']);
    }

    public function testDeleteSetsIsDeleted(): void
    {
        $this->repo->insert(['nama_dokter' => 'A', 'spesialisasi' => 'THT', 'no_izin_praktik' => 'SIP-A']);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $this->repo->delete($id);
        $this->assertEmpty($this->repo->findById($id));
        $row = $this->db->query('SELECT is_deleted FROM dokter WHERE id_dokter = ?', [$id]);
        $this->assertSame(1, (int) $row[0]['is_deleted']);
    }

    public function testRestoreAfterDelete(): void
    {
        $this->repo->insert(['nama_dokter' => 'B', 'spesialisasi' => 'THT', 'no_izin_praktik' => 'SIP-B']);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $this->repo->delete($id);
        $this->assertEmpty($this->repo->findById($id));

        $this->repo->restore($id);
        $this->assertNotEmpty($this->repo->findById($id));
        $row = $this->db->query('SELECT is_deleted FROM dokter WHERE id_dokter = ?', [$id]);
        $this->assertSame(0, (int) $row[0]['is_deleted']);
    }

    public function testFindAllExcludesDeleted(): void
    {
        $this->repo->insert(['nama_dokter' => 'C', 'spesialisasi' => 'THT', 'no_izin_praktik' => 'SIP-C']);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $this->repo->delete($id);
        $ids = array_column($this->repo->findAll(), 'id_dokter');
        $this->assertNotContains($id, $ids);
    }

    public function testFindAllIncludingDeletedIncludesDeleted(): void
    {
        $this->repo->insert(['nama_dokter' => 'D', 'spesialisasi' => 'THT', 'no_izin_praktik' => 'SIP-D']);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $this->repo->delete($id);
        $ids = array_column($this->repo->findAllIncludingDeleted(), 'id_dokter');
        $this->assertContains($id, $ids);
    }

    public function testCountExcludesDeleted(): void
    {
        $this->repo->insert(['nama_dokter' => 'E', 'spesialisasi' => 'THT', 'no_izin_praktik' => 'SIP-E']);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $before = $this->repo->count();
        $this->repo->delete($id);
        $after = $this->repo->count();
        $this->assertSame($before - 1, $after);
    }

    public function testCountReturnsInt(): void
    {
        $this->assertIsInt($this->repo->count());
    }
}
