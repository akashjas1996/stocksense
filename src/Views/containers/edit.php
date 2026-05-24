<div class="d-flex align-items-center mb-3 gap-2">
    <a href="<?= APP_URL ?>/containers/<?= $container['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">Edit Container</h5>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/containers/<?= $container['id'] ?>/update">
            <div class="mb-3">
                <label class="form-label">Room</label>
                <select name="room_id" class="form-select form-select-lg" required>
                    <?php foreach ($rooms as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $r['id'] == $container['room_id'] ? 'selected' : '' ?>>
                        <?= e($r['name']) ?>
                    </option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control form-control-lg"
                       value="<?= e($container['name']) ?>" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Type</label>
                <select name="type" class="form-select form-select-lg">
                    <?php foreach (['fridge','freezer','shelf','cabinet','drawer','basket','other'] as $t): ?>
                    <option value="<?= $t ?>" <?= $container['type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <button class="btn btn-dark btn-lg w-100">Save</button>
        </form>
    </div>
</div>

<form method="POST" action="<?= APP_URL ?>/containers/<?= $container['id'] ?>/delete"
      onsubmit="return confirm('Delete this container and all its inventory entries?')">
    <button class="btn btn-outline-danger w-100 mt-3">Delete Container</button>
</form>
