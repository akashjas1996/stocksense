<div class="text-center mb-4">
    <h2 class="fw-bold">Create Account</h2>
    <p class="text-muted">Join your family's inventory.</p>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= APP_URL ?>/auth/register">
            <div class="mb-3">
                <label class="form-label">Your Name</label>
                <input type="text" name="name" class="form-control form-control-lg" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control form-control-lg" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password <span class="text-muted small">(min 6 chars)</span></label>
                <input type="password" name="password" class="form-control form-control-lg" minlength="6" required>
            </div>
            <button class="btn btn-dark btn-lg w-100">Create Account</button>
        </form>
    </div>
</div>

<p class="text-center mt-3 text-muted">
    Already have an account? <a href="<?= APP_URL ?>/auth/login">Sign in</a>
</p>
