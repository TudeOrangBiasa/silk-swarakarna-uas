<?php

declare(strict_types=1);

namespace Tests\Repository;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Repository\PasienRepository;

final class PasienRepositoryTest extends TestCase
{
    private Database $db;
    private PasienRepository $repo;

    /** @var list<string> */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->db   = Database::getInstance();
        $this->repo = new PasienRepository();
        $this->createdIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_pasien = ?', [$id]);
            $this->db->execute('DELETE FROM pasien WHERE id_pasien = ?', [$id]);
        }
    }

    public function testInsertAndFindByIdRoundTrip(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, [
            'nama_pasien'   => 'Test Repo',
            'tanggal_lahir' => '1990-01-01',
            'no_hp'         => '081234567890',
            'alamat'        => 'Jl Test Repo',
        ]);

        $row = $this->repo->findById($id);
        $this->assertSame($id, $row['id_pasien']);
        $this->assertSame('Test Repo', $row['nama_pasien']);
    }

    public function testFindByIdNonExistentReturnsEmpty(): void
    {
        $this->assertEmpty($this->repo->findById('RM-99999'));
    }

    public function testFindAllReturnsMultiple(): void
    {
        $id1 = $this->makeId();
        $id2 = $this->makeId();
        $this->createdIds = [$id1, $id2];

        $this->repo->insert($id1, $this->data('A'));
        $this->repo->insert($id2, $this->data('B'));

        $rows = $this->repo->findAll();
        $ids = array_column($rows, 'id_pasien');
        $this->assertContains($id1, $ids);
        $this->assertContains($id2, $ids);
    }

    public function testUpdateAffectsOneRow(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('Original'));

        $affected = $this->repo->update($id, ['nama_pasien' => 'Updated']);
        $this->assertSame(1, $affected);
        $this->assertSame('Updated', $this->repo->findById($id)['nama_pasien']);
    }

    public function testUpdatePartialFieldsOnly(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('Full'));

        $this->repo->update($id, ['alamat' => 'New Alamat']);
        $row = $this->repo->findById($id);
        $this->assertSame('New Alamat', $row['alamat']);
        $this->assertSame('Test Repo Full', $row['nama_pasien']); // unchanged
    }

    public function testUpdateNonExistentAffectsZero(): void
    {
        $this->assertSame(0, $this->repo->update('RM-99999', ['nama_pasien' => 'X']));
    }

    public function testDeleteRemovesRow(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('ToDelete'));

        $this->repo->delete($id);
        $this->assertEmpty($this->repo->findById($id));
    }

    public function testCountReturnsInt(): void
    {
        $this->assertIsInt($this->repo->count());
    }

    private function makeId(): string
    {
        return 'RM-' . str_pad((string) random_int(900, 999), 3, '0', STR_PAD_LEFT);
    }

    private function data(string $nama): array
    {
        return [
            'nama_pasien'   => "Test Repo {$nama}",
            'tanggal_lahir' => '1990-01-01',
            'no_hp'         => '081234567890',
            'alamat'        => 'Jl Test Repo',
        ];
    }
}
