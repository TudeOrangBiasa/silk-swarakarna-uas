<?php
/**
 * View Pasien Create
 * Issue #31 — Form kosong, POST ke /pasien
 */

use Silk\Entity\Pasien;
use Silk\Presenter\PasienPresenter;

$presenter = new PasienPresenter(new Pasien());
$row = $presenter->getFormData(null);
?>

<?php include __DIR__ . '/../partials/_flash.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold mb-0">Tambah Pasien</h1>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="post" action="/pasien" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="nama_pasien">Nama Pasien <span class="text-danger">*</span></label>
                <input type="text" id="nama_pasien" name="nama_pasien" required maxlength="100"
                       value="<?= htmlspecialchars(old_input('nama_pasien') ?: $row['nama_pasien']) ?>"
                       class="form-control<?= has_error('nama_pasien') ? ' is-invalid' : '' ?>">
                <?php if (has_error('nama_pasien')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('nama_pasien') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="tanggal_lahir">Tanggal Lahir <span class="text-danger">*</span></label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" required max="<?= date('Y-m-d') ?>"
                       value="<?= htmlspecialchars(old_input('tanggal_lahir') ?: $row['tanggal_lahir']) ?>"
                       class="form-control<?= has_error('tanggal_lahir') ? ' is-invalid' : '' ?>">
                <?php if (has_error('tanggal_lahir')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('tanggal_lahir') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label d-block">Jenis Kelamin <span class="text-danger">*</span></label>
                <?php $jk = old_input('jenis_kelamin') ?: ($row['jenis_kelamin'] ?? ''); ?>
                <div class="btn-group" role="group" aria-label="Jenis Kelamin">
                    <input type="radio" class="btn-check" name="jenis_kelamin" id="jk_l" value="L" <?= $jk === 'L' ? 'checked' : '' ?> required>
                    <label class="btn btn-outline-primary" for="jk_l">Laki-laki</label>
                    <input type="radio" class="btn-check" name="jenis_kelamin" id="jk_p" value="P" <?= $jk === 'P' ? 'checked' : '' ?> required>
                    <label class="btn btn-outline-primary" for="jk_p">Perempuan</label>
                </div>
                <?php if (has_error('jenis_kelamin')): ?>
                    <div class="text-danger small mt-1"><?= error_for('jenis_kelamin') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="pekerjaan">Pekerjaan</label>
                <input type="text" id="pekerjaan" name="pekerjaan" maxlength="100"
                       value="<?= htmlspecialchars(old_input('pekerjaan') ?: ($row['pekerjaan'] ?? '')) ?>"
                       class="form-control<?= has_error('pekerjaan') ? ' is-invalid' : '' ?>"
                       placeholder="Contoh: PNS, Wiraswasta, Mahasiswa">
                <?php if (has_error('pekerjaan')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('pekerjaan') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="golongan_darah">Golongan Darah</label>
                <?php $gd = old_input('golongan_darah') ?: ($row['golongan_darah'] ?? ''); ?>
                <select id="golongan_darah" name="golongan_darah" class="form-select<?= has_error('golongan_darah') ? ' is-invalid' : '' ?>">
                    <option value="">-- Tidak Diketahui --</option>
                    <option value="A" <?= $gd === 'A' ? 'selected' : '' ?>>A</option>
                    <option value="B" <?= $gd === 'B' ? 'selected' : '' ?>>B</option>
                    <option value="AB" <?= $gd === 'AB' ? 'selected' : '' ?>>AB</option>
                    <option value="O" <?= $gd === 'O' ? 'selected' : '' ?>>O</option>
                </select>
                <?php if (has_error('golongan_darah')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('golongan_darah') ?></div>
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

            <div class="mb-3">
                <label class="form-label" for="alamat">Alamat <span class="text-danger">*</span></label>
                <textarea id="alamat" name="alamat" required class="form-control<?= has_error('alamat') ? ' is-invalid' : '' ?>" rows="3"><?= htmlspecialchars(old_input('alamat') ?: ($row['alamat'] ?? '')) ?></textarea>
                <?php if (has_error('alamat')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('alamat') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="riwayat_penyakit">Riwayat Penyakit</label>
                <textarea id="riwayat_penyakit" name="riwayat_penyakit" class="form-control<?= has_error('riwayat_penyakit') ? ' is-invalid' : '' ?>" rows="3"
                          placeholder="Contoh: Hipertensi, Diabetes (kosongkan jika tidak ada)"><?= htmlspecialchars(old_input('riwayat_penyakit') ?: ($row['riwayat_penyakit'] ?? '')) ?></textarea>
                <?php if (has_error('riwayat_penyakit')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('riwayat_penyakit') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="alergi">Alergi</label>
                <textarea id="alergi" name="alergi" class="form-control<?= has_error('alergi') ? ' is-invalid' : '' ?>" rows="3"
                          placeholder="Contoh: Seafood, Debu, Penisilin"><?= htmlspecialchars(old_input('alergi') ?: ($row['alergi'] ?? '')) ?></textarea>
                <?php if (has_error('alergi')): ?>
                    <div class="invalid-feedback d-block"><?= error_for('alergi') ?></div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button>
                <a href="/pasien" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
