<div class="d-flex align-items-center mb-3 gap-2">
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">Edit Room</h5>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/rooms/<?= $room['id'] ?>/update">
            <div class="mb-4">
                <label class="form-label">Room Name</label>
                <input type="text" name="name" class="form-control form-control-lg"
                       value="<?= e($room['name']) ?>" required autofocus>
            </div>
            <button class="btn btn-dark btn-lg w-100">Save</button>
        </form>
    </div>
</div>

<form method="POST" action="<?= APP_URL ?>/rooms/<?= $room['id'] ?>/delete"
      onsubmit="return confirm('Delete this room and all its contents?')">
    <button class="btn btn-outline-danger w-100 mt-3">Delete Room</button>
</form>
