<?php
/**
 * View Layanan Create
 * Issue #35 — Form kosong, POST ke /layanan
 */

use Silk\Entity\Layanan;
use Silk\Presenter\LayananPresenter;

$presenter = new LayananPresenter(new Layanan());
$row = $presenter->getFormData(null);
?>

<?php include __DIR__ . '/../partials/_flash.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Tambah Layanan</h1>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="post" action="/layanan" novalidate>
            <div class="mb-3">
                <label class="form-label" for="nama_layanan">Nama Layanan <span class="text-danger">*</span></label>
                <input type="text" id="nama_layanan" name="nama_layanan" required maxlength="100"
                       value="<?= htmlspecialchars(old_input('nama_layanan') ?: $row['nama_layanan']) ?>"
                       class="form-control<?= has_error('nama_layanan') ? ' is-invalid' : '' ?>">
                <?php if (has_error('nama_layanan')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('nama_layanan') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="biaya">Biaya <span class="text-danger">*</span></label>
                <input type="number" id="biaya" name="biaya" required min="1" step="1"
                       value="<?= htmlspecialchars(old_input('biaya') ?: (string) $row['biaya']) ?>"
                       class="form-control<?= has_error('biaya') ? ' is-invalid' : '' ?>">
                <div class="form-text">Biaya dalam Rupiah (Rp), tanpa titik atau koma. Contoh: 250000</div>
                <?php if (has_error('biaya')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('biaya') ?></div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
                <a href="/layanan" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
