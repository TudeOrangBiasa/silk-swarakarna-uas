<?php

declare(strict_types=1);

namespace Tests\Entity;

use Silk\Database;
use Silk\Entity\Dokter;
use Silk\Entity\Pasien;
use Silk\Entity\Pemeriksaan;
use Silk\Exception\ValidationException;
use Tests\EntityTestCase;

/**
 * Dokter entity tests.
 *
 * Isolation: explicit cleanup per test via $createdIds + $createdPasienIds
 * tracking. Transaksi rollback NOT used (entity manages its own transactions).
 *
 * Seed data (database/silk_swarakarna.sql):
 *   1, dr. Sari Wijaya, Sp.THT, SIP-12345, THT, 081234567891
 *   2, dr. Budi Santoso, Sp.THT-KL, SIP-12346, THT-KL, 081234567892
 */
final class DokterTest extends EntityTestCase
{
    private Database $db;
    private Dokter $dokter;

    /** @var list<int> Dokter IDs created during test. */
    private array $createdIds = [];

    /** @var list<string> Pasien IDs created as FK dependencies. */
    private array $createdPasienIds = [];

    protected function setUp(): void
    {
        $this->db     = Database::getInstance();
        $this->dokter = new Dokter();
        $this->createdIds = [];
        $this->createdPasienIds = [];
    }

    protected function tearDown(): void
    {
        // Clean up in FK order: child rows first.
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_dokter = ?', [$id]);
            $this->db->execute('DELETE FROM dokter WHERE id_dokter = ?', [$id]);
        }
        foreach ($this->createdPasienIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_pasien = ?', [$id]);
            $this->db->execute('DELETE FROM pasien WHERE id_pasien = ?', [$id]);
        }
    }

    public function testCreateValidReturnsId(): void
    {
        $id = $this->dokter->create($this->validData());
        $this->createdIds[] = $id;
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function testCreatePersistsData(): void
    {
        $data = $this->validData();
        $id   = $this->dokter->create($data);
        $this->createdIds[] = $id;

        $saved = $this->dokter->read($id);
        $this->assertSame($data['nama_dokter'], $saved['nama_dokter']);
        $this->assertSame($data['no_izin_praktik'], $saved['no_izin_praktik']);
        $this->assertSame($data['spesialisasi'], $saved['spesialisasi']);
        $this->assertSame($data['no_hp'], $saved['no_hp']);
    }

    public function testCreateDefaultSpesialisasiIsTHT(): void
    {
        $data = $this->validData();
        unset($data['spesialisasi']);

        $id = $this->dokter->create($data);
        $this->createdIds[] = $id;

        $saved = $this->dokter->read($id);
        $this->assertSame('THT', $saved['spesialisasi']);
    }

    public function testCreateMissingNamaDokterThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['nama_dokter'] = '';
        $this->expectValidationException(fn() => $this->dokter->create($data), ['nama_dokter']);
    }

    public function testCreateMissingNoIzinThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['no_izin_praktik'] = '';
        $this->expectValidationException(fn() => $this->dokter->create($data), ['no_izin_praktik']);
    }

    public function testCreateInvalidNoHpThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['no_hp'] = 'abc';
        $this->expectValidationException(fn() => $this->dokter->create($data), ['no_hp']);
    }

    public function testReadExistingReturnsData(): void
    {
        $data = $this->dokter->read(1);
        $this->assertSame('dr. Sari Wijaya, Sp.THT', $data['nama_dokter']);
    }

    public function testReadNonExistentReturnsEmptyArray(): void
    {
        $this->assertEmpty($this->dokter->read(99999));
    }

    public function testUpdateValidAffectsOne(): void
    {
        $id = $this->dokter->create($this->validData());
        $this->createdIds[] = $id;

        $affected = $this->dokter->update($id, ['nama_dokter' => 'Updated']);
        $this->assertSame(1, $affected);
        $this->assertSame('Updated', $this->dokter->read($id)['nama_dokter']);
    }

    public function testUpdateNonExistentAffectsZero(): void
    {
        $this->assertSame(0, $this->dokter->update(99999, ['nama_dokter' => 'X']));
    }

    public function testDeleteSuccess(): void
    {
        $id = $this->dokter->create($this->validData());
        $this->createdIds[] = $id;
        $this->assertTrue($this->dokter->delete($id));
        $this->assertEmpty($this->dokter->read($id));
    }

    public function testDeleteFkProtectedReturnsFalse(): void
    {
        // Dokter 1 referenced by seed pemeriksaan TRX-2026001 (FK ON DELETE RESTRICT).
        $this->assertFalse($this->dokter->delete(1));
    }

    public function testSearchFindsMatch(): void
    {
        $id   = $this->dokter->create($this->validData());
        $this->createdIds[] = $id;

        $results = $this->dokter->search('Test Dokter');
        $this->assertNotEmpty($results);
    }

    public function testSearchNoMatchReturnsEmpty(): void
    {
        $this->assertEmpty($this->dokter->search('NONEXISTENT_TERM_XYZ123'));
    }

    public function testCountReturnsInt(): void
    {
        $this->assertIsInt($this->dokter->count());
    }

    public function testReadForOptionsReturnsArray(): void
    {
        $this->assertIsArray($this->dokter->readForOptions());
    }

    /**
     * @return array{nama_dokter: string, no_izin_praktik: string, spesialisasi: string, no_hp: string}
     */
    private function validData(): array
    {
        $suffix = bin2hex(random_bytes(4));
        return [
            'nama_dokter'      => "dr. Test Dokter {$suffix}",
            'no_izin_praktik'  => "SIP-TEST-{$suffix}",
            'spesialisasi'     => 'THT',
            'no_hp'            => '081234567890',
        ];
    }
}
