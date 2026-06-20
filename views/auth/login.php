<?php
/**
 * Login page.
 */

if (is_logged_in()) {
    header('Location: /');
    exit;
}

$flash = flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SILK-Swarakarna</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
</head>
<body class="bg-body min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="bg-info text-white rounded p-2 d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                                <i class="bi bi-soundwave fs-3"></i>
                            </div>
                            <h1 class="h4 fw-bold mb-1">SILK-Swarakarna</h1>
                            <p class="text-muted small mb-0">Masuk ke sistem</p>
                        </div>

                        <?php if ($flash): ?>
                            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($flash['message']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="/login" novalidate>
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="username" class="form-label">Nama Pengguna</label>
                                <input type="text" id="username" name="username" required
                                       class="form-control form-control-lg"
                                       autocomplete="username" autofocus>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Kata Sandi</label>
                                <input type="password" id="password" name="password" required
                                       class="form-control form-control-lg"
                                       autocomplete="current-password">
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                            </button>
                        </form>

                        <p class="text-muted small text-center mt-4 mb-0">
                            Demo: <code>admin</code> / <code>admin123</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
