<?php
$title = '비밀글 확인';
include_header($title, $siteConfig);
?>
<style>
.sb-secret-card { background:rgba(15,20,32,0.88); border:1px solid rgba(255,255,255,0.14); border-radius:14px; padding:38px 32px; backdrop-filter:blur(12px); box-sizing:border-box; }
.sb-secret-input { width:100% !important; box-sizing:border-box !important; padding:12px 14px !important; background:rgba(255,255,255,0.07) !important; border:1px solid rgba(255,255,255,0.18) !important; border-radius:8px !important; color:#fff !important; -webkit-text-fill-color:#fff !important; caret-color:#fff; font-size:14px !important; outline:none !important; height:auto !important; }
.sb-secret-input:focus { background:rgba(255,255,255,0.12) !important; border-color:#818cf8 !important; }
</style>
<section class="container">
    <canvas id="stars" aria-hidden="true"></canvas>
    <h2 class="title"><?= htmlspecialchars($post['board_title'] ?? 'Contact') ?></h2>
    <div class="inner" style="max-width:440px;margin:0 auto;padding:10px 20px 90px;box-sizing:border-box;">
        <div class="sb-secret-card">
            <h3 style="color:#fff;font-size:18px;font-weight:700;text-align:center;margin-bottom:10px;"><?= htmlspecialchars($secretTitle ?? '비밀글입니다') ?></h3>
            <p style="text-align:center;color:#9ca3af;font-size:13px;margin-bottom:24px;">작성 시 입력한 비밀번호를 입력해 주세요.</p>

            <?php if (!empty($secretError)): ?>
                <div style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);color:#fca5a5;padding:12px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;">
                    <?= htmlspecialchars($secretError) ?>
                </div>
            <?php endif; ?>

            <form action="<?= htmlspecialchars($actionUrl ?? ('/board/view/' . (int)$post['id'])) ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div style="margin-bottom:18px;">
                    <label style="display:block;color:#d1d5db;font-size:13px;font-weight:600;margin-bottom:8px;">비밀번호</label>
                    <input type="password" name="secret_password" class="sb-secret-input" required placeholder="비밀번호를 입력해 주세요" autofocus>
                </div>
                <div class="board-btn-group" style="margin-top:20px;justify-content:center;">
                    <button type="submit" class="btn-write" style="border:none;cursor:pointer;">확인</button>
                    <a href="/board/<?= htmlspecialchars($post['board_slug']) ?>" class="btn-cancel">목록</a>
                </div>
            </form>
        </div>
    </div>
</section>
<?php include_footer($siteConfig); ?>
