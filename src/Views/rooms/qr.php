<div class="d-flex align-items-center mb-3 gap-2">
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">QR Code</h5>
</div>

<div class="card shadow-sm text-center p-4">
    <p class="text-muted mb-1">Print and stick this in your <strong><?= e($room['name']) ?></strong></p>
    <div id="qr-container" class="mx-auto my-3" style="max-width:250px"></div>
    <p class="text-muted small"><?= e($qrPayload) ?></p>
    <button onclick="window.print()" class="btn btn-dark mt-2">
        <i class="bi bi-printer"></i> Print
    </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
QRCode.toCanvas(document.createElement('canvas'), <?= json_encode($qrPayload) ?>, {width:250}, function(err, canvas){
    if (!err) document.getElementById('qr-container').appendChild(canvas);
});
</script>
