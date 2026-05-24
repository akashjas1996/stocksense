<div class="page-head">
    <h1>Rooms</h1>
    <?php if (isLoggedIn()): ?>
    <a href="<?= APP_URL ?>/rooms/create" class="btn-accent" style="padding:9px 16px;text-decoration:none;font-size:.85rem;">
        <i class="bi bi-plus"></i> Add
    </a>
    <?php endif ?>
</div>

<?php if (empty($rooms)): ?>
<div class="empty-state"><i class="bi bi-door-open"></i><p>No rooms yet.</p></div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:10px;">
    <?php foreach ($rooms as $room): ?>
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="loc-card" style="text-decoration:none;">
        <div class="loc-icon"><?= roomEmoji($room['name']) ?></div>
        <div class="loc-info">
            <div class="loc-name"><?= e($room['name']) ?></div>
            <div class="loc-meta"><?= $room['container_count'] ?> containers &middot; <?= formatWeight($room['total_grams']) ?> total</div>
        </div>
        <i class="bi bi-chevron-right" style="color:var(--text-3);font-size:.85rem;"></i>
    </a>
    <?php endforeach ?>
</div>
<?php endif ?>
