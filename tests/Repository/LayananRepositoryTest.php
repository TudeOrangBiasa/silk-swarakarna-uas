<?php

declare(strict_types=1);

namespace Tests\Repository;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Repository\LayananRepository;

final class LayananRepositoryTest extends TestCase
{
    private Database $db;
    private LayananRepository $repo;
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->db   = Database::getInstance();
        $this->repo = new LayananRepository();
        $this->createdIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_layanan = ?', [$id]);
            $this->db->execute('DELETE FROM layanan WHERE id_layanan = ?', [$id]);
        }
    }

    public function testInsertAndFindByIdRoundTrip(): void
    {
        $this->repo->insert(['nama_layanan' => 'Test Repo', 'biaya' => 100000]);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $row = $this->repo->findById($id);
        $this->assertSame('Test Repo', $row['nama_layanan']);
        $this->assertSame(100000, (int) $row['biaya']);
    }

    public function testInsertCastsBiayaToInt(): void
    {
        $this->repo->insert(['nama_layanan' => 'Cast Test', 'biaya' => '250000']);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $row = $this->repo->findById($id);
        $this->assertSame(250000, (int) $row['biaya']);
    }

    public function testFindByIdNonExistentReturnsEmpty(): void
    {
        $this->assertEmpty($this->repo->findById(99999));
    }

    public function testFindAllReturnsMultiple(): void
    {
        $this->repo->insert(['nama_layanan' => 'A', 'biaya' => 100000]);
        $id1 = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id1;
        $this->repo->insert(['nama_layanan' => 'B', 'biaya' => 200000]);
        $id2 = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id2;

        $rows = $this->repo->findAll();
        $this->assertGreaterThanOrEqual(2, count($rows));
    }

    public function testUpdateAffectsOneRow(): void
    {
        $this->repo->insert(['nama_layanan' => 'A', 'biaya' => 100000]);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $affected = $this->repo->update($id, ['biaya' => 999999]);
        $this->assertSame(1, $affected);
        $this->assertSame(999999, (int) $this->repo->findById($id)['biaya']);
    }

    public function testDeleteRemovesRow(): void
    {
        $this->repo->insert(['nama_layanan' => 'A', 'biaya' => 100000]);
        $id = (int) $this->db->lastInsertId();
        $this->createdIds[] = $id;

        $this->repo->delete($id);
        $this->assertEmpty($this->repo->findById($id));
    }

    public function testCountReturnsInt(): void
    {
        $this->assertIsInt($this->repo->count());
    }
}
