<div class="d-flex align-items-center mb-3 gap-2">
    <a href="<?= APP_URL ?>/rooms" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">Add Room</h5>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/rooms/store">
            <div class="mb-3">
                <label class="form-label">Room Name</label>
                <input type="text" name="name" class="form-control form-control-lg"
                       placeholder="e.g. Kitchen, Pantry, Bedroom" required autofocus>
            </div>
            <button class="btn btn-dark btn-lg w-100">Create Room</button>
        </form>
    </div>
</div>
