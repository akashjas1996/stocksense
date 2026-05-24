<div class="page-head">
    <a href="javascript:history.back()" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1>Add Container</h1>
</div>

<div class="card" style="padding:20px;">
    <form method="POST" action="<?= APP_URL ?>/containers/store">
        <div class="form-group">
            <label>Room</label>
            <select name="room_id" class="form-control" required>
                <option value="">— Select room —</option>
                <?php foreach ($rooms as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $r['id'] == $roomId ? 'selected' : '' ?>>
                    <?= e($r['name']) ?>
                </option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="form-group">
            <label>Container Name</label>
            <input type="text" name="name" class="form-control" required autofocus
                   placeholder="e.g. Main Fridge, Top Shelf">
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control">
                <?php foreach (['fridge','freezer','shelf','cabinet','drawer','basket','other'] as $t): ?>
                <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <button type="submit" class="btn-accent">Create Container</button>
    </form>
</div>
