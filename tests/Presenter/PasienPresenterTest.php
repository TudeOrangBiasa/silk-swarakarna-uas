<?php

declare(strict_types=1);

namespace Tests\Presenter;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Entity\Pasien;
use Silk\Presenter\PasienPresenter;

final class PasienPresenterTest extends TestCase
{
    private Database $db;
    private PasienPresenter $presenter;

    /** @var list<string> */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->db        = Database::getInstance();
        $this->presenter = new PasienPresenter(new Pasien());
        $this->createdIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_pasien = ?', [$id]);
            $this->db->execute('DELETE FROM pasien WHERE id_pasien = ?', [$id]);
        }
    }

    public function testGetListDataReturnsRowsAndPagination(): void
    {
        $id = $this->createPasien();
        $this->createdIds[] = $id;

        $result = $this->presenter->getListData(null, 1, 20);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertGreaterThanOrEqual(1, count($result['rows']));
        $this->assertArrayHasKey('total', $result['pagination']);
    }

    public function testGetListDataWithKeywordFilters(): void
    {
        $id = $this->createPasien('UniqueKeyword');
        $this->createdIds[] = $id;

        $result = $this->presenter->getListData('UniqueKeyword', 1, 20);
        $ids = array_column($result['rows'], 'id_pasien');
        $this->assertContains($id, $ids);
    }

    public function testGetListDataIncludesFormattedDate(): void
    {
        $id = $this->createPasien();
        $this->createdIds[] = $id;

        $result = $this->presenter->getListData(null, 1, 100);
        $found = null;
        foreach ($result['rows'] as $r) {
            if ($r['id_pasien'] === $id) {
                $found = $r;
                break;
            }
        }
        $this->assertNotNull($found);
        $this->assertArrayHasKey('tanggal_lahir_fmt', $found);
        $this->assertSame('15/05/1990', $found['tanggal_lahir_fmt']);
    }

    public function testGetListDataPaginationTotalMatchesCount(): void
    {
        $result = $this->presenter->getListData(null, 1, 20);
        $count = $this->presenter->getCount();
        $this->assertSame($count, $result['pagination']['total']);
    }

    public function testGetFormDataEmptyReturnsEmptyRow(): void
    {
        $form = $this->presenter->getFormData(null);
        $this->assertSame('', $form['nama_pasien']);
        $this->assertSame('', $form['tanggal_lahir']);
        $this->assertSame('', $form['jenis_kelamin']);
        $this->assertSame('', $form['pekerjaan']);
        $this->assertSame('', $form['golongan_darah']);
        $this->assertSame('', $form['riwayat_penyakit']);
        $this->assertSame('', $form['alergi']);
        $this->assertSame('', $form['no_hp']);
        $this->assertSame('', $form['alamat']);
    }

    public function testGetFormDataExistingReturnsFilledRow(): void
    {
        $id = $this->createPasien();
        $this->createdIds[] = $id;

        $form = $this->presenter->getFormData($id);
        $this->assertSame($id, $form['id_pasien']);
        $this->assertNotEmpty($form['nama_pasien']);
    }

    public function testGetOptionsReturnsValueLabelArray(): void
    {
        $id = $this->createPasien();
        $this->createdIds[] = $id;

        $options = $this->presenter->getOptions();
        $this->assertIsArray($options);
        $found = false;
        foreach ($options as $opt) {
            if ($opt['value'] === $id) {
                $this->assertArrayHasKey('label', $opt);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Created pasien not in options');
    }

    public function testGetCountReturnsInt(): void
    {
        $this->assertIsInt($this->presenter->getCount());
    }

    private function createPasien(string $namePrefix = 'Test Presenter'): string
    {
        $pasien = new Pasien();
        return $pasien->create([
            'nama_pasien'      => $namePrefix . ' ' . bin2hex(random_bytes(2)),
            'tanggal_lahir'    => '1990-05-15',
            'jenis_kelamin'    => 'L',
            'pekerjaan'        => 'Mahasiswa',
            'golongan_darah'   => 'A',
            'riwayat_penyakit' => 'Tidak ada',
            'alergi'           => 'Tidak ada',
            'no_hp'            => '081234567890',
            'alamat'           => 'Jl Test',
        ]);
    }
}
