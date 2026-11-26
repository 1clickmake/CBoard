<?php include_header('Register', $siteConfig); ?>

<?php if (($siteConfig['template'] ?? 'basic') === 'sebyeol'): ?>
<style>
.sb-auth-card { background:rgba(15,20,32,0.88); border:1px solid rgba(255,255,255,0.14); border-radius:14px; padding:38px 32px; backdrop-filter:blur(12px); box-sizing:border-box; }
.sb-auth-field { margin-bottom:16px; }
.sb-auth-label { display:block; color:#d1d5db; font-size:13px; font-weight:600; margin-bottom:7px; }
.sb-auth-input { width:100% !important; box-sizing:border-box !important; padding:12px 14px !important; background:rgba(255,255,255,0.07) !important; border:1px solid rgba(255,255,255,0.18) !important; border-radius:8px !important; color:#fff !important; -webkit-text-fill-color:#fff !important; caret-color:#fff; font-size:14px !important; outline:none !important; height:auto !important; }
.sb-auth-input:focus { background:rgba(255,255,255,0.12) !important; border-color:#818cf8 !important; }
.sb-auth-input::placeholder { color:#9ca3af !important; -webkit-text-fill-color:#9ca3af !important; }
.sb-auth-btn { width:100%; padding:13px; background:#fff; color:#0b0f19 !important; font-weight:700; font-size:15px; border:none; border-radius:8px; cursor:pointer; margin-top:8px; box-sizing:border-box; }
.sb-auth-card .feedback-text { font-size:12px; margin-top:5px; }
</style>
<section class="container">
    <canvas id="stars" aria-hidden="true"></canvas>
    <h2 class="title">Join Us</h2>
    <div class="inner" style="max-width:480px;margin:0 auto;padding:10px 20px 90px;box-sizing:border-box;">
        <div class="sb-auth-card">
            <p style="text-align:center;color:#9ca3af;font-size:14px;margin-bottom:24px;">세별 회원가입</p>

            <?php if (isset($error)): ?>
                <div style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);color:#fca5a5;padding:12px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="/register" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="country" value="<?= $_SESSION['visitor_country'] ?? 'Unknown' ?>">
                <div class="sb-auth-field">
                    <label class="sb-auth-label">아이디</label>
                    <input type="text" name="user_id" id="user_id" class="sb-auth-input" required placeholder="아이디를 입력해 주세요">
                    <div id="user_id_feedback" class="feedback-text"></div>
                </div>
                <div class="sb-auth-field">
                    <label class="sb-auth-label">이름 (닉네임)</label>
                    <input type="text" name="username" id="username" class="sb-auth-input" required placeholder="이름을 입력해 주세요">
                    <div id="username_feedback" class="feedback-text"></div>
                </div>
                <div class="sb-auth-field">
                    <label class="sb-auth-label">이메일</label>
                    <input type="email" name="email" id="email" class="sb-auth-input" required placeholder="이메일을 입력해 주세요">
                    <div id="email_feedback" class="feedback-text"></div>
                </div>
                <div class="sb-auth-field">
                    <label class="sb-auth-label">비밀번호</label>
                    <input type="password" name="password" class="sb-auth-input" required placeholder="비밀번호를 입력해 주세요">
                </div>
                <div class="sb-auth-field">
                    <label class="sb-auth-label">비밀번호 확인</label>
                    <input type="password" name="confirm_password" class="sb-auth-input" required placeholder="비밀번호를 다시 입력해 주세요">
                </div>
                <div class="sb-auth-field">
                    <label class="sb-auth-label"><?= htmlspecialchars($captcha_q ?? 'CAPTCHA') ?></label>
                    <input type="text" name="captcha_answer" class="sb-auth-input" required inputmode="numeric" autocomplete="off" placeholder="정답을 입력해 주세요">
                </div>
                <button type="submit" class="sb-auth-btn">회원가입</button>
            </form>

            <p style="text-align:center;color:#9ca3af;font-size:13px;margin-top:20px;">
                이미 계정이 있으신가요? <a href="/login" style="color:#fff;text-decoration:underline;margin-left:4px;">로그인</a>
            </p>
        </div>
    </div>
</section>
<?php else: ?>
<div class="container auth-container-register">
    <div class="glass-card">
        <h2 class="auth-title"><?= t('auth.reg.title') ?></h2>
        
        <?php if (isset($error)): ?>
            <div class="auth-alert-error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="/register" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="country" value="<?= $_SESSION['visitor_country'] ?? 'Unknown' ?>">
            <div class="form-group">
                <label><?= t('auth.login.user_id') ?></label>
                <input type="text" name="user_id" id="user_id" class="form-control" required>
                <div id="user_id_feedback" class="feedback-text"></div>
            </div>
            <div class="form-group">
                <label><?= t('auth.reg.username') ?></label>
                <input type="text" name="username" id="username" class="form-control" required>
                <div id="username_feedback" class="feedback-text"></div>
            </div>
            <div class="form-group">
                <label><?= t('auth.reg.email') ?></label>
                <input type="email" name="email" id="email" class="form-control" required>
                <div id="email_feedback" class="feedback-text"></div>
            </div>
            <div class="form-group">
                <label><?= t('auth.login.password') ?></label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label><?= t('auth.reg.confirm_password') ?></label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label><?= htmlspecialchars($captcha_q ?? 'CAPTCHA') ?></label>
                <input type="text" name="captcha_answer" class="form-control" required inputmode="numeric" autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-auth-submit"><?= t('auth.reg.submit') ?></button>
        </form>
        
        <p class="auth-footer-text">
            <?= t('auth.reg.has_account') ?> <a href="/login" class="auth-link"><?= t('auth.reg.login_here') ?></a>
        </p>
    </div>
</div>
<?php endif; ?>


<?php include_footer($siteConfig); ?>

<!-- Success Modal -->
<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
<div id="successModal" class="modal-overlay">
    <div class="modal-content glass-card success-box">
        <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h3>Welcome Aboard!</h3>
        <p>회원가입이 완료 되었습니다.<br>잠시 후 로그인 페이지로 이동합니다.</p>
        
        <div class="timer-bar">
            <div id="timerProgress" class="progress"></div>
        </div>

        <div class="timer-link-container">
            <a href="/login" class="btn btn-primary">지금 바로 로그인</a>
            <span class="timer-text">
                <span id="countdown">3</span>초 후 자동 이동
            </span>
        </div>
    </div>
</div>

<?php endif; ?>
