<div class="page-head">
    <a href="<?= APP_URL ?>/containers/<?= $container['id'] ?>" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1>Edit Container</h1>
</div>

<div class="card" style="padding:20px;margin-bottom:12px;">
    <form method="POST" action="<?= APP_URL ?>/containers/<?= $container['id'] ?>/update">
        <div class="form-group">
            <label>Room</label>
            <select name="room_id" class="form-control" required>
                <?php foreach ($rooms as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $r['id'] == $container['room_id'] ? 'selected' : '' ?>>
                    <?= e($r['name']) ?>
                </option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required
                   value="<?= e($container['name']) ?>">
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control">
                <?php foreach (['fridge','freezer','shelf','cabinet','drawer','basket','other'] as $t): ?>
                <option value="<?= $t ?>" <?= $container['type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <a href="<?= APP_URL ?>/containers/<?= $container['id'] ?>/qr" class="btn-outline" style="font-size:.82rem;padding:8px 14px;text-decoration:none;display:inline-block;">
                <i class="bi bi-qr-code"></i> QR Code
            </a>
        </div>
        <button type="submit" class="btn-accent">Save</button>
    </form>
</div>

<form method="POST" action="<?= APP_URL ?>/containers/<?= $container['id'] ?>/delete"
      onsubmit="return confirm('Delete this container and all its inventory entries?')">
    <button type="submit" style="width:100%;padding:13px;background:none;border:1.5px solid var(--danger);border-radius:var(--r);color:var(--danger);font-weight:700;font-size:.9rem;cursor:pointer;">
        <i class="bi bi-trash"></i> Delete Container
    </button>
</form>
