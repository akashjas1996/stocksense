<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">🏠</div>
        <div class="auth-title">Stock<span style="color:var(--accent)">Sense</span></div>
        <div class="auth-sub">Home inventory, sorted.</div>

        <form method="POST" action="<?= APP_URL ?>/auth/login">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required autofocus placeholder="you@example.com">
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-accent">Sign in</button>
        </form>

        <div style="text-align:center;margin-top:16px;font-size:.85rem;color:var(--text-3);">
            No account? <a href="<?= APP_URL ?>/auth/register" style="color:var(--accent);font-weight:700">Create one</a>
        </div>
    </div>
</div>
