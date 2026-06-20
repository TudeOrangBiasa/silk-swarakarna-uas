<?php
/**
 * View Pasien Create
 * Issue #31 — Form kosong, POST ke /pasien
 */

use Silk\Entity\Pasien;
use Silk\Presenter\PasienPresenter;

$presenter = new PasienPresenter(new Pasien());
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
    <h1 class="h3 fw-semibold mb-0">Tambah Pasien</h1>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="post" action="/pasien" novalidate>
            <div class="mb-3">
                <label class="form-label">Nama Pasien <span class="text-danger">*</span></label>
                <input type="text" name="nama_pasien" required maxlength="100"
                       value="<?= htmlspecialchars(old_input('nama_pasien') ?: $row['nama_pasien']) ?>"
                       class="form-control<?= has_error('nama_pasien') ? ' is-invalid' : '' ?>">
                <?php if (has_error('nama_pasien')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('nama_pasien') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_lahir" required max="<?= date('Y-m-d') ?>"
                       value="<?= htmlspecialchars(old_input('tanggal_lahir') ?: $row['tanggal_lahir']) ?>"
                       class="form-control<?= has_error('tanggal_lahir') ? ' is-invalid' : '' ?>">
                <?php if (has_error('tanggal_lahir')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('tanggal_lahir') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                <?php $jk = old_input('jenis_kelamin') ?: ($row['jenis_kelamin'] ?? ''); ?>
                <select name="jenis_kelamin" required class="form-select<?= has_error('jenis_kelamin') ? ' is-invalid' : '' ?>">
                    <option value="">-- Pilih --</option>
                    <option value="L" <?= $jk === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= $jk === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
                <?php if (has_error('jenis_kelamin')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('jenis_kelamin') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">No HP <span class="text-danger">*</span></label>
                <input type="text" name="no_hp" required pattern="[0-9]{10,15}"
                       value="<?= htmlspecialchars(old_input('no_hp') ?: ($row['no_hp'] ?? '')) ?>"
                       class="form-control<?= has_error('no_hp') ? ' is-invalid' : '' ?>"
                       placeholder="08xxxxxxxxxx">
                <?php if (has_error('no_hp')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('no_hp') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat <span class="text-danger">*</span></label>
                <textarea name="alamat" required class="form-control<?= has_error('alamat') ? ' is-invalid' : '' ?>" rows="3"><?= htmlspecialchars(old_input('alamat') ?: ($row['alamat'] ?? '')) ?></textarea>
                <?php if (has_error('alamat')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('alamat') ?></div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
                <a href="/pasien" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
