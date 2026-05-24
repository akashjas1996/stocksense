<div class="page-head">
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1>QR Code</h1>
</div>

<div class="card" style="text-align:center;padding:32px 20px;">
    <div style="font-size:2rem;margin-bottom:8px;"><?= roomEmoji($room['name']) ?></div>
    <div style="font-weight:800;font-size:1.1rem;margin-bottom:4px;"><?= e($room['name']) ?></div>
    <div style="font-size:.8rem;color:var(--text-3);margin-bottom:24px;">Print and stick this inside the room</div>
    <div id="qr-container" style="display:inline-block;padding:16px;background:#fff;border-radius:16px;border:1.5px solid var(--border);margin-bottom:20px;"></div>
    <div style="font-size:.72rem;color:var(--text-3);word-break:break-all;margin-bottom:20px;"><?= e($qrPayload) ?></div>
    <button onclick="window.print()" class="btn-accent">
        <i class="bi bi-printer"></i> Print
    </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
QRCode.toCanvas(document.createElement('canvas'), <?= json_encode($qrPayload) ?>, {width:220,margin:1}, function(err, canvas){
    if (!err) document.getElementById('qr-container').appendChild(canvas);
});
</script>
