<?php include_header('Login', $siteConfig); ?>

<?php if (($siteConfig['template'] ?? 'basic') === 'sebyeol'): ?>
<style>
.sb-auth-card { background:rgba(15,20,32,0.88); border:1px solid rgba(255,255,255,0.14); border-radius:14px; padding:38px 32px; backdrop-filter:blur(12px); box-sizing:border-box; }
.sb-auth-field { margin-bottom:18px; }
.sb-auth-label { display:block; color:#d1d5db; font-size:13px; font-weight:600; margin-bottom:8px; }
.sb-auth-input { width:100% !important; box-sizing:border-box !important; padding:12px 14px !important; background:rgba(255,255,255,0.07) !important; border:1px solid rgba(255,255,255,0.18) !important; border-radius:8px !important; color:#fff !important; -webkit-text-fill-color:#fff !important; caret-color:#fff; font-size:14px !important; outline:none !important; height:auto !important; }
.sb-auth-input:focus { background:rgba(255,255,255,0.12) !important; border-color:#818cf8 !important; }
.sb-auth-input:-webkit-autofill,
.sb-auth-input:-webkit-autofill:hover,
.sb-auth-input:-webkit-autofill:focus,
.sb-auth-input:-webkit-autofill:active { -webkit-box-shadow: 0 0 0 1000px #171e2e inset !important; -webkit-text-fill-color: #ffffff !important; caret-color: #ffffff !important; border-color: rgba(255,255,255,0.22) !important; transition: background-color 5000s ease-in-out 0s; }
.sb-auth-input::placeholder { color:#9ca3af !important; -webkit-text-fill-color:#9ca3af !important; }
.sb-auth-btn { width:100%; padding:13px; background:#fff; color:#0b0f19 !important; font-weight:700; font-size:15px; border:none; border-radius:8px; cursor:pointer; margin-top:6px; box-sizing:border-box; }
</style>
<section class="container">
    <canvas id="stars" aria-hidden="true"></canvas>
    <h2 class="title">Login</h2>
    <div class="inner" style="max-width:440px;margin:0 auto;padding:10px 20px 90px;box-sizing:border-box;">
        <div class="sb-auth-card">
            <p style="text-align:center;color:#9ca3af;font-size:14px;margin-bottom:26px;">세별 관리자 및 회원 로그인</p>

            <?php if (isset($error)): ?>
                <div style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);color:#fca5a5;padding:12px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="sb-auth-field">
                    <label class="sb-auth-label">아이디</label>
                    <input type="text" name="user_id" class="sb-auth-input" required autocomplete="username" placeholder="아이디를 입력해 주세요">
                </div>
                <div class="sb-auth-field">
                    <label class="sb-auth-label">비밀번호</label>
                    <input type="password" name="password" class="sb-auth-input" required autocomplete="current-password" placeholder="비밀번호를 입력해 주세요">
                </div>
                <?php if (isset($captcha_q)): ?>
                <div class="sb-auth-field">
                    <label class="sb-auth-label"><?= htmlspecialchars($captcha_q) ?></label>
                    <input type="text" name="captcha_answer" class="sb-auth-input" required inputmode="numeric" autocomplete="off" placeholder="정답을 입력해 주세요">
                </div>
                <?php endif; ?>
                <button type="submit" class="sb-auth-btn">로그인</button>
            </form>

            <p style="text-align:center;color:#9ca3af;font-size:13px;margin-top:20px;">
                계정이 없으신가요? <a href="/register" style="color:#fff;text-decoration:underline;margin-left:4px;">회원가입</a>
            </p>
        </div>
    </div>
</section>
<?php else: ?>
<div class="container auth-container">
    <div class="glass-card">
        <h2 class="auth-title"><?= t('auth.login.title') ?></h2>
        
        <?php if (isset($error)): ?>
            <div class="auth-alert-error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group mb-3">
                <label><?= t('auth.login.user_id') ?></label>
                <input type="text" name="user_id" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label><?= t('auth.login.password') ?></label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <?php if (isset($captcha_q)): ?>
            <div class="form-group mb-3">
                <label><?= htmlspecialchars($captcha_q) ?></label>
                <input type="text" name="captcha_answer" class="form-control" required inputmode="numeric" autocomplete="off">
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary btn-auth-submit"><?= t('auth.login.submit') ?></button>
        </form>
        
        <p class="auth-footer-text">
            Don't have an account? <a href="/register" class="auth-link">Register here</a>
        </p>
    </div>
</div>
<?php endif; ?>

<?php include_footer($siteConfig); ?>
