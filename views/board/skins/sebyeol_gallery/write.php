<?php
$title = isset($post) ? '게시글 수정' : '게시글 등록';
include_header($title, $siteConfig);
?>
<?php do_action('board_editor_head'); ?>
<style>
.sb-write-wrap { max-width: 960px; margin: 0 auto; padding: 30px 20px 90px; position: relative; z-index: 2; }
.sb-write-card { background: rgba(15, 20, 32, 0.88); border: 1px solid rgba(255, 255, 255, 0.14); border-radius: 14px; padding: 34px 30px; backdrop-filter: blur(12px); color: #f3f4f6; }
.sb-write-head { border-bottom: 1px solid rgba(255, 255, 255, 0.12); padding-bottom: 18px; margin-bottom: 24px; }
.sb-write-head h2 { font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 6px; }
.sb-write-head p { font-size: 13px; color: #9ca3af; }
.sb-field { margin-bottom: 22px; }
.sb-label { display: block; font-size: 13px; font-weight: 600; color: #e5e7eb; margin-bottom: 8px; }
.sb-input { width: 100%; padding: 12px 14px; background: rgba(255, 255, 255, 0.08) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; border-radius: 8px; color: #fff !important; -webkit-text-fill-color: #fff !important; caret-color: #fff; font-size: 14px; outline: none; box-sizing: border-box; }
.sb-input:focus, .sb-input:hover { background: rgba(255, 255, 255, 0.12) !important; border-color: #818cf8 !important; color: #fff !important; }
.sb-input::placeholder { color: #9ca3af !important; -webkit-text-fill-color: #9ca3af !important; }
.sb-mode-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.sb-mode-btns { display: flex; gap: 6px; }
.sb-mode-btns input { display: none; }
.sb-mode-btns label { padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; background: rgba(255,255,255,0.08); color: #d1d5db; border: 1px solid rgba(255,255,255,0.15); }
.sb-mode-btns input:checked + label { background: #fff; color: #0b0f19; border-color: #fff; }
.sb-editor-box { background: #fff; border-radius: 8px; overflow: hidden; color: #111827; }
.sb-editor-box button, .sb-editor-box select, .sb-editor-box i { color: #1f2937 !important; }
.sb-html-area { width: 100%; min-height: 320px; padding: 14px; background: #0b1120; border: 1px solid rgba(255,255,255,0.18); border-radius: 8px; color: #e5e7eb; font-family: Consolas, monospace; font-size: 13px; box-sizing: border-box; }
.sb-file-row { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
.sb-file-row input[type="file"] { flex: 1; padding: 9px 12px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.18); border-radius: 8px; color: #e5e7eb; font-size: 13px; }
.sb-btn-sm { width: 38px; height: 38px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color: #fff; font-size: 18px; cursor: pointer; }
.sb-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 28px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.12); }
.sb-btn-cancel { padding: 11px 24px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.25); color: #e5e7eb !important; font-size: 14px; font-weight: 600; background: transparent; }
.sb-btn-submit { padding: 11px 28px; border-radius: 8px; border: none; background: #fff; color: #0b0f19 !important; font-size: 14px; font-weight: 700; cursor: pointer; }
.sb-existing { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 14px; margin-bottom: 12px; font-size: 13px; color: #d1d5db; }
.sb-existing-item { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; }
</style>

<section class="container">
    <canvas id="stars" aria-hidden="true"></canvas>
    <h2 class="title"><?= htmlspecialchars($board['title']) ?></h2>

    <div class="sb-write-wrap">
        <div class="sb-write-card">
            <div class="sb-write-head">
                <h2><?= $title ?></h2>
                <p>게시판: <?= htmlspecialchars($board['title']) ?></p>
            </div>

            <form id="writeForm" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="sb-field">
                    <label class="sb-label" for="post-title">제목</label>
                    <input type="text" id="post-title" name="title" class="sb-input" value="<?= isset($post) ? htmlspecialchars($post['title']) : '' ?>" required placeholder="제목을 입력해 주세요">
                </div>

                <div class="sb-field">
                    <div class="sb-mode-bar">
                        <label class="sb-label" style="margin-bottom:0;">내용</label>
                        <div class="sb-mode-btns">
                            <input type="radio" name="editor_mode" id="mode_visual" value="visual" <?= ($post['editor_mode'] ?? 'visual') === 'visual' ? 'checked' : '' ?> autocomplete="off">
                            <label for="mode_visual">Visual</label>
                            <input type="radio" name="editor_mode" id="mode_html" value="html" <?= ($post['editor_mode'] ?? 'visual') === 'html' ? 'checked' : '' ?> autocomplete="off">
                            <label for="mode_html">HTML</label>
                        </div>
                    </div>

                    <div id="visual-editor-wrapper" class="sb-editor-box" style="<?= ($post['editor_mode'] ?? 'visual') === 'visual' ? '' : 'display:none;' ?>">
                        <?php do_action('board_editor', isset($post) ? $_raw['post']['content'] : ''); ?>
                    </div>

                    <div id="html-editor-wrapper" style="<?= ($post['editor_mode'] ?? 'visual') === 'html' ? '' : 'display:none;' ?>">
                        <textarea id="html_content" class="sb-html-area"><?= isset($post) ? htmlspecialchars($_raw['post']['content']) : '' ?></textarea>
                    </div>

                    <input type="hidden" name="content" id="content">
                </div>

                <?php
                $maxUploadFiles = isset($board['upload_count']) ? max(0, (int)$board['upload_count']) : 5;
                $existingFileCount = isset($files) && is_array($files) ? count($files) : 0;
                ?>
                <?php if ($maxUploadFiles > 0): ?>
                <div class="sb-field">
                    <label class="sb-label">첨부 이미지 (최대 <?= $maxUploadFiles ?>개 · 첫 번째 이미지가 대표 썸네일로 사용됩니다)</label>

                    <?php if (isset($files) && !empty($files)): ?>
                        <div class="sb-existing">
                            <div style="color:#9ca3af;margin-bottom:6px;">기존 첨부파일:</div>
                            <?php foreach ($files as $file): ?>
                                <div class="sb-existing-item">
                                    <span><?= htmlspecialchars($file['original_name']) ?></span>
                                    <label style="cursor:pointer;color:#fca5a5;">
                                        <input type="checkbox" name="delete_files[]" value="<?= (int)$file['id'] ?>"> 삭제
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div id="file-inputs">
                        <div class="sb-file-row file-input-group">
                            <input type="file" name="attachments[]" accept="image/*">
                            <?php if ($maxUploadFiles > 1): ?><button type="button" class="sb-btn-sm btn-add-file">+</button><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="sb-actions">
                    <a href="/board/<?= htmlspecialchars($board['slug']) ?>" class="sb-btn-cancel">취소</a>
                    <button type="submit" class="sb-btn-submit"><?= isset($post) ? '수정 완료' : '등록하기' ?></button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php $currentSlug = isset($board) ? $board['slug'] : (isset($post['board_slug']) ? $post['board_slug'] : ''); ?>
<?php do_action('board_editor_scripts', $csrf_token, $currentSlug); ?>
<script>
$(document).ready(function() {
    $('input[name="editor_mode"]').on('change', function() {
        if ($(this).val() === 'visual') {
            $('#html-editor-wrapper').hide();
            $('#visual-editor-wrapper').show();
        } else {
            $('#visual-editor-wrapper').hide();
            $('#html-editor-wrapper').show();
        }
    });

    if (!window.asamiyaEditor) {
        $('input[name="editor_mode"][value="html"]').prop('checked', true);
        $('#visual-editor-wrapper').hide();
        $('#html-editor-wrapper').show();
    }

    $('#writeForm').on('submit', function() {
        if (!window.asamiyaEditor || $('input[name="editor_mode"]:checked').val() === 'html') {
            $('#content').val($('#html_content').val());
        }
    });

    var maxUploadFiles = <?= $maxUploadFiles ?>;
    var existingFileCount = <?= $existingFileCount ?>;
    $(document).on('click', '.btn-add-file', function() {
        var count = $('.file-input-group').length;
        if (existingFileCount + count < maxUploadFiles) {
            $('#file-inputs').append(
                '<div class="sb-file-row file-input-group">' +
                '<input type="file" name="attachments[]" accept="image/*">' +
                '<button type="button" class="sb-btn-sm btn-remove-file">-</button>' +
                '</div>'
            );
        } else {
            alert('최대 ' + maxUploadFiles + '개까지 첨부할 수 있습니다.');
        }
    });

    $(document).on('click', '.btn-remove-file', function() {
        $(this).closest('.file-input-group').remove();
    });
});
</script>

<?php include_footer($siteConfig); ?>
