<div class="page-head">
    <a href="javascript:history.back()" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1>Edit Item</h1>
</div>

<?php
$item = db()->prepare('SELECT * FROM items WHERE id = ?');
$item->execute([$entry['item_id']]);
$item = $item->fetch();
?>

<div class="card" style="padding:20px;margin-bottom:12px;">
    <form method="POST" action="<?= APP_URL ?>/inventory/<?= $entry['id'] ?>/update">

        <div class="form-group">
            <label>Item</label>
            <input type="text" class="form-control" value="<?= e($item['name']) ?>" disabled
                   style="opacity:.6;background:var(--border-s);">
        </div>

        <div class="form-group">
            <label>Room</label>
            <select name="room_id" id="room-select" class="form-control" required
                    onchange="filterContainers(this.value)">
                <?php foreach ($rooms as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $r['id'] == $entry['room_id'] ? 'selected' : '' ?>>
                    <?= e($r['name']) ?>
                </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="form-group">
            <label>Container <span style="font-weight:500;color:var(--text-3)">(optional)</span></label>
            <select name="container_id" id="container-select" class="form-control">
                <option value="">— None (loose in room) —</option>
                <?php foreach ($containers as $c): ?>
                <option value="<?= $c['id'] ?>" data-room="<?= $c['room_id'] ?>"
                    <?= $c['id'] == $entry['container_id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="form-group">
            <label>Quantity (grams)</label>
            <div class="input-row">
                <input type="number" name="quantity_grams" class="form-control" min="1" required
                       value="<?= $entry['quantity_grams'] ?>">
                <div class="input-unit">g</div>
            </div>
        </div>

        <div class="form-group">
            <label>Expiry date</label>
            <input type="date" name="expiry_date" class="form-control"
                   value="<?= e($entry['expiry_date'] ?? '') ?>">
        </div>

        <div class="form-group" style="margin-bottom:24px;">
            <label>Notes</label>
            <input type="text" name="notes" class="form-control"
                   value="<?= e($entry['notes'] ?? '') ?>">
        </div>

        <button type="submit" class="btn-accent">Save Changes</button>
    </form>
</div>

<form method="POST" action="<?= APP_URL ?>/inventory/<?= $entry['id'] ?>/delete"
      onsubmit="return confirm('Remove this item from inventory?')">
    <button type="submit" style="width:100%;padding:13px;background:none;border:1.5px solid var(--danger);border-radius:var(--r);color:var(--danger);font-weight:700;font-size:.9rem;cursor:pointer;">
        <i class="bi bi-trash"></i> Remove Item
    </button>
</form>

<script>
function filterContainers(roomId) {
    const sel = document.getElementById('container-select');
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        opt.hidden = opt.dataset.room !== roomId;
    });
}
document.addEventListener('DOMContentLoaded', () => filterContainers(document.getElementById('room-select').value));
</script>
