<div class="d-flex align-items-center mb-3 gap-2">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">Edit Item</h5>
</div>

<?php
$item = db()->prepare('SELECT * FROM items WHERE id = ?');
$item->execute([$entry['item_id']]);
$item = $item->fetch();
?>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/inventory/<?= $entry['id'] ?>/update">

            <div class="mb-3">
                <label class="form-label">Item</label>
                <input type="text" class="form-control form-control-lg" value="<?= e($item['name']) ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Room</label>
                <select name="room_id" id="room-select" class="form-select form-select-lg" required
                        onchange="filterContainers(this.value)">
                    <?php foreach ($rooms as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $r['id'] == $entry['room_id'] ? 'selected' : '' ?>>
                        <?= e($r['name']) ?>
                    </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Container <span class="text-muted small">(optional)</span></label>
                <select name="container_id" id="container-select" class="form-select form-select-lg">
                    <option value="">— None (loose in room) —</option>
                    <?php foreach ($containers as $c): ?>
                    <option value="<?= $c['id'] ?>" data-room="<?= $c['room_id'] ?>"
                        <?= $c['id'] == $entry['container_id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity (grams)</label>
                <div class="input-group input-group-lg">
                    <input type="number" name="quantity_grams" class="form-control" min="1" required
                           value="<?= $entry['quantity_grams'] ?>">
                    <span class="input-group-text">g</span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Expiry date</label>
                <input type="date" name="expiry_date" class="form-control form-control-lg"
                       value="<?= e($entry['expiry_date'] ?? '') ?>">
            </div>

            <div class="mb-4">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control"
                       value="<?= e($entry['notes'] ?? '') ?>">
            </div>

            <button class="btn btn-dark btn-lg w-100">Save Changes</button>
        </form>
    </div>
</div>

<form method="POST" action="<?= APP_URL ?>/inventory/<?= $entry['id'] ?>/delete"
      onsubmit="return confirm('Remove this item from inventory?')">
    <button class="btn btn-outline-danger w-100 mt-3">Remove Item</button>
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
