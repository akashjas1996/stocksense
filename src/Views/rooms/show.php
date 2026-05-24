<?php
$hasIssues = false;
foreach (array_merge($looseItems, ...(array_map(fn($c) => [], $containers))) as $inv) {
    if (expiryStatus($inv['expiry_date'] ?? null)) { $hasIssues = true; break; }
}
?>
<div class="page-head">
    <a href="<?= APP_URL ?>/rooms" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1><?= e($room['name']) ?></h1>
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>/qr"   class="icon-btn" title="QR Code"><i class="bi bi-qr-code"></i></a>
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>/edit" class="icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
</div>

<!-- Containers -->
<div class="sec-head">
    <span class="sec-label">Containers (<?= count($containers) ?>)</span>
    <a href="<?= APP_URL ?>/containers/create?room_id=<?= $room['id'] ?>" class="btn-outline" style="padding:6px 12px;font-size:.78rem;">
        <i class="bi bi-plus"></i> Add
    </a>
</div>

<?php if (empty($containers)): ?>
<div class="empty-state" style="padding:24px 0;">
    <i class="bi bi-box-seam" style="font-size:2rem;"></i>
    <p>No containers yet.</p>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
    <?php foreach ($containers as $c):
        $statusColor = '';
        // We'll do a quick expiry check via the DB query's total — skip for now, just show stats
    ?>
    <a href="<?= APP_URL ?>/containers/<?= $c['id'] ?>" class="loc-card">
        <div class="loc-icon" style="font-size:1.6rem"><?= containerIcon($c['type']) ?></div>
        <div class="loc-info">
            <div class="loc-name"><?= e($c['name']) ?></div>
            <div class="loc-meta">
                <span style="text-transform:capitalize"><?= e($c['type']) ?></span>
                &middot; <?= $c['item_count'] ?> items
                &middot; <?= formatWeight((int)$c['total_grams']) ?>
            </div>
        </div>
        <i class="bi bi-chevron-right loc-chevron"></i>
    </a>
    <?php endforeach ?>
</div>
<?php endif ?>

<!-- Loose items -->
<div class="sec-head">
    <span class="sec-label">Loose items (<?= count($looseItems) ?>)</span>
    <a href="<?= APP_URL ?>/inventory/create?room_id=<?= $room['id'] ?>" class="btn-outline" style="padding:6px 12px;font-size:.78rem;">
        <i class="bi bi-plus"></i> Add
    </a>
</div>

<?php if (empty($looseItems)): ?>
<div class="empty-state" style="padding:20px 0;">
    <p style="color:var(--text-3);font-size:.85rem;font-weight:600;">No loose items in this room.</p>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:8px;">
    <?php foreach ($looseItems as $inv):
        $status = expiryStatus($inv['expiry_date']);
        $isLow  = $inv['quantity_grams'] < 500;
        $item   = db()->prepare('SELECT name_en, image_url FROM items WHERE id = ?');
        $item->execute([$inv['item_id']]); $item = $item->fetch();
    ?>
    <div class="item-card <?= $status === 'expired' ? 'dead' : ($isLow ? 'low' : '') ?>">
        <div class="item-thumb">
            <?php if (!empty($item['image_url'])): ?>
            <img src="<?= e($item['image_url']) ?>" alt="">
            <?php else: ?>
            <?= itemEmoji($inv['item_name'], $item['name_en'] ?? '') ?>
            <?php endif ?>
        </div>
        <div class="item-info">
            <div class="item-name"><?= e($inv['item_name']) ?></div>
            <div style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;margin-top:4px;">
                <span class="qty-pill <?= $isLow ? 'low' : '' ?>"><?= formatWeight($inv['quantity_grams']) ?></span>
                <?php if ($status): ?><span class="tag <?= $status === 'expired' ? 'expired' : 'soon' ?>"><?= $status === 'expired' ? 'Expired' : 'Exp ' . e($inv['expiry_date']) ?></span><?php endif ?>
                <?php if ($inv['notes']): ?><span style="font-size:.72rem;color:var(--text-3)"><?= e($inv['notes']) ?></span><?php endif ?>
            </div>
        </div>
        <div class="item-actions">
            <a href="<?= APP_URL ?>/inventory/<?= $inv['id'] ?>/consume" class="icon-btn" title="Use"><i class="bi bi-dash-circle"></i></a>
            <a href="<?= APP_URL ?>/inventory/<?= $inv['id'] ?>/edit"    class="icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
        </div>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>
