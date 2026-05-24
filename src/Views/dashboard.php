<h5 class="fw-bold mb-3">Dashboard</h5>

<?php if ($expired): ?>
<div class="alert alert-danger py-2">
    <strong><i class="bi bi-exclamation-triangle-fill"></i> Expired items</strong>
    <ul class="mb-0 mt-1 small">
        <?php foreach ($expired as $e): ?>
        <li><?= e($e['item_name']) ?> — <?= e($e['room_name']) ?> (<?= e($e['expiry_date']) ?>)</li>
        <?php endforeach ?>
    </ul>
</div>
<?php endif ?>

<?php if ($expiringSoon): ?>
<div class="alert alert-warning py-2">
    <strong><i class="bi bi-clock-fill"></i> Expiring soon</strong>
    <ul class="mb-0 mt-1 small">
        <?php foreach ($expiringSoon as $e): ?>
        <li><?= e($e['item_name']) ?> — <?= e($e['room_name']) ?> (<?= e($e['expiry_date']) ?>)</li>
        <?php endforeach ?>
    </ul>
</div>
<?php endif ?>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0 text-muted">Rooms</h6>
    <a href="<?= APP_URL ?>/rooms/create" class="btn btn-sm btn-outline-dark">
        <i class="bi bi-plus"></i> Add Room
    </a>
</div>

<?php if (empty($rooms)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-door-open display-4"></i>
    <p class="mt-2">No rooms yet. <a href="<?= APP_URL ?>/rooms/create">Add your first room.</a></p>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($rooms as $room): ?>
    <div class="col-6">
        <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="text-decoration-none">
            <div class="card location-card h-100 text-center p-3">
                <i class="bi bi-door-open-fill display-5 text-secondary"></i>
                <div class="fw-semibold mt-1"><?= e($room['name']) ?></div>
                <div class="text-muted small">
                    <?= $room['container_count'] ?> containers<br>
                    <?= $room['item_count'] ?> items
                </div>
            </div>
        </a>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>
