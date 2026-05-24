<div class="text-center mb-4">
    <h2 class="fw-bold"><?= APP_NAME ?></h2>
    <p class="text-muted">Home inventory, sorted.</p>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/auth/login">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control form-control-lg" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control form-control-lg" required>
            </div>
            <button class="btn btn-dark btn-lg w-100">Sign In</button>
        </form>
    </div>
</div>

<p class="text-center mt-3 text-muted">
    No account? <a href="<?= APP_URL ?>/auth/register">Create one</a>
</p>
