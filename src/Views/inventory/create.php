<div class="d-flex align-items-center mb-3 gap-2">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">Add Item</h5>
</div>

<!-- Barcode scan shortcut -->
<div class="card bg-success text-white mb-3" id="scan-card" style="cursor:pointer" onclick="startProductScan()">
    <div class="card-body d-flex align-items-center gap-3 py-2">
        <i class="bi bi-upc-scan fs-3"></i>
        <div>
            <div class="fw-semibold">Scan product barcode</div>
            <div class="small opacity-75">Auto-fill item name</div>
        </div>
    </div>
</div>
<div id="product-scanner" class="mb-3" style="display:none">
    <div id="product-qr-reader" style="width:100%"></div>
    <button class="btn btn-outline-secondary btn-sm mt-2" onclick="stopProductScan()">Cancel</button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/inventory/store" id="inv-form">
            <input type="hidden" name="barcode" id="barcode-field">

            <div class="mb-3">
                <label class="form-label">Item Name</label>
                <input type="text" name="item_name" id="item-name" class="form-control form-control-lg"
                       value="<?= e($preItemName) ?>" placeholder="e.g. Aata, Basmati Rice" required autofocus list="items-list">
                <datalist id="items-list">
                    <?php foreach ($items as $it): ?>
                    <option value="<?= e($it['name']) ?>">
                    <?php endforeach ?>
                </datalist>
            </div>

            <div class="mb-3">
                <label class="form-label">Room</label>
                <select name="room_id" id="room-select" class="form-select form-select-lg" required
                        onchange="filterContainers(this.value)">
                    <option value="">— Select room —</option>
                    <?php foreach ($rooms as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= $r['id'] == $preRoomId ? 'selected' : '' ?>>
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
                        <?= $c['id'] == $preContainerId ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity (grams)</label>
                <div class="input-group input-group-lg">
                    <input type="number" name="quantity_grams" class="form-control" min="1" required placeholder="500">
                    <span class="input-group-text">g</span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Arrived</label>
                <input type="date" name="arrival_date" class="form-control form-control-lg"
                       value="<?= date('Y-m-d') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Expiry date <span class="text-muted small">(optional)</span></label>
                <input type="date" name="expiry_date" class="form-control form-control-lg">
            </div>

            <div class="mb-4">
                <label class="form-label">Notes <span class="text-muted small">(optional)</span></label>
                <input type="text" name="notes" class="form-control" placeholder="Brand, variety...">
            </div>

            <button class="btn btn-dark btn-lg w-100">Add to Inventory</button>
        </form>
    </div>
</div>

<script>
// Filter containers by selected room
function filterContainers(roomId) {
    const sel = document.getElementById('container-select');
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        opt.hidden = opt.dataset.room !== roomId;
    });
    sel.value = '';
}
document.addEventListener('DOMContentLoaded', () => {
    const roomSel = document.getElementById('room-select');
    if (roomSel.value) filterContainers(roomSel.value);
});

// Product barcode scanner
let productScanner = null;
function startProductScan() {
    document.getElementById('scan-card').style.display = 'none';
    document.getElementById('product-scanner').style.display = 'block';
    productScanner = new Html5Qrcode('product-qr-reader');
    productScanner.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: 250 },
        (code) => {
            stopProductScan();
            lookupBarcode(code);
        }
    ).catch(() => stopProductScan());
}
function stopProductScan() {
    if (productScanner) { productScanner.stop().catch(()=>{}); productScanner = null; }
    document.getElementById('scan-card').style.display = 'block';
    document.getElementById('product-scanner').style.display = 'none';
}
function lookupBarcode(code) {
    document.getElementById('barcode-field').value = code;
    fetch(`<?= APP_URL ?>/scan/product?barcode=${encodeURIComponent(code)}`)
        .then(r => r.json())
        .then(data => {
            if (data.name) document.getElementById('item-name').value = data.name;
        });
}
</script>
