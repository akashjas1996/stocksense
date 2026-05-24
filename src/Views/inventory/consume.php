<div class="page-head">
    <a href="javascript:history.back()" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1>Use item</h1>
</div>

<div class="card" style="text-align:center;padding:28px 20px;margin-bottom:20px;">
    <div style="font-size:3rem;margin-bottom:8px;"><?= itemEmoji($item['name']) ?></div>
    <div style="font-weight:900;font-size:1.2rem;"><?= e($item['name']) ?></div>
    <div style="color:var(--text-3);font-size:.85rem;margin-top:4px;">Available</div>
    <div style="font-size:2rem;font-weight:900;color:var(--accent);margin-top:4px;">
        <?= formatWeight($entry['quantity_grams']) ?>
    </div>
</div>

<form method="POST" action="<?= APP_URL ?>/inventory/<?= $entry['id'] ?>/consume">
    <div class="form-group">
        <label>Amount used</label>
        <div class="input-row">
            <input type="number" name="use_grams" id="use_grams" class="form-control"
                   min="1" max="<?= $entry['quantity_grams'] ?>" required placeholder="Enter grams">
            <div class="input-unit">g</div>
        </div>
    </div>

    <div class="quick-btns" style="margin-bottom:24px;">
        <?php foreach ([50, 100, 250, 500] as $q): ?>
        <?php if ($q <= $entry['quantity_grams']): ?>
        <button type="button" class="quick-btn"
                onclick="document.getElementById('use_grams').value=<?= $q ?>">
            <?= formatWeight($q) ?>
        </button>
        <?php endif ?>
        <?php endforeach ?>
        <button type="button" class="quick-btn all-btn"
                onclick="document.getElementById('use_grams').value=<?= $entry['quantity_grams'] ?>">
            All
        </button>
    </div>

    <button type="submit" class="btn-accent">Confirm</button>
</form>
