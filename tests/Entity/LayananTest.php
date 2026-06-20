<?php

declare(strict_types=1);

namespace Tests\Entity;

use Silk\Database;
use Silk\Entity\Layanan;
use Silk\Exception\ValidationException;
use Tests\EntityTestCase;

/**
 * Layanan entity tests.
 *
 * Isolation: explicit cleanup per test via $createdIds tracking + tearDown.
 *
 * Seed data (database/silk_swarakarna.sql):
 *   1, Audiometri, 250000
 *   2, OAE, 350000
 *   3, BERA, 500000
 */
final class LayananTest extends EntityTestCase
{
    private Database $db;
    private Layanan $layanan;

    /** @var list<int> */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->db      = Database::getInstance();
        $this->layanan = new Layanan();
        $this->createdIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_layanan = ?', [$id]);
            $this->db->execute('DELETE FROM layanan WHERE id_layanan = ?', [$id]);
        }
    }

    public function testCreateValidReturnsId(): void
    {
        $id = $this->layanan->create($this->validData());
        $this->createdIds[] = $id;
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function testCreatePersistsData(): void
    {
        $data = $this->validData();
        $id   = $this->layanan->create($data);
        $this->createdIds[] = $id;

        $saved = $this->layanan->read($id);
        $this->assertSame($data['nama_layanan'], $saved['nama_layanan']);
        $this->assertSame((int) $data['biaya'], (int) $saved['biaya']);
    }

    public function testCreateMissingNamaThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['nama_layanan'] = '';
        $this->expectValidationException(fn() => $this->layanan->create($data), ['nama_layanan']);
    }

    public function testCreateBiayaZeroThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['biaya'] = 0;
        $this->expectValidationException(fn() => $this->layanan->create($data), ['biaya']);
    }

    public function testCreateBiayaNegativeThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['biaya'] = -100;
        $this->expectValidationException(fn() => $this->layanan->create($data), ['biaya']);
    }

    public function testCreateBiayaStringCastsToInt(): void
    {
        $data = $this->validData();
        $data['biaya'] = '250000'; // string
        $id = $this->layanan->create($data);
        $this->createdIds[] = $id;

        $saved = $this->layanan->read($id);
        $this->assertSame(250000, (int) $saved['biaya']);
    }

    public function testReadExistingReturnsData(): void
    {
        $data = $this->layanan->read(1);
        $this->assertSame('Audiometri', $data['nama_layanan']);
    }

    public function testReadNonExistentReturnsEmptyArray(): void
    {
        $this->assertEmpty($this->layanan->read(99999));
    }

    public function testUpdateValidAffectsOne(): void
    {
        $id = $this->layanan->create($this->validData());
        $this->createdIds[] = $id;

        $affected = $this->layanan->update($id, ['nama_layanan' => 'Updated Name']);
        $this->assertSame(1, $affected);
    }

    public function testUpdateNonExistentAffectsZero(): void
    {
        $this->assertSame(0, $this->layanan->update(99999, ['nama_layanan' => 'X']));
    }

    public function testDeleteSuccess(): void
    {
        $id = $this->layanan->create($this->validData());
        $this->createdIds[] = $id;
        $this->assertTrue($this->layanan->delete($id));
        $this->assertEmpty($this->layanan->read($id));
    }

    public function testDeleteFkProtectedReturnsFalse(): void
    {
        // Layanan 1 (Audiometri) referenced by TRX-2026001.
        $this->assertFalse($this->layanan->delete(1));
    }

    public function testCountReturnsInt(): void
    {
        $this->assertIsInt($this->layanan->count());
    }

    public function testReadForOptionsReturnsArray(): void
    {
        $options = $this->layanan->readForOptions();
        $this->assertIsArray($options);
        $this->assertNotEmpty($options);
        $this->assertArrayHasKey('id_layanan', $options[0]);
    }

    /**
     * @return array{nama_layanan: string, biaya: int}
     */
    private function validData(): array
    {
        $suffix = bin2hex(random_bytes(4));
        return [
            'nama_layanan' => "Test Layanan {$suffix}",
            'biaya'        => 100000,
        ];
    }
}
