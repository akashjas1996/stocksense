<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= $pageTitle ?? APP_NAME ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">
    <!-- PWA -->
    <link rel="manifest" href="<?= APP_URL ?>/manifest.php">
    <meta name="theme-color" content="#D97706">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="StockSense">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>/icons/apple-touch-icon.png">
</head>
<body style="padding-bottom:24px;">

<nav class="top-nav">
    <span class="brand">Stock<span>Sense</span></span>
    <?php if (isLoggedIn()): ?>
    <a href="<?= APP_URL ?>/" class="btn-outline" style="padding:7px 14px;font-size:.8rem;">Dashboard</a>
    <?php else: ?>
    <a href="<?= APP_URL ?>/auth/login" class="btn-outline" style="padding:7px 14px;font-size:.8rem;">Sign in</a>
    <?php endif ?>
</nav>

<div class="page">
    <?= $content ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
