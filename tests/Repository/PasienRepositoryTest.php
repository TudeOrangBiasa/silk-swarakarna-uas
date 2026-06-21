<?php

declare(strict_types=1);

namespace Tests\Repository;

use PHPUnit\Framework\Attributes\Depends;
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
        $this->repo->insert($id, $this->data('Repo'));

        $row = $this->repo->findById($id);
        $this->assertSame($id, $row['id_pasien']);
        $this->assertSame('Test Repo Repo', $row['nama_pasien']);
        $this->assertSame('L', $row['jenis_kelamin']);
        $this->assertSame('PNS', $row['pekerjaan']);
        $this->assertSame('O', $row['golongan_darah']);
        $this->assertSame('Tidak ada', $row['riwayat_penyakit']);
        $this->assertSame('Debu', $row['alergi']);
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

    public function testUpdateJenisKelamin(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('JK'));

        $this->repo->update($id, ['jenis_kelamin' => 'P']);
        $this->assertSame('P', $this->repo->findById($id)['jenis_kelamin']);
    }

    public function testUpdatePekerjaan(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('Pekerjaan'));

        $this->repo->update($id, ['pekerjaan' => 'Wiraswasta']);
        $this->assertSame('Wiraswasta', $this->repo->findById($id)['pekerjaan']);
    }

    public function testUpdateGolonganDarah(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('GD'));

        $this->repo->update($id, ['golongan_darah' => 'AB']);
        $this->assertSame('AB', $this->repo->findById($id)['golongan_darah']);
    }

    public function testUpdateRiwayatPenyakit(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('Riwayat'));

        $this->repo->update($id, ['riwayat_penyakit' => 'Diabetes']);
        $this->assertSame('Diabetes', $this->repo->findById($id)['riwayat_penyakit']);
    }

    public function testUpdateAlergi(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('Alergi'));

        $this->repo->update($id, ['alergi' => 'Penisilin']);
        $this->assertSame('Penisilin', $this->repo->findById($id)['alergi']);
    }

    public function testUpdateNonExistentAffectsZero(): void
    {
        $this->assertSame(0, $this->repo->update('RM-99999', ['nama_pasien' => 'X']));
    }

    public function testDeleteSetsIsDeleted(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('ToDelete'));

        $this->repo->delete($id);
        // Soft delete: is_deleted should be 1, and findById filters it.
        $this->assertEmpty($this->repo->findById($id));
        $row = $this->db->query('SELECT is_deleted FROM pasien WHERE id_pasien = ?', [$id]);
        $this->assertSame(1, (int) $row[0]['is_deleted']);
    }

    public function testRestoreAfterDelete(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('ToRestore'));

        $this->repo->delete($id);
        $this->assertEmpty($this->repo->findById($id));

        $this->repo->restore($id);
        $this->assertNotEmpty($this->repo->findById($id));
        $row = $this->db->query('SELECT is_deleted FROM pasien WHERE id_pasien = ?', [$id]);
        $this->assertSame(0, (int) $row[0]['is_deleted']);
    }

    public function testFindAllExcludesDeleted(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('ExcludeMe'));

        $this->repo->delete($id);
        $ids = array_column($this->repo->findAll(), 'id_pasien');
        $this->assertNotContains($id, $ids);
    }

    public function testFindAllIncludingDeletedIncludesDeleted(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('IncludeMe'));

        $this->repo->delete($id);
        $ids = array_column($this->repo->findAllIncludingDeleted(), 'id_pasien');
        $this->assertContains($id, $ids);
    }

    public function testCountExcludesDeleted(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('CountMe'));

        $before = $this->repo->count();
        $this->repo->delete($id);
        $after = $this->repo->count();
        $this->assertSame($before - 1, $after);
    }

    public function testCountAllIncludingDeleted(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('CountAllMe'));

        $before = $this->repo->countAllIncludingDeleted();
        $this->repo->delete($id);
        $after = $this->repo->countAllIncludingDeleted();
        $this->assertSame($before, $after); // count including deleted should not change
    }

    public function testCountReturnsInt(): void
    {
        $this->assertIsInt($this->repo->count());
    }

    // ---------------------------------------------------------------
    // foto field
    // ---------------------------------------------------------------

    public function testInsertWithFoto(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $data = $this->data('WithFoto');
        $data['foto'] = 'assets/uploads/pasien/abc123.jpg';

        $this->repo->insert($id, $data);
        $row = $this->repo->findById($id);

        $this->assertSame('assets/uploads/pasien/abc123.jpg', $row['foto']);
    }

    public function testInsertWithoutFotoStoresNull(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('NoFoto'));

        $row = $this->repo->findById($id);
        $this->assertNull($row['foto']);
    }

    public function testUpdateWithFoto(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $this->repo->insert($id, $this->data('Orig'));

        $affected = $this->repo->update($id, ['foto' => 'assets/uploads/pasien/update.jpg']);
        $this->assertSame(1, $affected);

        $row = $this->repo->findById($id);
        $this->assertSame('assets/uploads/pasien/update.jpg', $row['foto']);
    }

    public function testUpdateWithoutFotoPreservesExisting(): void
    {
        $id = $this->makeId();
        $this->createdIds[] = $id;
        $data = $this->data('Preserve');
        $data['foto'] = 'assets/uploads/pasien/existing.jpg';
        $this->repo->insert($id, $data);

        // Update another field — foto should remain unchanged
        $this->repo->update($id, ['alamat' => 'Updated Alamat']);
        $row = $this->repo->findById($id);

        $this->assertSame('assets/uploads/pasien/existing.jpg', $row['foto']);
        $this->assertSame('Updated Alamat', $row['alamat']);
    }

    private function makeId(): string
    {
        return 'RM-' . str_pad((string) random_int(900, 999), 3, '0', STR_PAD_LEFT);
    }

    private function data(string $nama): array
    {
        return [
            'nama_pasien'      => "Test Repo {$nama}",
            'tanggal_lahir'    => '1990-01-01',
            'jenis_kelamin'    => 'L',
            'pekerjaan'        => 'PNS',
            'golongan_darah'   => 'O',
            'riwayat_penyakit' => 'Tidak ada',
            'alergi'           => 'Debu',
            'no_hp'            => '081234567890',
            'alamat'           => 'Jl Test Repo',
        ];
    }
}
