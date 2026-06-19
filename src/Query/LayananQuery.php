<?php

declare(strict_types=1);

namespace Silk\Query;

use Silk\Database;

final class LayananQuery
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findLayananForOptions(): array
    {
        return $this->db->query('SELECT id_layanan, nama_layanan, biaya FROM layanan ORDER BY nama_layanan ASC');
    }
}
