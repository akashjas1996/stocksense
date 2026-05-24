<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= APP_URL ?>/">
            <i class="bi bi-house-heart-fill me-1"></i><?= APP_NAME ?>
        </a>
        <div class="d-flex gap-2 align-items-center">
            <a href="<?= APP_URL ?>/scan" class="btn btn-success btn-sm">
                <i class="bi bi-upc-scan"></i> Scan
            </a>
            <?php if (isLoggedIn()): ?>
            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text text-muted small"><?= e(currentUser()['name']) ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/auth/logout">Logout</a></li>
                </ul>
            </div>
            <?php endif ?>
        </div>
    </div>
</nav>

<main class="container-fluid py-3 px-3" style="max-width:600px;margin:auto;">

    <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= e($msg) ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif ?>

    <?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= e($msg) ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif ?>

    <?= $content ?>

</main>

<nav class="navbar fixed-bottom bg-dark border-top border-secondary">
    <div class="container-fluid justify-content-around">
        <a href="<?= APP_URL ?>/" class="nav-link text-light text-center small">
            <i class="bi bi-grid-3x3-gap-fill d-block fs-5"></i>Dashboard
        </a>
        <a href="<?= APP_URL ?>/rooms" class="nav-link text-light text-center small">
            <i class="bi bi-door-open-fill d-block fs-5"></i>Rooms
        </a>
        <a href="<?= APP_URL ?>/scan" class="nav-link text-light text-center small">
            <i class="bi bi-upc-scan d-block fs-5"></i>Scan
        </a>
        <a href="<?= APP_URL ?>/inventory/create" class="nav-link text-light text-center small">
            <i class="bi bi-plus-circle-fill d-block fs-5"></i>Add
        </a>
    </div>
</nav>

<div style="height:70px"></div><!-- bottom nav spacer -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="<?= APP_URL ?>/js/app.js"></script>
</body>
</html>
