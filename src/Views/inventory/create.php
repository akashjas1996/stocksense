<div class="page-head">
    <a href="javascript:history.back()" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1>Add Item</h1>
</div>

<!-- Barcode scan shortcut -->
<div id="scan-card" style="background:var(--accent-l);border:1.5px solid var(--border);border-radius:var(--r);padding:14px 16px;display:flex;align-items:center;gap:12px;margin-bottom:14px;cursor:pointer;" onclick="startProductScan()">
    <i class="bi bi-upc-scan" style="font-size:1.6rem;color:var(--accent);flex-shrink:0;"></i>
    <div>
        <div style="font-weight:700;color:var(--text);">Scan product barcode</div>
        <div style="font-size:.8rem;color:var(--text-2);">Auto-fill item name</div>
    </div>
</div>
<div id="product-scanner" style="display:none;margin-bottom:14px;">
    <div style="border-radius:var(--r);overflow:hidden;">
        <div id="product-qr-reader" style="width:100%"></div>
    </div>
    <button type="button" class="btn-outline" style="margin-top:8px;width:100%;" onclick="stopProductScan()">Cancel</button>
</div>

<div class="card" style="padding:20px;">
    <form method="POST" action="<?= APP_URL ?>/inventory/store" id="inv-form">
        <input type="hidden" name="barcode" id="barcode-field">

        <div class="form-group">
            <label>Item Name</label>
            <input type="text" name="item_name" id="item-name" class="form-control"
                   value="<?= e($preItemName) ?>" placeholder="e.g. Aata, Basmati Rice" required autofocus list="items-list">
            <datalist id="items-list">
                <?php foreach ($items as $it): ?>
                <option value="<?= e($it['name']) ?>">
                <?php endforeach ?>
            </datalist>
        </div>

        <div class="form-group">
            <label>Room</label>
            <select name="room_id" id="room-select" class="form-control" required
                    onchange="filterContainers(this.value)">
                <option value="">— Select room —</option>
                <?php foreach ($rooms as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $r['id'] == $preRoomId ? 'selected' : '' ?>>
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
                    <?= $c['id'] == $preContainerId ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="form-group">
            <label>Quantity (grams)</label>
            <div class="input-row">
                <input type="number" name="quantity_grams" class="form-control" min="1" required placeholder="500">
                <div class="input-unit">g</div>
            </div>
        </div>

        <div class="form-group">
            <label>Arrived</label>
            <input type="date" name="arrival_date" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
            <label>Expiry date <span style="font-weight:500;color:var(--text-3)">(optional)</span></label>
            <input type="date" name="expiry_date" class="form-control">
        </div>

        <div class="form-group" style="margin-bottom:24px;">
            <label>Notes <span style="font-weight:500;color:var(--text-3)">(optional)</span></label>
            <input type="text" name="notes" class="form-control" placeholder="Brand, variety…">
        </div>

        <button type="submit" class="btn-accent">Add to Inventory</button>
    </form>
</div>

<script>
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

let productScanner = null;
function startProductScan() {
    document.getElementById('scan-card').style.display = 'none';
    document.getElementById('product-scanner').style.display = 'block';
    productScanner = new Html5Qrcode('product-qr-reader');
    productScanner.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: 250 },
        (code) => { stopProductScan(); lookupBarcode(code); }
    ).catch(() => stopProductScan());
}
function stopProductScan() {
    if (productScanner) { productScanner.stop().catch(()=>{}); productScanner = null; }
    document.getElementById('scan-card').style.display = 'flex';
    document.getElementById('product-scanner').style.display = 'none';
}
function lookupBarcode(code) {
    document.getElementById('barcode-field').value = code;
    fetch(`<?= APP_URL ?>/scan/product?barcode=${encodeURIComponent(code)}`)
        .then(r => r.json())
        .then(data => { if (data.name) document.getElementById('item-name').value = data.name; });
}
</script>
