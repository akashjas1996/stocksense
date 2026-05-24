<h5 class="fw-bold mb-3">Scan</h5>

<div class="card shadow-sm mb-3">
    <div class="card-body p-3">
        <p class="text-muted small mb-2">
            Point camera at a <strong>room or container QR</strong> to view its contents,
            or at a <strong>product barcode</strong> to add an item.
        </p>
        <div id="qr-reader" style="width:100%"></div>
        <div id="scan-result" class="mt-2 text-center text-muted small"></div>
    </div>
</div>

<div class="d-flex gap-2">
    <button id="btn-location" class="btn btn-dark flex-fill active-mode" onclick="setMode('location')">
        <i class="bi bi-qr-code"></i> Location
    </button>
    <button id="btn-product" class="btn btn-outline-dark flex-fill" onclick="setMode('product')">
        <i class="bi bi-upc-scan"></i> Product
    </button>
</div>
<p class="text-muted small text-center mt-2" id="mode-hint">Scanning for room / container QR code</p>

<script>
let scanner = null;
let currentMode = 'location';

function setMode(mode) {
    currentMode = mode;
    document.getElementById('btn-location').className = mode === 'location'
        ? 'btn btn-dark flex-fill' : 'btn btn-outline-dark flex-fill';
    document.getElementById('btn-product').className = mode === 'product'
        ? 'btn btn-dark flex-fill' : 'btn btn-outline-dark flex-fill';
    document.getElementById('mode-hint').textContent = mode === 'location'
        ? 'Scanning for room / container QR code'
        : 'Scanning product barcode to add item';
    document.getElementById('scan-result').textContent = '';
}

function onScan(code) {
    if (currentMode === 'location') {
        // Navigate directly — server handles QR lookup
        window.location.href = `<?= APP_URL ?>/scan/location?qr=${encodeURIComponent(code)}`;
    } else {
        document.getElementById('scan-result').textContent = 'Looking up…';
        fetch(`<?= APP_URL ?>/scan/product?barcode=${encodeURIComponent(code)}`)
            .then(r => r.json())
            .then(data => {
                const name = data.name || code;
                document.getElementById('scan-result').innerHTML =
                    `Found: <strong>${name}</strong>. <a href="<?= APP_URL ?>/inventory/create?barcode=${encodeURIComponent(code)}&item_name=${encodeURIComponent(name)}">Add to inventory →</a>`;
            });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    scanner = new Html5Qrcode('qr-reader');
    scanner.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 280, height: 200 } },
        (code) => onScan(code)
    ).catch(err => {
        document.getElementById('scan-result').textContent = 'Camera access denied or unavailable.';
    });
});
</script>
