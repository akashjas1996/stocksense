<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">🏠</div>
        <div class="auth-title">Create account</div>
        <div class="auth-sub">Join your family's inventory.</div>

        <form method="POST" action="<?= APP_URL ?>/auth/register">
            <div class="form-group">
                <label>Your name</label>
                <input type="text" name="name" class="form-control" required autofocus placeholder="Akaash">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required placeholder="you@example.com">
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <label>Password <span style="font-weight:500;text-transform:none">(min 6 chars)</span></label>
                <input type="password" name="password" class="form-control" minlength="6" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-accent">Create account</button>
        </form>

        <div style="text-align:center;margin-top:16px;font-size:.85rem;color:var(--text-3);">
            Already have an account? <a href="<?= APP_URL ?>/auth/login" style="color:var(--accent);font-weight:700">Sign in</a>
        </div>
    </div>
</div>
