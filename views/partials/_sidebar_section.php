<?php
/**
 * Reusable sidebar nav section.
 * @var string $label  Section label (e.g., "Master Data")
 * @var array  $items  Each: ['url' => '/foo', 'icon' => 'bi-x', 'label' => 'Foo']
 */
$current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<div class="px-3 pt-3 pb-1">
    <small class="text-uppercase fw-semibold sidebar-section-label"><?= htmlspecialchars($label) ?></small>
</div>
<ul class="nav flex-column px-2">
    <?php foreach ($items as $item):
        $is_active = str_starts_with($current_uri, $item['url']);
    ?>
        <li class="nav-item">
            <a class="sidebar-link <?= $is_active ? 'active' : '' ?>"
               href="<?= htmlspecialchars($item['url']) ?>"
               <?= $is_active ? 'aria-current="page"' : '' ?>>
                <i class="bi <?= htmlspecialchars($item['icon']) ?> me-3 fs-5"></i>
                <span><?= htmlspecialchars($item['label']) ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
