<?php $isRoom = $location['type'] === 'room'; ?>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <div style="width:56px;height:56px;border-radius:16px;background:var(--accent-l);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;">
        <?= $isRoom ? roomEmoji($location['name']) : containerIcon($location['type'] ?? 'other') ?>
    </div>
    <div>
        <h1 style="margin:0;font-size:1.2rem;font-weight:900;"><?= e($location['name']) ?></h1>
        <?php if (!$isRoom && isset($location['room_name'])): ?>
        <div style="font-size:.8rem;color:var(--text-3);font-weight:600;">in <?= e($location['room_name']) ?></div>
        <?php endif ?>
    </div>
</div>

<?php if ($expired): ?>
<div class="alert-strip danger" style="margin-bottom:8px;">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span><?= count($expired) ?> item<?= count($expired) > 1 ? 's' : '' ?> expired</span>
</div>
<?php endif ?>
<?php if ($expiringSoon): ?>
<div class="alert-strip warn" style="margin-bottom:8px;">
    <i class="bi bi-clock-fill"></i>
    <span><?= count($expiringSoon) ?> expiring within <?= EXPIRY_WARN_DAYS ?> days</span>
</div>
<?php endif ?>
<?php if ($lowStock): ?>
<div class="alert-strip" style="background:#F5F5F4;color:var(--text-2);margin-bottom:12px;">
    <i class="bi bi-battery-half"></i>
    <span><?= count($lowStock) ?> item<?= count($lowStock) > 1 ? 's' : '' ?> running low</span>
</div>
<?php endif ?>

<?php if (empty($items)): ?>
<div class="empty-state"><i class="bi bi-inbox"></i><p>Nothing stored here.</p></div>
<?php else: ?>

<?php
$grouped = [];
foreach ($items as $inv) {
    $key = $inv['container_name'] ?? '__loose__';
    $grouped[$key][] = $inv;
}
?>

<?php foreach ($grouped as $groupName => $groupItems): ?>
<?php if ($isRoom && count($grouped) > 1): ?>
<div class="sec-label" style="margin-top:16px;margin-bottom:6px;">
    <?= $groupName === '__loose__' ? 'No container' : e($groupName) ?>
</div>
<?php endif ?>

<div style="display:flex;flex-direction:column;gap:8px;margin-bottom:8px;">
    <?php foreach ($groupItems as $inv):
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
                <?php if ($inv['notes']): ?><span style="font-size:.72rem;color:var(--text-3)"><?= e($inv['notes']) ?></span><?php endif ?>
            </div>
        </div>
        <?php if (isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/inventory/<?= $inv['id'] ?>/consume" class="icon-btn" title="Use">
            <i class="bi bi-dash-circle"></i>
        </a>
        <?php endif ?>
    </div>
    <?php endforeach ?>
</div>
<?php endforeach ?>

<div style="text-align:center;font-size:.78rem;color:var(--text-3);font-weight:600;margin-top:8px;">
    <?= count($items) ?> items &middot; <?= formatWeight(array_sum(array_column($items, 'quantity_grams'))) ?> total
</div>
<?php endif ?>

<?php if (isLoggedIn()): ?>
<div style="margin-top:20px;">
    <?php
    $addUrl = $isRoom
        ? APP_URL . '/inventory/create?room_id=' . $location['id']
        : APP_URL . '/inventory/create?room_id=' . $location['room_id'] . '&container_id=' . $location['id'];
    ?>
    <a href="<?= $addUrl ?>" class="btn-accent" style="display:block;text-align:center;text-decoration:none;padding:13px;">
        <i class="bi bi-plus-circle"></i> Add item here
    </a>
</div>
<?php endif ?>
