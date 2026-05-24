<div class="page-head">
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1><?= e($container['name']) ?></h1>
    <a href="<?= APP_URL ?>/containers/<?= $container['id'] ?>/qr"   class="icon-btn"><i class="bi bi-qr-code"></i></a>
    <a href="<?= APP_URL ?>/containers/<?= $container['id'] ?>/edit" class="icon-btn"><i class="bi bi-pencil"></i></a>
</div>

<div style="font-size:.8rem;color:var(--text-3);font-weight:600;margin-bottom:16px;">
    <?= containerIcon($container['type']) ?>
    <span style="text-transform:capitalize"><?= e($container['type']) ?></span>
    &middot; in <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" style="color:var(--accent)"><?= e($room['name']) ?></a>
</div>

<div class="sec-head">
    <span class="sec-label">Items (<?= count($items) ?>)</span>
    <a href="<?= APP_URL ?>/inventory/create?room_id=<?= $container['room_id'] ?>&container_id=<?= $container['id'] ?>"
       class="btn-outline" style="padding:6px 12px;font-size:.78rem;">
        <i class="bi bi-plus"></i> Add
    </a>
</div>

<?php if (empty($items)): ?>
<div class="empty-state">
    <i class="bi bi-inbox"></i>
    <p>Nothing stored here yet.</p>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:8px;">
    <?php foreach ($items as $inv):
        $status = expiryStatus($inv['expiry_date']);
        $isLow  = $inv['quantity_grams'] < 500;
        $meta   = db()->prepare('SELECT name_en, image_url FROM items WHERE id = ?');
        $meta->execute([$inv['item_id']]); $meta = $meta->fetch();
    ?>
    <div class="item-card <?= $status === 'expired' ? 'dead' : ($isLow ? 'low' : '') ?>">
        <div class="item-thumb">
            <?php if (!empty($meta['image_url'])): ?>
            <img src="<?= e($meta['image_url']) ?>" alt="">
            <?php else: ?>
            <?= itemEmoji($inv['item_name'], $meta['name_en'] ?? '') ?>
            <?php endif ?>
        </div>
        <div class="item-info">
            <div class="item-name"><?= e($inv['item_name']) ?></div>
            <?php if (!empty($meta['name_en'])): ?>
            <div class="item-sub"><?= e($meta['name_en']) ?></div>
            <?php endif ?>
            <div style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;margin-top:5px;">
                <span class="qty-pill <?= $isLow ? 'low' : '' ?>"><?= formatWeight($inv['quantity_grams']) ?></span>
                <?php if ($status): ?><span class="tag <?= $status === 'expired' ? 'expired' : 'soon' ?>"><?= $status === 'expired' ? 'Expired' : 'Exp ' . e($inv['expiry_date']) ?></span><?php endif ?>
                <?php if ($isLow && !$status): ?><span class="tag" style="background:var(--warn-l);color:var(--accent-d)">Low</span><?php endif ?>
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

<div style="text-align:center;margin-top:16px;font-size:.78rem;color:var(--text-3);font-weight:600;">
    <?= count($items) ?> items &middot; <?= formatWeight(array_sum(array_column($items, 'quantity_grams'))) ?> total
</div>
<?php endif ?>
