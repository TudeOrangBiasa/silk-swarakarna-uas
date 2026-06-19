<?php
/** @var string $page the requested URL that 404'd */
?>
<div class="text-center py-5">
    <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
    <h1 class="display-4 fw-bold mt-3">404</h1>
    <p class="lead">Halaman <code><?= htmlspecialchars($page) ?></code> tidak ditemukan.</p>
    <a href="<?= APP_URL ?>" class="btn btn-primary">
        <i class="bi bi-house"></i> Kembali ke Dashboard
    </a>
</div>
