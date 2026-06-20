<?php
/**
 * View Pemeriksaan Create
 * Issue #36 — 3 FK dropdowns (Pasien, Dokter, Layanan) + keluhan + tanggal
 */

use Silk\Entity\Pemeriksaan;
use Silk\Presenter\PemeriksaanPresenter;

$presenter = new PemeriksaanPresenter(new Pemeriksaan());
$row = $presenter->getFormData(null);
$pasienOptions = $presenter->getPasienOptions();
$dokterOptions = $presenter->getDokterOptions();
$layananOptions = $presenter->getLayananOptions();
$flash = flash_message();
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Tambah Pemeriksaan</h1>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="post" action="/pemeriksaan" novalidate>
            <div class="mb-3">
                <label class="form-label" for="id_pasien">Pasien <span class="text-danger">*</span></label>
                <select id="id_pasien" name="id_pasien" required class="form-select<?= has_error('id_pasien') ? ' is-invalid' : '' ?>">
                    <option value="">-- Pilih Pasien --</option>
                    <?php foreach ($pasienOptions as $opt): ?>
                        <option value="<?= htmlspecialchars($opt['value']) ?>" <?= (old_input('id_pasien') ?: $row['id_pasien']) === $opt['value'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (has_error('id_pasien')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('id_pasien') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="id_dokter">Dokter <span class="text-danger">*</span></label>
                <select id="id_dokter" name="id_dokter" required class="form-select<?= has_error('id_dokter') ? ' is-invalid' : '' ?>">
                    <option value="">-- Pilih Dokter --</option>
                    <?php foreach ($dokterOptions as $opt): ?>
                        <option value="<?= htmlspecialchars($opt['value']) ?>" <?= (old_input('id_dokter') ?: (string) $row['id_dokter']) === $opt['value'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (has_error('id_dokter')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('id_dokter') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="id_layanan">Layanan <span class="text-danger">*</span></label>
                <select id="id_layanan" name="id_layanan" required class="form-select<?= has_error('id_layanan') ? ' is-invalid' : '' ?>">
                    <option value="">-- Pilih Layanan --</option>
                    <?php foreach ($layananOptions as $opt): ?>
                        <option value="<?= htmlspecialchars($opt['value']) ?>" <?= (old_input('id_layanan') ?: (string) $row['id_layanan']) === $opt['value'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($opt['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (has_error('id_layanan')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('id_layanan') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="tanggal_periksa">Tanggal Periksa <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_periksa" name="tanggal_periksa" required
                       value="<?= htmlspecialchars(old_input('tanggal_periksa') ?: $row['tanggal_periksa']) ?>"
                       class="form-control<?= has_error('tanggal_periksa') ? ' is-invalid' : '' ?>">
                <?php if (has_error('tanggal_periksa')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('tanggal_periksa') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="keluhan">Keluhan <span class="text-danger">*</span></label>
                <textarea id="keluhan" name="keluhan" required class="form-control<?= has_error('keluhan') ? ' is-invalid' : '' ?>" rows="3"
                          placeholder="Tuliskan keluhan pasien..."><?= htmlspecialchars(old_input('keluhan') ?: ($row['keluhan'] ?? '')) ?></textarea>
                <?php if (has_error('keluhan')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('keluhan') ?></div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
                <a href="/pemeriksaan" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
