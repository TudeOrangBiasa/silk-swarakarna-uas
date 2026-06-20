<?php

declare(strict_types=1);

namespace Tests\Presenter;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Entity\Dokter;
use Silk\Presenter\DokterPresenter;

final class DokterPresenterTest extends TestCase
{
    private Database $db;
    private DokterPresenter $presenter;
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->db        = Database::getInstance();
        $this->presenter = new DokterPresenter(new Dokter());
        $this->createdIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_dokter = ?', [$id]);
            $this->db->execute('DELETE FROM dokter WHERE id_dokter = ?', [$id]);
        }
    }

    public function testGetListDataReturnsRowsAndPagination(): void
    {
        $this->createDokter();
        $result = $this->presenter->getListData(null, 1, 20);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('pagination', $result);
    }

    public function testGetListDataIncludesCreatedAtFmt(): void
    {
        $id = $this->createDokter();

        $result = $this->presenter->getListData(null, 1, 100);
        $found = null;
        foreach ($result['rows'] as $r) {
            if ((int) $r['id_dokter'] === $id) {
                $found = $r;
                break;
            }
        }
        $this->assertNotNull($found);
        $this->assertArrayHasKey('created_at_fmt', $found);
    }

    public function testGetListDataWithKeywordFilters(): void
    {
        $id = $this->createDokter('UniqueKeyword');

        $result = $this->presenter->getListData('UniqueKeyword', 1, 20);
        $found = false;
        foreach ($result['rows'] as $r) {
            if ((int) $r['id_dokter'] === $id) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testGetFormDataEmptyReturnsEmptyRowWithSpesialisasiTHT(): void
    {
        $form = $this->presenter->getFormData(null);
        $this->assertSame('', $form['nama_dokter']);
        $this->assertSame('THT', $form['spesialisasi']);
    }

    public function testGetFormDataExistingReturnsFilledRow(): void
    {
        $id = $this->createDokter();

        $form = $this->presenter->getFormData($id);
        $this->assertSame($id, (int) $form['id_dokter']);
    }

    public function testGetOptionsReturnsValueLabelArray(): void
    {
        $id = $this->createDokter();

        $options = $this->presenter->getOptions();
        $found = false;
        foreach ($options as $opt) {
            if ((int) $opt['value'] === $id) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testGetCountReturnsInt(): void
    {
        $this->assertIsInt($this->presenter->getCount());
    }

    private function createDokter(string $namePrefix = 'dr. Test Presenter'): int
    {
        $dokter = new Dokter();
        $id = $dokter->create([
            'nama_dokter'     => $namePrefix . ' ' . bin2hex(random_bytes(2)),
            'no_izin_praktik' => 'SIP-' . bin2hex(random_bytes(4)),
            'spesialisasi'    => 'THT',
            'no_hp'           => '081234567890',
        ]);
        $this->createdIds[] = $id;
        return $id;
    }
}
