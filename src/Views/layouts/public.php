<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">
    <style>
        body { background: #f8f9fa; }
        .qty-badge { font-size: .85rem; font-weight: 600; }
    </style>
</head>
<body>
<div class="sticky-top bg-dark text-white px-3 py-2 d-flex align-items-center gap-2">
    <i class="bi bi-house-heart-fill text-success"></i>
    <span class="fw-bold"><?= APP_NAME ?></span>
    <span class="ms-auto small text-secondary">
        <?php if (isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/" class="text-secondary text-decoration-none">Dashboard →</a>
        <?php else: ?>
        <a href="<?= APP_URL ?>/auth/login" class="text-secondary text-decoration-none">Sign in →</a>
        <?php endif ?>
    </span>
</div>

<main class="container-fluid py-3 px-3" style="max-width:560px;margin:auto;">
    <?= $content ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
