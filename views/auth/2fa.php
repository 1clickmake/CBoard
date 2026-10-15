<?php include_header('Two-Factor Authentication', $siteConfig); ?>

<div class="container auth-container">
    <div class="glass-card">
        <h2 class="auth-title">Two-Factor Authentication</h2>
        <p class="auth-footer-text">Enter the 6-digit code from your authenticator app.</p>

        <?php if (isset($error)): ?>
            <div class="auth-alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/login/2fa" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group mb-3">
                <label>Authentication Code</label>
                <input type="text" name="otp" class="form-control" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="123456">
            </div>
            <button type="submit" class="btn btn-primary btn-auth-submit">Verify</button>
        </form>
    </div>
</div>

<?php include_footer($siteConfig); ?>
