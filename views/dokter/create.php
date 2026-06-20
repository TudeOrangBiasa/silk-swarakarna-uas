<?php
/**
 * View Dokter Create
 * Issue #33 — Form kosong, POST ke /dokter
 */

use Silk\Entity\Dokter;
use Silk\Presenter\DokterPresenter;

$presenter = new DokterPresenter(new Dokter());
$row = $presenter->getFormData(null);
$flash = flash_message();
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Tambah Dokter</h1>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="post" action="/dokter" novalidate>
            <div class="mb-3">
                <label class="form-label" for="nama_dokter">Nama Dokter <span class="text-danger">*</span></label>
                <input type="text" id="nama_dokter" name="nama_dokter" required maxlength="100"
                       value="<?= htmlspecialchars(old_input('nama_dokter') ?: $row['nama_dokter']) ?>"
                       class="form-control<?= has_error('nama_dokter') ? ' is-invalid' : '' ?>">
                <?php if (has_error('nama_dokter')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('nama_dokter') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="no_izin_praktik">No Izin Praktik <span class="text-danger">*</span></label>
                <input type="text" id="no_izin_praktik" name="no_izin_praktik" required maxlength="50"
                       value="<?= htmlspecialchars(old_input('no_izin_praktik') ?: ($row['no_izin_praktik'] ?? '')) ?>"
                       class="form-control<?= has_error('no_izin_praktik') ? ' is-invalid' : '' ?>">
                <?php if (has_error('no_izin_praktik')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('no_izin_praktik') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="spesialisasi">Spesialisasi <span class="text-danger">*</span></label>
                <input type="text" id="spesialisasi" name="spesialisasi" required maxlength="100"
                       value="<?= htmlspecialchars(old_input('spesialisasi') ?: $row['spesialisasi']) ?>"
                       class="form-control<?= has_error('spesialisasi') ? ' is-invalid' : '' ?>">
                <?php if (has_error('spesialisasi')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('spesialisasi') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="no_hp">No HP <span class="text-danger">*</span></label>
                <input type="text" id="no_hp" name="no_hp" required pattern="[0-9]{10,15}"
                       value="<?= htmlspecialchars(old_input('no_hp') ?: ($row['no_hp'] ?? '')) ?>"
                       class="form-control<?= has_error('no_hp') ? ' is-invalid' : '' ?>"
                       placeholder="08xxxxxxxxxx">
                <?php if (has_error('no_hp')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('no_hp') ?></div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
                <a href="/dokter" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
