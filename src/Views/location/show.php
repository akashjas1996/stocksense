<?php
$isRoom = $location['type'] === 'room';
$icon   = $isRoom ? 'bi-door-open-fill' : 'bi-box-seam';
$sub    = $isRoom ? '' : ('In ' . e($location['room_name'] ?? ''));
?>

<!-- Header -->
<div class="mb-3">
    <div class="d-flex align-items-center gap-2">
        <i class="bi <?= $icon ?> fs-3 text-secondary"></i>
        <div>
            <h5 class="fw-bold mb-0"><?= e($location['name']) ?></h5>
            <?php if ($sub): ?><div class="text-muted small"><?= $sub ?></div><?php endif ?>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if ($expired): ?>
<div class="alert alert-danger py-2 small mb-2">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong><?= count($expired) ?> item<?= count($expired) > 1 ? 's' : '' ?> expired</strong>
</div>
<?php endif ?>
<?php if ($expiringSoon): ?>
<div class="alert alert-warning py-2 small mb-2">
    <i class="bi bi-clock-fill"></i>
    <strong><?= count($expiringSoon) ?> expiring within <?= EXPIRY_WARN_DAYS ?> days</strong>
</div>
<?php endif ?>
<?php if ($lowStock): ?>
<div class="alert alert-secondary py-2 small mb-3">
    <i class="bi bi-battery-half"></i>
    <strong><?= count($lowStock) ?> item<?= count($lowStock) > 1 ? 's' : '' ?> running low (&lt;500g)</strong>
</div>
<?php endif ?>

<!-- Item list -->
<?php if (empty($items)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-inbox display-4"></i>
    <p class="mt-2">Nothing stored here.</p>
</div>
<?php else: ?>

<?php
// Group by container when showing a full room
$grouped = [];
foreach ($items as $inv) {
    $key = $inv['container_name'] ?? '__loose__';
    $grouped[$key][] = $inv;
}
?>

<?php foreach ($grouped as $groupName => $groupItems): ?>
<?php if ($isRoom && count($grouped) > 1): ?>
<div class="text-muted small fw-semibold mt-3 mb-1 px-1">
    <?= $groupName === '__loose__' ? 'No container' : e($groupName) ?>
</div>
<?php endif ?>

<div class="d-flex flex-column gap-2 mb-2">
<?php foreach ($groupItems as $inv):
    $status = expiryStatus($inv['expiry_date']);
    $isLow  = $inv['quantity_grams'] < 500;
?>
<div class="item-pill d-flex align-items-center gap-2 <?= $isLow ? 'border-warning' : '' ?>">
    <div class="flex-grow-1 min-w-0">
        <div class="fw-semibold text-truncate"><?= e($inv['item_name']) ?></div>
        <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
            <span class="badge qty-badge <?= $isLow ? 'bg-warning text-dark' : 'bg-dark' ?>">
                <?= formatWeight($inv['quantity_grams']) ?>
            </span>
            <?php if ($status): ?>
            <span class="badge badge-<?= $status ?>">
                <?= $status === 'expired' ? 'Expired' : 'Exp: ' . e($inv['expiry_date']) ?>
            </span>
            <?php endif ?>
            <?php if ($inv['notes']): ?>
            <span class="text-muted small fst-italic"><?= e($inv['notes']) ?></span>
            <?php endif ?>
        </div>
    </div>
    <?php if (isLoggedIn()): ?>
    <a href="<?= APP_URL ?>/inventory/<?= $inv['id'] ?>/consume"
       class="btn btn-sm btn-outline-success flex-shrink-0">
        <i class="bi bi-dash-circle"></i> Use
    </a>
    <?php else: ?>
    <a href="<?= APP_URL ?>/auth/login"
       class="btn btn-sm btn-outline-secondary flex-shrink-0 small">
        Login to use
    </a>
    <?php endif ?>
</div>
<?php endforeach ?>
</div>
<?php endforeach ?>

<div class="text-muted small text-center mt-3">
    <?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?> &middot;
    <?= formatWeight(array_sum(array_column($items, 'quantity_grams'))) ?> total
</div>
<?php endif ?>

<?php if (isLoggedIn()): ?>
<div class="mt-4">
    <?php
    $addUrl = $isRoom
        ? APP_URL . '/inventory/create?room_id=' . $location['id']
        : APP_URL . '/inventory/create?room_id=' . $location['room_id'] . '&container_id=' . $location['id'];
    ?>
    <a href="<?= $addUrl ?>" class="btn btn-dark w-100">
        <i class="bi bi-plus-circle"></i> Add item here
    </a>
</div>
<?php endif ?>
