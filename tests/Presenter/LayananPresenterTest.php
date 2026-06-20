<?php

declare(strict_types=1);

namespace Tests\Presenter;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Entity\Layanan;
use Silk\Presenter\LayananPresenter;

final class LayananPresenterTest extends TestCase
{
    private Database $db;
    private LayananPresenter $presenter;
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->db        = Database::getInstance();
        $this->presenter = new LayananPresenter(new Layanan());
        $this->createdIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_layanan = ?', [$id]);
            $this->db->execute('DELETE FROM layanan WHERE id_layanan = ?', [$id]);
        }
    }

    public function testGetListDataReturnsRows(): void
    {
        $this->createLayanan();
        $result = $this->presenter->getListData();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testGetListDataIncludesBiayaFmt(): void
    {
        $id = $this->createLayanan();
        $this->createdIds[] = $id;

        $result = $this->presenter->getListData();
        $found = null;
        foreach ($result as $r) {
            if ((int) $r['id_layanan'] === $id) {
                $found = $r;
                break;
            }
        }
        $this->assertNotNull($found);
        $this->assertArrayHasKey('biaya_fmt', $found);
        $this->assertStringContainsString('Rp', $found['biaya_fmt']);
    }

    public function testGetFormDataEmptyReturnsEmptyRow(): void
    {
        $form = $this->presenter->getFormData(null);
        $this->assertSame('', $form['nama_layanan']);
        $this->assertSame(0, (int) $form['biaya']);
    }

    public function testGetFormDataExistingReturnsFilledRow(): void
    {
        $id = $this->createLayanan();
        $this->createdIds[] = $id;

        $form = $this->presenter->getFormData($id);
        $this->assertSame($id, (int) $form['id_layanan']);
    }

    public function testGetOptionsReturnsValueLabelArray(): void
    {
        $id = $this->createLayanan();
        $this->createdIds[] = $id;

        $options = $this->presenter->getOptions();
        $found = false;
        foreach ($options as $opt) {
            if ((int) $opt['value'] === $id) {
                $this->assertArrayHasKey('label', $opt);
                $this->assertStringContainsString('Rp', $opt['label']);
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

    private function createLayanan(string $namePrefix = 'Test Layanan'): int
    {
        $layanan = new Layanan();
        $id = $layanan->create([
            'nama_layanan' => $namePrefix . ' ' . bin2hex(random_bytes(2)),
            'biaya'        => 150000,
        ]);
        $this->createdIds[] = $id;
        return $id;
    }
}
