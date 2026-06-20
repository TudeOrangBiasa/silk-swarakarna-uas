<?php

declare(strict_types=1);

namespace Tests\Entity;

use Silk\Database;
use Silk\Entity\Pasien;
use Silk\Exception\ValidationException;
use Tests\EntityTestCase;

/**
 * Pasien entity tests.
 *
 * Isolation: explicit cleanup per test via $createdIds tracking + tearDown.
 * Transaction rollback is NOT used because Pasien::create() manages its
 * own transactions internally (PDO does not support nested transactions),
 * which would conflict with a setUp transaction.
 *
 * Seed data (database/silk_swarakarna.sql):
 *   RM-001 Andi Pratama,  1990-05-15, 081234567890, Jl. Sudirman No. 45
 *   RM-002 Siti Aminah,   1985-11-22, 082345678901, Jl. Tukad Badung No. 12
 *   RM-003 I Wayan Surya, 2010-03-08, 083456789012, Jl. Gatot Subroto No. 88
 */
final class PasienTest extends EntityTestCase
{
    private Database $db;
    private Pasien $pasien;

    /** @var list<string> Pasien IDs created during test, for cleanup in tearDown. */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->db     = Database::getInstance();
        $this->pasien = new Pasien();
        $this->createdIds = [];
    }

    protected function tearDown(): void
    {
        // Clean up in FK order: child rows (pemeriksaan) first, then pasien.
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_pasien = ?', [$id]);
            $this->db->execute('DELETE FROM pasien WHERE id_pasien = ?', [$id]);
        }
    }

    // ---------------------------------------------------------------
    // generateKodeOtomatis()
    // ---------------------------------------------------------------

    public function testGenerateKodeOtomatisReturnsRmFormat(): void
    {
        $kode = $this->pasien->generateKodeOtomatis();
        $this->assertMatchesRegularExpression('/^RM-\d{3}$/', $kode);
    }

    public function testGenerateKodeOtomatisIncrements(): void
    {
        $first = $this->pasien->generateKodeOtomatis();
        $firstNum = (int) substr($first, 3);

        $id = $this->pasien->create($this->validData());
        $this->createdIds[] = $id;

        $next = $this->pasien->generateKodeOtomatis();
        $nextNum = (int) substr($next, 3);

        $this->assertGreaterThan($firstNum, $nextNum);
    }

    // ---------------------------------------------------------------
    // create() — happy path
    // ---------------------------------------------------------------

    public function testCreateReturnsId(): void
    {
        $id = $this->pasien->create($this->validData());
        $this->createdIds[] = $id;

        $this->assertIsString($id);
        $this->assertStringStartsWith('RM-', $id);
    }

    public function testCreatePersistsData(): void
    {
        $data = $this->validData();
        $id   = $this->pasien->create($data);
        $this->createdIds[] = $id;

        $saved = $this->pasien->read($id);
        $this->assertIsArray($saved);
        $this->assertSame($id, $saved['id_pasien']);
        $this->assertSame($data['nama_pasien'], $saved['nama_pasien']);
        $this->assertSame($data['tanggal_lahir'], $saved['tanggal_lahir']);
        $this->assertSame($data['no_hp'], $saved['no_hp']);
        $this->assertSame($data['alamat'], $saved['alamat']);
    }

    // ---------------------------------------------------------------
    // create() — validation errors
    // ---------------------------------------------------------------

    public function testCreateMissingNamaPasienThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['nama_pasien'] = '';
        $this->expectValidationException(fn() => $this->pasien->create($data), ['nama_pasien']);
    }

    public function testCreateMissingAllRequiredThrowsAllErrors(): void
    {
        $data = [
            'nama_pasien'   => '',
            'tanggal_lahir'  => '',
            'no_hp'         => '',
            'alamat'        => '',
        ];
        $this->expectValidationException(fn() => $this->pasien->create($data), [
            'nama_pasien', 'tanggal_lahir', 'no_hp', 'alamat',
        ]);
    }

    public function testCreateFutureTanggalLahirThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['tanggal_lahir'] = '2030-01-01';
        $this->expectValidationException(fn() => $this->pasien->create($data), ['tanggal_lahir']);
    }

    public function testCreateInvalidNoHpThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['no_hp'] = 'abc';
        $this->expectValidationException(fn() => $this->pasien->create($data), ['no_hp']);
    }

    public function testCreateNoHpTooShortThrowsValidationException(): void
    {
        $data = $this->validData();
        $data['no_hp'] = '123';
        $this->expectValidationException(fn() => $this->pasien->create($data), ['no_hp']);
    }

    // ---------------------------------------------------------------
    // read()
    // ---------------------------------------------------------------

    public function testReadExistingReturnsData(): void
    {
        $data = $this->pasien->read('RM-001');
        $this->assertIsArray($data);
        $this->assertSame('RM-001', $data['id_pasien']);
        $this->assertSame('Andi Pratama', $data['nama_pasien']);
    }

    public function testReadNonExistentReturnsEmptyArray(): void
    {
        $data = $this->pasien->read('RM-999');
        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }

    // ---------------------------------------------------------------
    // update()
    // ---------------------------------------------------------------

    public function testUpdateValidAffectsOne(): void
    {
        $data = $this->validData();
        $id   = $this->pasien->create($data);
        $this->createdIds[] = $id;

        $affected = $this->pasien->update($id, ['nama_pasien' => 'Updated Name']);
        $this->assertSame(1, $affected);

        $saved = $this->pasien->read($id);
        $this->assertSame('Updated Name', $saved['nama_pasien']);
    }

    public function testUpdateNonExistentAffectsZero(): void
    {
        $affected = $this->pasien->update('RM-999', ['nama_pasien' => 'Nobody']);
        $this->assertSame(0, $affected);
    }

    // ---------------------------------------------------------------
    // delete()
    // ---------------------------------------------------------------

    public function testDeleteSuccess(): void
    {
        $data = $this->validData();
        $id   = $this->pasien->create($data);
        $this->createdIds[] = $id; // for cleanup if assertion fails before delete

        $result = $this->pasien->delete($id);
        $this->assertTrue($result);

        // Confirm the record is gone
        $this->assertEmpty($this->pasien->read($id));
    }

    public function testDeleteFkProtectedReturnsFalse(): void
    {
        // RM-001 is referenced by seed pemeriksaan TRX-2026001 via FK ON DELETE RESTRICT.
        $result = $this->pasien->delete('RM-001');
        $this->assertFalse($result);

        // Confirm record still exists
        $data = $this->pasien->read('RM-001');
        $this->assertSame('RM-001', $data['id_pasien']);
    }

    // ---------------------------------------------------------------
    // search()
    // ---------------------------------------------------------------

    public function testSearchFindsMatch(): void
    {
        $data = $this->validData();
        $id   = $this->pasien->create($data);
        $this->createdIds[] = $id;

        $results = $this->pasien->search($data['nama_pasien']);
        $this->assertNotEmpty($results);
        $this->assertContains($id, array_column($results, 'id_pasien'));
    }

    public function testSearchNoMatchReturnsEmpty(): void
    {
        $results = $this->pasien->search('NONEXISTENT_TERM_XYZ123');
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    // ---------------------------------------------------------------
    // count()
    // ---------------------------------------------------------------

    public function testCountReturnsInt(): void
    {
        $count = $this->pasien->count();
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * @return array{nama_pasien: string, tanggal_lahir: string, no_hp: string, alamat: string}
     */
    private function validData(): array
    {
        $suffix = bin2hex(random_bytes(4));
        return [
            'nama_pasien'   => "Test Pasien {$suffix}",
            'tanggal_lahir' => '1990-01-01',
            'no_hp'         => '081234567890',
            'alamat'        => 'Jl. Test No. 123, Denpasar',
        ];
    }
}
