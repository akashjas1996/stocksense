<div class="page-head">
    <h1>Scan</h1>
</div>

<div style="font-size:.85rem;color:var(--text-2);margin-bottom:14px;text-align:center;">
    Point at a <strong>room/container QR</strong> to view contents, or a <strong>product barcode</strong> to add an item.
</div>

<div class="card" style="overflow:hidden;margin-bottom:14px;padding:0;">
    <div id="qr-reader" style="width:100%;"></div>
    <div id="scan-result" style="padding:12px 16px;text-align:center;font-size:.85rem;color:var(--text-2);min-height:40px;"></div>
</div>

<div style="display:flex;gap:8px;">
    <button id="btn-location" class="btn-accent" style="flex:1;" onclick="setMode('location')">
        <i class="bi bi-qr-code"></i> Location
    </button>
    <button id="btn-product" class="btn-outline" style="flex:1;" onclick="setMode('product')">
        <i class="bi bi-upc-scan"></i> Product
    </button>
</div>
<div id="mode-hint" style="text-align:center;font-size:.8rem;color:var(--text-3);margin-top:8px;font-weight:600;">
    Scanning for room / container QR code
</div>

<script>
let scanner = null;
let currentMode = 'location';

function setMode(mode) {
    currentMode = mode;
    const isLoc = mode === 'location';
    document.getElementById('btn-location').className = isLoc ? 'btn-accent' : 'btn-outline';
    document.getElementById('btn-location').style.flex = '1';
    document.getElementById('btn-product').className = isLoc ? 'btn-outline' : 'btn-accent';
    document.getElementById('btn-product').style.flex = '1';
    document.getElementById('mode-hint').textContent = isLoc
        ? 'Scanning for room / container QR code'
        : 'Scanning product barcode to add item';
    document.getElementById('scan-result').textContent = '';
}

function onScan(code) {
    if (currentMode === 'location') {
        window.location.href = `<?= APP_URL ?>/scan/location?qr=${encodeURIComponent(code)}`;
    } else {
        document.getElementById('scan-result').textContent = 'Looking up…';
        fetch(`<?= APP_URL ?>/scan/product?barcode=${encodeURIComponent(code)}`)
            .then(r => r.json())
            .then(data => {
                const name = data.name || code;
                document.getElementById('scan-result').innerHTML =
                    `Found: <strong>${name}</strong>. <a href="<?= APP_URL ?>/inventory/create?barcode=${encodeURIComponent(code)}&item_name=${encodeURIComponent(name)}" style="color:var(--accent);font-weight:700;">Add to inventory →</a>`;
            });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    scanner = new Html5Qrcode('qr-reader');
    scanner.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 280, height: 200 } },
        (code) => onScan(code)
    ).catch(() => {
        document.getElementById('scan-result').textContent = 'Camera access denied or unavailable.';
    });
});
</script>
