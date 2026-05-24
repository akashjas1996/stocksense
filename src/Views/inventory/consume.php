<div class="d-flex align-items-center mb-3 gap-2">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">Use Item</h5>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4 text-center">
        <h4 class="fw-bold"><?= e($item['name']) ?></h4>
        <p class="text-muted">Available: <strong><?= formatWeight($entry['quantity_grams']) ?></strong></p>

        <form method="POST" action="<?= APP_URL ?>/inventory/<?= $entry['id'] ?>/consume">
            <div class="input-group input-group-lg mb-4 mt-3">
                <input type="number" name="use_grams" class="form-control text-center"
                       min="1" max="<?= $entry['quantity_grams'] ?>" required
                       placeholder="Amount used">
                <span class="input-group-text">g</span>
            </div>

            <!-- Quick-pick buttons -->
            <div class="d-flex gap-2 justify-content-center mb-4 flex-wrap">
                <?php foreach ([100, 250, 500] as $q): ?>
                <?php if ($q <= $entry['quantity_grams']): ?>
                <button type="button" class="btn btn-outline-secondary"
                        onclick="document.querySelector('[name=use_grams]').value=<?= $q ?>">
                    <?= formatWeight($q) ?>
                </button>
                <?php endif ?>
                <?php endforeach ?>
                <button type="button" class="btn btn-outline-danger"
                        onclick="document.querySelector('[name=use_grams]').value=<?= $entry['quantity_grams'] ?>">
                    All
                </button>
            </div>

            <button class="btn btn-success btn-lg w-100">Confirm Use</button>
        </form>
    </div>
</div>
