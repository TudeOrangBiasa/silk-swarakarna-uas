<?php

declare(strict_types=1);

namespace Tests\Presenter;

use PHPUnit\Framework\TestCase;
use Silk\Database;
use Silk\Entity\Dokter;
use Silk\Entity\Layanan;
use Silk\Entity\Pasien;
use Silk\Entity\Pemeriksaan;
use Silk\Presenter\PemeriksaanPresenter;

final class PemeriksaanPresenterTest extends TestCase
{
    private Database $db;
    private PemeriksaanPresenter $presenter;

    private array $createdPemeriksaanIds = [];
    private array $createdPasienIds = [];
    private array $createdDokterIds = [];
    private array $createdLayananIds = [];

    protected function setUp(): void
    {
        $this->db        = Database::getInstance();
        $this->presenter = new PemeriksaanPresenter(new Pemeriksaan());
        $this->createdPemeriksaanIds = [];
        $this->createdPasienIds = [];
        $this->createdDokterIds = [];
        $this->createdLayananIds = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdPemeriksaanIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_periksa = ?', [$id]);
        }
        foreach ($this->createdPasienIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_pasien = ?', [$id]);
            $this->db->execute('DELETE FROM pasien WHERE id_pasien = ?', [$id]);
        }
        foreach ($this->createdDokterIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_dokter = ?', [$id]);
            $this->db->execute('DELETE FROM dokter WHERE id_dokter = ?', [$id]);
        }
        foreach ($this->createdLayananIds as $id) {
            $this->db->execute('DELETE FROM pemeriksaan WHERE id_layanan = ?', [$id]);
            $this->db->execute('DELETE FROM layanan WHERE id_layanan = ?', [$id]);
        }
    }

    public function testGetListDataReturnsRowsAndPagination(): void
    {
        $this->createPemeriksaan();
        $result = $this->presenter->getListData(null, null, null, null, 1, 20);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('pagination', $result);
    }

    public function testGetListDataIncludesStatusBadgeHtml(): void
    {
        $this->createPemeriksaan();
        $result = $this->presenter->getListData(null, null, null, null, 1, 100);
        $first = $result['rows'][0] ?? null;
        $this->assertNotNull($first);
        $this->assertArrayHasKey('status_badge_html', $first);
        $this->assertStringContainsString('badge', $first['status_badge_html']);
    }

    public function testGetListDataWithStatusFilter(): void
    {
        $this->createPemeriksaan(); // status Menunggu
        $result = $this->presenter->getListData(null, 'Menunggu', null, null, 1, 20);
        $this->assertGreaterThanOrEqual(1, $result['pagination']['total']);
    }

    public function testGetListDataWithDateRangeFilter(): void
    {
        $this->createPemeriksaan();
        $result = $this->presenter->getListData(null, null, date('Y-m-d'), date('Y-m-d'), 1, 20);
        $this->assertGreaterThanOrEqual(1, $result['pagination']['total']);
    }

    public function testGetDashboardStatsReturnsAllKeys(): void
    {
        $stats = $this->presenter->getDashboardStats();
        $this->assertArrayHasKey('total_pasien', $stats);
        $this->assertArrayHasKey('total_dokter', $stats);
        $this->assertArrayHasKey('total_layanan', $stats);
        $this->assertArrayHasKey('pemeriksaan_hari_ini', $stats);
        $this->assertArrayHasKey('pemeriksaan_bulan_ini', $stats);
        $this->assertArrayHasKey('pendapatan_bulan_ini', $stats);
        $this->assertIsInt($stats['pendapatan_bulan_ini']);
        $this->assertArrayHasKey('count_by_month', $stats);
        $this->assertArrayHasKey('top_layanan', $stats);
        $this->assertArrayHasKey('dokter_stats', $stats);
    }

    public function testGetDashboardStatsTotalPasienReturnsInt(): void
    {
        $stats = $this->presenter->getDashboardStats();
        $this->assertIsInt($stats['total_pasien']);
        $this->assertGreaterThanOrEqual(0, $stats['total_pasien']);
    }

    public function testGetDashboardStatsCountByMonthHas12Keys(): void
    {
        $stats = $this->presenter->getDashboardStats();
        $this->assertCount(12, $stats['count_by_month']);
    }

    public function testGetCetakDataReturnsRowsAndTotal(): void
    {
        $this->createPemeriksaan();
        $result = $this->presenter->getCetakData(null, null, null, null);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('filters', $result);
        $this->assertIsArray($result['rows']);
        $this->assertIsInt($result['total']);
        $this->assertGreaterThanOrEqual(0, $result['total']);
    }

    public function testGetCetakDataTotalMatchesRowsSum(): void
    {
        $id = $this->createPemeriksaan(); // biaya = 100000
        $this->createdPemeriksaanIds[] = $id;
        $id2 = $this->createPemeriksaan(); // biaya = 100000
        $this->createdPemeriksaanIds[] = $id2;

        $result = $this->presenter->getCetakData(null, null, date('Y-m-d'), date('Y-m-d'));
        $this->assertGreaterThanOrEqual(200000, $result['total']);
    }

    public function testGetPasienOptionsReturnsArray(): void
    {
        $this->assertIsArray($this->presenter->getPasienOptions());
    }

    public function testGetDokterOptionsReturnsArray(): void
    {
        $this->assertIsArray($this->presenter->getDokterOptions());
    }

    public function testGetLayananOptionsReturnsArray(): void
    {
        $this->assertIsArray($this->presenter->getLayananOptions());
    }

    public function testGetStatusOptionsReturnsThreeValues(): void
    {
        $options = $this->presenter->getStatusOptions();
        $this->assertSame(['Menunggu', 'Sedang Diperiksa', 'Selesai'], $options);
    }

    public function testGetAllowedTransitionsForMenunggu(): void
    {
        $transitions = $this->presenter->getAllowedTransitions('Menunggu');
        $this->assertSame(['Sedang Diperiksa', 'Selesai'], $transitions);
    }

    public function testGetAllowedTransitionsForSelesaiIsEmpty(): void
    {
        $this->assertSame([], $this->presenter->getAllowedTransitions('Selesai'));
    }

    public function testGetFormDataEmptyReturnsEmptyRowWithTodayDate(): void
    {
        $form = $this->presenter->getFormData(null);
        $this->assertSame('', $form['id_periksa']);
        $this->assertSame(date('Y-m-d'), $form['tanggal_periksa']);
        $this->assertSame('Menunggu', $form['status_pemeriksaan']);
    }

    public function testGetFormDataExistingReturnsFilledRow(): void
    {
        $id = $this->createPemeriksaan();
        $this->createdPemeriksaanIds[] = $id;

        $form = $this->presenter->getFormData($id);
        $this->assertSame($id, $form['id_periksa']);
    }

    public function testGetCountReturnsInt(): void
    {
        $this->assertIsInt($this->presenter->getCount());
    }

    public function testGetCountByDateReturnsInt(): void
    {
        $this->assertIsInt($this->presenter->getCountByDate(date('Y-m-d')));
    }

    public function testGetLatestReturnsRows(): void
    {
        $latest = $this->presenter->getLatest(5);
        $this->assertIsArray($latest);
        $this->assertLessThanOrEqual(5, count($latest));
    }

    private function createPemeriksaan(): string
    {
        $pasien = new Pasien();
        $pasienId = $pasien->create([
            'nama_pasien'   => 'Presenter Test ' . bin2hex(random_bytes(2)),
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'no_hp'         => '081234567890',
            'alamat'        => 'Jl Test',
        ]);
        $this->createdPasienIds[] = $pasienId;

        $dokter = new Dokter();
        $dokterId = $dokter->create([
            'nama_dokter'     => 'dr. Presenter ' . bin2hex(random_bytes(2)),
            'no_izin_praktik' => 'SIP-' . bin2hex(random_bytes(4)),
            'spesialisasi'    => 'THT',
            'no_hp'           => '081234567891',
        ]);
        $this->createdDokterIds[] = $dokterId;

        $layanan = new Layanan();
        $layananId = $layanan->create([
            'nama_layanan' => 'Presenter ' . bin2hex(random_bytes(2)),
            'biaya'        => 100000,
        ]);
        $this->createdLayananIds[] = $layananId;

        $pemeriksaan = new Pemeriksaan();
        $id = $pemeriksaan->create([
            'id_pasien'       => $pasienId,
            'id_dokter'       => $dokterId,
            'id_layanan'      => $layananId,
            'tanggal_periksa' => date('Y-m-d'),
            'keluhan'         => 'Test',
        ]);
        $this->createdPemeriksaanIds[] = $id;
        return $id;
    }
}
