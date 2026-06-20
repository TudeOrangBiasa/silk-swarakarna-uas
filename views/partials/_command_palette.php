<div class="modal fade" id="commandPalette" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content command-palette-content">
            <div class="modal-body p-0">
                <div class="p-3 border-bottom">
                    <input type="search" class="form-control form-control-lg border-0 shadow-none"
                           id="commandPaletteInput" placeholder="Cari atau navigasi..."
                           aria-label="Cari" autocomplete="off">
                </div>
                <ul class="list-group list-group-flush" id="commandPaletteResults" role="listbox">
                    <?php foreach ([
                        ['url' => '/',              'icon' => 'bi-grid-1x2',         'label' => 'Dashboard'],
                        ['url' => '/pasien',        'icon' => 'bi-people',           'label' => 'Pasien'],
                        ['url' => '/dokter',        'icon' => 'bi-heart-pulse',      'label' => 'Dokter'],
                        ['url' => '/layanan',       'icon' => 'bi-tags',             'label' => 'Layanan'],
                        ['url' => '/pemeriksaan',   'icon' => 'bi-journal-medical',  'label' => 'Pemeriksaan'],
                    ] as $item): ?>
                        <li class="list-group-item command-item" data-url="<?= htmlspecialchars($item['url']) ?>" role="option" tabindex="0">
                            <i class="bi <?= htmlspecialchars($item['icon']) ?> me-3 fs-5 text-muted"></i>
                            <span><?= htmlspecialchars($item['label']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const input = document.getElementById('commandPaletteInput');
    const items = document.querySelectorAll('#commandPaletteResults .command-item');
    const modal = document.getElementById('commandPalette');

    // Open on cmd+K (Mac) or ctrl+K (Win/Linux)
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    });

    // Reset on open
    modal.addEventListener('shown.bs.modal', () => {
        input.value = '';
        items.forEach(it => it.style.display = '');
        input.focus();
    });

    // Filter as user types
    input.addEventListener('input', () => {
        const q = input.value.toLowerCase();
        items.forEach(it => {
            it.style.display = it.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // Enter to navigate, Esc to close
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const visible = Array.from(items).find(it => it.style.display !== 'none');
            if (visible) window.location.href = visible.dataset.url;
        }
    });

    // Click to navigate
    items.forEach(it => it.addEventListener('click', () => window.location.href = it.dataset.url));
})();
</script>
