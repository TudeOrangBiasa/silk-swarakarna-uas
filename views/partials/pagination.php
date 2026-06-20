<?php
/**
 * Pagination partial.
 *
 * Expects:
 *   $pagination — array from paginate() helper
 *   $baseUrl    — string, base URL for the current page (e.g. '/pasien')
 *
 * Renders Bootstrap 5 pagination with "Menampilkan X–Y dari Z" info.
 * Shows nothing when total_pages <= 1.
 */
if (!isset($pagination) || $pagination['total_pages'] <= 1) {
    return;
}

$current = $pagination['page'];
$totalPages = $pagination['total_pages'];
$total = $pagination['total'];
$perPage = $pagination['per_page'];
$from = ($current - 1) * $perPage + 1;
$to = min($current * $perPage, $total);

// Preserve all GET params except page
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = $queryParams ? '?' . http_build_query($queryParams) : '';

$pageUrl = fn($p) => $baseUrl . $queryString . ($queryString ? '&' : '?') . 'page=' . $p;
?>
<div class="d-flex justify-content-between align-items-center mt-4 px-2">
    <small class="text-muted">
        Menampilkan <?= $from ?>–<?= $to ?> dari <?= $total ?>
    </small>
    <nav aria-label="Navigasi halaman">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= !$pagination['has_prev'] ? 'disabled' : '' ?>">
                <?php if ($pagination['has_prev']): ?>
                    <a class="page-link" href="<?= htmlspecialchars($pageUrl($current - 1)) ?>">Sebelumnya</a>
                <?php else: ?>
                    <span class="page-link">Sebelumnya</span>
                <?php endif; ?>
            </li>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $current ? 'active' : '' ?>">
                    <?php if ($p === $current): ?>
                        <span class="page-link"><?= $p ?></span>
                    <?php else: ?>
                        <a class="page-link" href="<?= htmlspecialchars($pageUrl($p)) ?>"><?= $p ?></a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= !$pagination['has_next'] ? 'disabled' : '' ?>">
                <?php if ($pagination['has_next']): ?>
                    <a class="page-link" href="<?= htmlspecialchars($pageUrl($current + 1)) ?>">Selanjutnya</a>
                <?php else: ?>
                    <span class="page-link">Selanjutnya</span>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</div>
