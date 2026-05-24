<div class="page-head">
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1>Edit Room</h1>
</div>

<div class="card" style="padding:20px;margin-bottom:12px;">
    <form method="POST" action="<?= APP_URL ?>/rooms/<?= $room['id'] ?>/update">
        <div class="form-group">
            <label>Room Name</label>
            <input type="text" name="name" class="form-control" required autofocus
                   value="<?= e($room['name']) ?>">
        </div>
        <div style="display:flex;gap:8px;align-items:center;margin-top:4px;margin-bottom:16px;">
            <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>/qr" class="btn-outline" style="font-size:.82rem;padding:8px 14px;text-decoration:none;">
                <i class="bi bi-qr-code"></i> QR Code
            </a>
        </div>
        <button type="submit" class="btn-accent">Save</button>
    </form>
</div>

<form method="POST" action="<?= APP_URL ?>/rooms/<?= $room['id'] ?>/delete"
      onsubmit="return confirm('Delete this room and all its contents?')">
    <button type="submit" style="width:100%;padding:13px;background:none;border:1.5px solid var(--danger);border-radius:var(--r);color:var(--danger);font-weight:700;font-size:.9rem;cursor:pointer;">
        <i class="bi bi-trash"></i> Delete Room
    </button>
</form>
