<div class="d-flex align-items-center mb-3 gap-2">
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0 flex-grow-1">
        <?= e($container['name']) ?>
        <span class="badge bg-secondary small"><?= e($container['type']) ?></span>
    </h5>
    <a href="<?= APP_URL ?>/containers/<?= $container['id'] ?>/qr" class="btn btn-sm btn-outline-dark">
        <i class="bi bi-qr-code"></i>
    </a>
    <a href="<?= APP_URL ?>/containers/<?= $container['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-pencil"></i>
    </a>
</div>

<p class="text-muted small mb-3">In <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>"><?= e($room['name']) ?></a></p>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="text-muted mb-0">Items</h6>
    <a href="<?= APP_URL ?>/inventory/create?room_id=<?= $container['room_id'] ?>&container_id=<?= $container['id'] ?>"
       class="btn btn-sm btn-outline-dark"><i class="bi bi-plus"></i> Add</a>
</div>

<?php if (empty($items)): ?>
<div class="text-center py-4 text-muted">
    <i class="bi bi-box-seam display-4"></i>
    <p class="mt-2 small">Nothing here yet.</p>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-2">
    <?php foreach ($items as $inv): ?>
    <?php $status = expiryStatus($inv['expiry_date']); ?>
    <div class="item-pill d-flex align-items-center gap-2">
        <div class="flex-grow-1">
            <div class="fw-semibold"><?= e($inv['item_name']) ?></div>
            <div class="text-muted small">
                <?= formatWeight($inv['quantity_grams']) ?>
                <?php if ($inv['expiry_date']): ?>
                &middot; <span class="badge badge-<?= $status ?: 'ok' ?>">
                    <?= $status === 'expired' ? 'expired' : 'exp' ?>: <?= e($inv['expiry_date']) ?>
                </span>
                <?php endif ?>
            </div>
            <?php if ($inv['notes']): ?>
            <div class="text-muted small fst-italic"><?= e($inv['notes']) ?></div>
            <?php endif ?>
        </div>
        <a href="<?= APP_URL ?>/inventory/<?= $inv['id'] ?>/consume" class="btn btn-sm btn-outline-success">
            <i class="bi bi-dash-circle"></i>
        </a>
        <a href="<?= APP_URL ?>/inventory/<?= $inv['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil"></i>
        </a>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>
