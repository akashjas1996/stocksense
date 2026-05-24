<div class="page-head">
    <a href="<?= APP_URL ?>/rooms" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1>Add Room</h1>
</div>

<div class="card" style="padding:20px;">
    <form method="POST" action="<?= APP_URL ?>/rooms/store">
        <div class="form-group">
            <label>Room Name</label>
            <input type="text" name="name" class="form-control" required autofocus
                   placeholder="e.g. Kitchen, Pantry, Bedroom">
        </div>
        <button type="submit" class="btn-accent">Create Room</button>
    </form>
</div>
