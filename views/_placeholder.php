<?php
/** @var string $viewPath view name like 'pasien/index' */
$entity = explode('/', $viewPath)[0] ?? 'unknown';
?>
<div class="alert alert-info" role="alert">
    <h4 class="alert-heading">Halaman dalam pengembangan</h4>
    <p class="mb-0">
        View <code><?= htmlspecialchars($viewPath) ?></code> belum diimplementasi.
        Lihat issue terkait di issue tracker.
    </p>
</div>
