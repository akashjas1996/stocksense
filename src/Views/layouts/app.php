<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($pageTitle ?? APP_NAME) ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">
</head>
<body>

<!-- Top nav -->
<nav class="top-nav">
    <span class="brand">Stock<span>Sense</span></span>
    <a href="<?= APP_URL ?>/scan" class="icon-btn accent" title="Scan">
        <i class="bi bi-upc-scan"></i>
    </a>
    <?php if (isLoggedIn()): ?>
    <div style="position:relative">
        <button class="icon-btn" id="user-btn" onclick="document.getElementById('user-menu').classList.toggle('open')" style="font-weight:800;font-size:.8rem;">
            <?= strtoupper(substr(currentUser()['name'], 0, 1)) ?>
        </button>
        <div id="user-menu" style="display:none;position:absolute;right:0;top:44px;background:var(--card);border:1.5px solid var(--border);border-radius:var(--r-s);padding:8px;min-width:160px;box-shadow:var(--shadow-l);z-index:300;">
            <div style="padding:6px 10px;font-size:.8rem;color:var(--text-3);font-weight:700;"><?= e(currentUser()['name']) ?></div>
            <a href="<?= APP_URL ?>/auth/logout" style="display:block;padding:8px 10px;font-size:.85rem;font-weight:700;color:var(--danger);text-decoration:none;border-radius:6px;">Sign out</a>
        </div>
    </div>
    <?php endif ?>
</nav>

<!-- Toast messages -->
<div class="toast-stack">
    <?php if ($msg = flash('success')): ?>
    <div class="toast success"><i class="bi bi-check-circle-fill me-2"></i><?= e($msg) ?></div>
    <?php endif ?>
    <?php if ($msg = flash('error')): ?>
    <div class="toast error"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($msg) ?></div>
    <?php endif ?>
</div>

<!-- Page content -->
<div class="page">
    <?= $content ?>
</div>

<!-- Floating bottom nav -->
<?php $cur = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
<nav class="bottom-nav">
    <a href="<?= APP_URL ?>/"        class="<?= $cur === parse_url(APP_URL.'/', PHP_URL_PATH) ? 'active' : '' ?>">
        <i class="bi bi-grid-3x3-gap-fill"></i>Home
    </a>
    <a href="<?= APP_URL ?>/rooms"   class="<?= str_contains($cur, '/rooms') ? 'active' : '' ?>">
        <i class="bi bi-door-open-fill"></i>Rooms
    </a>
    <a href="<?= APP_URL ?>/scan"    class="<?= str_contains($cur, '/scan') ? 'active' : '' ?>">
        <i class="bi bi-upc-scan"></i>Scan
    </a>
    <a href="<?= APP_URL ?>/history" class="<?= str_contains($cur, '/history') ? 'active' : '' ?>">
        <i class="bi bi-graph-up"></i>History
    </a>
    <a href="<?= APP_URL ?>/inventory/create" class="<?= str_contains($cur, '/inventory/create') ? 'active' : '' ?>">
        <i class="bi bi-plus-circle-fill"></i>Add
    </a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// Close user menu on outside click
document.addEventListener('click', e => {
    const menu = document.getElementById('user-menu');
    if (menu && !e.target.closest('#user-btn') && !e.target.closest('#user-menu')) menu.style.display = 'none';
    const btn = document.getElementById('user-btn');
    if (btn && e.target.closest('#user-btn')) menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
});
// Auto-dismiss toasts
document.querySelectorAll('.toast').forEach(t => setTimeout(() => t.style.opacity = '0', 3500));
</script>
<script src="<?= APP_URL ?>/js/app.js"></script>
</body>
</html>
