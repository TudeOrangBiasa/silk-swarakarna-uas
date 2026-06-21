<?php
/** @var string $page_title Set by router to override the page title. */
$page_title = $page_title ?? 'SILK-Swarakarna';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= time() ?>">
    <script>
        if (localStorage.getItem('sidebar-collapsed') === '1') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
</head>
<body class="bg-body overflow-hidden">
    <a class="visually-hidden-focusable" href="#mainContent">Skip to main content</a>
    <div class="d-flex vh-100 flex-column flex-lg-row">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content-wrapper flex-grow-1 bg-body-tertiary d-flex flex-column w-100 overflow-hidden">
            <?php include __DIR__ . '/_topbar.php'; ?>
            <main id="mainContent" class="flex-grow-1 overflow-y-auto container-fluid p-4 p-lg-5">
