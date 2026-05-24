<div class="d-flex align-items-center mb-3 gap-2">
    <a href="<?= APP_URL ?>/rooms" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h5 class="fw-bold mb-0 flex-grow-1"><?= e($room['name']) ?></h5>
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>/qr" class="btn btn-sm btn-outline-dark">
        <i class="bi bi-qr-code"></i>
    </a>
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-pencil"></i>
    </a>
</div>

<!-- Containers -->
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="text-muted mb-0">Containers</h6>
    <a href="<?= APP_URL ?>/containers/create?room_id=<?= $room['id'] ?>" class="btn btn-sm btn-outline-dark">
        <i class="bi bi-plus"></i> Add
    </a>
</div>

<?php if (empty($containers)): ?>
<p class="text-muted small mb-3">No containers in this room.</p>
<?php else: ?>
<div class="d-flex flex-column gap-2 mb-4">
    <?php foreach ($containers as $c): ?>
    <a href="<?= APP_URL ?>/containers/<?= $c['id'] ?>" class="text-decoration-none">
        <div class="card location-card">
            <div class="card-body d-flex align-items-center py-2">
                <i class="bi bi-box-seam fs-4 text-secondary me-3"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold"><?= e($c['name']) ?>
                        <span class="badge bg-secondary ms-1 small"><?= e($c['type']) ?></span>
                    </div>
                    <div class="text-muted small">
                        <?= $c['item_count'] ?> items &middot; <?= formatWeight($c['total_grams']) ?>
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </div>
        </div>
    </a>
    <?php endforeach ?>
</div>
<?php endif ?>

<!-- Loose items (no container) -->
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="text-muted mb-0">Items in room <span class="small">(no container)</span></h6>
    <a href="<?= APP_URL ?>/inventory/create?room_id=<?= $room['id'] ?>" class="btn btn-sm btn-outline-dark">
        <i class="bi bi-plus"></i> Add
    </a>
</div>

<?php if (empty($looseItems)): ?>
<p class="text-muted small">No loose items.</p>
<?php else: ?>
<div class="d-flex flex-column gap-2">
    <?php foreach ($looseItems as $inv): ?>
    <?php $status = expiryStatus($inv['expiry_date']); ?>
    <div class="item-pill d-flex align-items-center gap-2">
        <div class="flex-grow-1">
            <div class="fw-semibold"><?= e($inv['item_name']) ?></div>
            <div class="text-muted small">
                <?= formatWeight($inv['quantity_grams']) ?>
                <?php if ($inv['expiry_date']): ?>
                &middot; <span class="badge badge-<?= $status ?: 'ok' ?>">exp: <?= e($inv['expiry_date']) ?></span>
                <?php endif ?>
            </div>
        </div>
        <a href="<?= APP_URL ?>/inventory/<?= $inv['id'] ?>/consume" class="btn btn-sm btn-outline-success">
            <i class="bi bi-dash-circle"></i> Use
        </a>
        <a href="<?= APP_URL ?>/inventory/<?= $inv['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil"></i>
        </a>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>
