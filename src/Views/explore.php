<!-- Top summary banner -->
<div class="mall-banner">
    <div class="mall-banner-inner">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--accent);margin-bottom:2px;">Your Pantry</div>
        <div style="font-size:1.6rem;font-weight:900;line-height:1.1;color:var(--text);">
            <?= $totalItems ?> items
        </div>
        <div style="font-size:.85rem;color:var(--text-2);margin-top:4px;font-weight:600;">
            <?= formatWeight((int)$totalWeight) ?> across <?= $roomCount ?> room<?= $roomCount !== 1 ? 's' : '' ?>
        </div>
    </div>
    <div style="font-size:3.5rem;line-height:1;">🏪</div>
</div>

<?php if (empty($mall)): ?>
<div class="empty-state"><i class="bi bi-shop"></i><p>Nothing in stock yet.</p></div>
<?php endif ?>

<?php foreach ($mall as $roomId => $room): ?>
<?php
    $roomTotal = 0;
    $roomItems = 0;
    foreach ($room['sections'] as $sec) {
        foreach ($sec['items'] as $inv) {
            $roomTotal += $inv['quantity_grams'];
            $roomItems++;
        }
    }
?>

<!-- Room / Department -->
<div class="mall-dept">
    <div class="mall-dept-head">
        <div class="mall-dept-icon"><?= roomEmoji($room['name']) ?></div>
        <div>
            <div class="mall-dept-name"><?= e($room['name']) ?></div>
            <div class="mall-dept-meta"><?= $roomItems ?> items &middot; <?= formatWeight($roomTotal) ?></div>
        </div>
        <a href="<?= APP_URL ?>/rooms/<?= $roomId ?>" class="mall-dept-link">
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <?php if (empty($room['sections'])): ?>
    <div style="padding:12px 0 4px;font-size:.82rem;color:var(--text-3);font-weight:600;">Empty room.</div>

    <?php else: ?>
    <?php foreach ($room['sections'] as $skey => $section): ?>

    <!-- Aisle / Container -->
    <div class="mall-aisle">
        <?php if ($skey !== '__loose__'): ?>
        <div class="mall-aisle-label">
            <span><?= containerIcon($section['type'] ?? 'other') ?></span>
            <span><?= e($section['name']) ?></span>
        </div>
        <?php else: ?>
        <div class="mall-aisle-label" style="color:var(--text-3);">
            <span>📦</span><span>Loose items</span>
        </div>
        <?php endif ?>

        <!-- Horizontal product shelf -->
        <div class="mall-shelf">
            <?php foreach ($section['items'] as $inv):
                $status = expiryStatus($inv['expiry_date']);
                $grams  = (int)$inv['quantity_grams'];
                $fill   = min(round($grams / 5000 * 100), 100);
                $fillColor = $status === 'expired' ? 'var(--danger)'
                           : ($status === 'soon'   ? 'var(--warn)'
                           : ($grams < 200          ? '#F59E0B'
                           :                         'var(--good)'));
            ?>
            <a href="<?= APP_URL ?>/inventory/<?= $inv['inv_id'] ?>/edit" class="mall-card <?= $status === 'expired' ? 'dead' : ($status === 'soon' ? 'soon' : '') ?>">
                <div class="mall-card-img">
                    <?php if (!empty($inv['image_url'])): ?>
                    <img src="<?= e($inv['image_url']) ?>" alt="">
                    <?php else: ?>
                    <?= itemEmoji($inv['item_name'], $inv['name_en'] ?? '') ?>
                    <?php endif ?>
                </div>
                <div class="mall-card-name"><?= e($inv['item_name']) ?></div>
                <?php if (!empty($inv['name_en'])): ?>
                <div class="mall-card-sub"><?= e($inv['name_en']) ?></div>
                <?php endif ?>
                <div class="mall-card-qty"><?= formatWeight($grams) ?></div>
                <div class="mall-fill-wrap">
                    <div class="mall-fill-bar" style="width:<?= $fill ?>%;background:<?= $fillColor ?>;"></div>
                </div>
                <?php if ($status === 'expired'): ?>
                <div class="mall-card-tag expired">Expired</div>
                <?php elseif ($status === 'soon'): ?>
                <div class="mall-card-tag soon">Exp <?= e($inv['expiry_date']) ?></div>
                <?php endif ?>
            </a>
            <?php endforeach ?>

            <!-- Add item shortcut at end of shelf -->
            <?php
            $addUrl = APP_URL . '/inventory/create?room_id=' . $roomId;
            if ($skey !== '__loose__') $addUrl .= '&container_id=' . $skey;
            ?>
            <a href="<?= $addUrl ?>" class="mall-card-add">
                <i class="bi bi-plus-circle" style="font-size:1.4rem;color:var(--accent);"></i>
                <span>Add</span>
            </a>
        </div>
    </div>
    <?php endforeach ?>
    <?php endif ?>
</div>
<?php endforeach ?>

<div style="text-align:center;padding:32px 0 8px;font-size:.78rem;color:var(--text-3);font-weight:600;">
    End of pantry &middot; <?= $totalItems ?> items total
</div>
