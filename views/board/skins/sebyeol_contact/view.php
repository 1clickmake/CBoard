<?php
$title = $post['title'];
include_header($title, $siteConfig);
?>
<section class="container">
    <canvas id="stars" aria-hidden="true"></canvas>
    <h2 class="title">Contact</h2>
    <div class="inner">
        <div class="board-view">
            <div class="subjectBox">
                <div class="leftArea">
                    <h3 class="subject"><?= htmlspecialchars($post['title']) ?></h3>
                </div>
                <div class="rightArea">
                    <p>
                        <span class="stit">작성자</span>
                        <span class="name"><?= htmlspecialchars(!empty($post['username']) ? $post['username'] : (!empty($post['author_name']) ? $post['author_name'] : '손님')) ?></span>
                    </p>
                    <p>
                        <span class="stit">등록일</span>
                        <span class="date"><?= date('Y-m-d', strtotime($post['created_at'])) ?></span>
                    </p>
                    <p>
                        <span class="stit">조회수</span>
                        <span class="name"><?= (int)($post['views'] ?? 0) ?></span>
                    </p>
                </div>
            </div>

            <?php if (!empty($files)): ?>
            <div class="file_box">
                <?php foreach ($files as $file): ?>
                    <a href="/board/download/<?= (int)$file['id'] ?>" target="_blank">
                        <span class="i_file">file</span><?= htmlspecialchars($file['original_name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php
            $rawContent = $_raw['post']['content'] ?? '';
            if (!preg_match('/<(p|br|div|ul|ol|li|table|h[1-6])\b/i', $rawContent)) {
                $rawContent = nl2br($rawContent);
            }
            ?>
            <div class="content_box" style="color:#fff;font-size:15px;line-height:1.7;word-break:break-word;">
                <?= $rawContent ?>
            </div>
        </div>

        <?php if (!empty($comments)): ?>
        <div class="board-view" style="margin-top:24px;padding:24px;border-top:1px solid rgba(255,255,255,0.12);">
            <h4 style="margin-bottom:16px;font-size:16px;color:#fff;">답변</h4>
            <?php foreach ($comments as $cmt): ?>
                <div style="padding:14px 0;border-bottom:1px solid rgba(255,255,255,0.08);">
                    <div style="font-size:13px;color:#9ca3af;margin-bottom:6px;">
                        <strong style="color:#fff;"><?= htmlspecialchars(!empty($cmt['username']) ? $cmt['username'] : '손님') ?></strong>
                        · <?= date('Y-m-d H:i', strtotime($cmt['created_at'])) ?>
                    </div>
                    <div style="color:#e5e7eb;line-height:1.7;"><?= nl2br(htmlspecialchars($cmt['content'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($post['allow_comments']) && check_level($post['level_comment'] ?? 1)): ?>
        <div style="margin-top:24px;">
            <form id="sebyeolReplyForm" action="/board/comment/add" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                <textarea name="content" class="textArea_sty1" rows="4" placeholder="답변 내용을 입력하세요." required style="width:100%;box-sizing:border-box;display:block;"></textarea>
                <button type="submit" class="btn-write" style="display:block;width:100%;height:48px;line-height:48px;border:none;cursor:pointer;margin-top:12px;text-align:center;font-size:15px;">답변 등록</button>
            </form>
        </div>
        <script>
        document.getElementById('sebyeolReplyForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            var form = this;
            var btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            try {
                var res = await fetch('/board/comment/add', { method: 'POST', body: new FormData(form) });
                var data = await res.json();
                if (data && data.success) {
                    location.reload();
                } else {
                    alert((data && data.error) ? data.error : '답변 등록에 실패했습니다.');
                    if (btn) btn.disabled = false;
                }
            } catch (err) {
                alert('답변 등록 중 오류가 발생했습니다.');
                if (btn) btn.disabled = false;
            }
        });
        </script>
        <?php endif; ?>

        <?php
        $canModify = !empty($is_admin) || (!empty($is_member) && ($user['user_id'] ?? '') === ($post['user_id'] ?? ''));
        $isGuestPost = ($post['user_id'] ?? '') === 'guest';
        ?>
        <div class="board-btn-group">
            <a href="/board/<?= htmlspecialchars($post['board_slug']) ?>" class="btn-write">목록</a>
            <?php if ($canModify || $isGuestPost): ?>
                <a href="/board/edit/<?= (int)$post['id'] ?>" class="btn-cancel">수정</a>
            <?php endif; ?>
            <?php if ($canModify): ?>
                <form action="/board/delete/<?= (int)$post['id'] ?>" method="POST" onsubmit="return confirm('삭제하시겠습니까?')" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <button type="submit" class="btn-cancel" style="border:none;cursor:pointer;">삭제</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include_footer($siteConfig); ?>
