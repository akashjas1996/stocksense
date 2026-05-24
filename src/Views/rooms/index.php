<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Rooms</h5>
    <a href="<?= APP_URL ?>/rooms/create" class="btn btn-dark btn-sm">
        <i class="bi bi-plus"></i> Add Room
    </a>
</div>

<?php if (empty($rooms)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-door-open display-4"></i>
    <p class="mt-2">No rooms yet.</p>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-2">
    <?php foreach ($rooms as $room): ?>
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="text-decoration-none">
        <div class="card location-card">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-door-open-fill fs-3 text-secondary me-3"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold"><?= e($room['name']) ?></div>
                    <div class="text-muted small">
                        <?= $room['container_count'] ?> containers &middot;
                        <?= formatWeight($room['total_grams']) ?> total
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </div>
        </div>
    </a>
    <?php endforeach ?>
</div>
<?php endif ?>
