<?php 
$title = isset($post) ? 'Edit Post' : 'Write Post';
include_header($title, $siteConfig); 
?>
<style><?php include CM_BOARD_SKINS_PATH . '/basic/style.css'; ?></style>

<?php do_action('board_editor_head'); ?>

<div class="container basic-container">
    <div class="glass-card">
        <div class="write-header">
            <h1 class="write-title"><?= $title ?></h1>
            <p class="write-subtitle">Board: <?= htmlspecialchars($board['title']) ?></p>
        </div>
        
        <form id="writeForm" action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?= isset($post) ? htmlspecialchars($post['title']) : '' ?>" required placeholder="Enter post title...">
            </div>
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <div class="editor-mode-container">
                    <label class="editor-label">Content</label>
                    <div class="btn-group mode-switch-group" role="group">
                        <input type="radio" class="btn-check" name="editor_mode" id="mode_visual" value="visual" <?= ($post['editor_mode'] ?? 'visual') === 'visual' ? 'checked' : '' ?> autocomplete="off">
                        <label class="btn btn-sm btn-mode-label" for="mode_visual">Visual</label>

                        <input type="radio" class="btn-check" name="editor_mode" id="mode_html" value="html" <?= ($post['editor_mode'] ?? 'visual') === 'html' ? 'checked' : '' ?> autocomplete="off">
                        <label class="btn btn-sm btn-mode-label" for="mode_html">HTML</label>
                    </div>
                </div>

                <!-- Rich Editor Container (provided by editor plugin) -->
                <div id="visual-editor-wrapper" style="<?= ($post['editor_mode'] ?? 'visual') === 'visual' ? '' : 'display: none;' ?>">
                    <?php do_action('board_editor', isset($post) ? $_raw['post']['content'] : ''); ?>
                </div>

                <!-- Raw HTML Textarea -->
                <div id="html-editor-wrapper" style="<?= ($post['editor_mode'] ?? 'visual') === 'html' ? '' : 'display: none;' ?>">
                    <textarea id="html_content" class="form-control html-editor-textarea"><?= isset($post) ? htmlspecialchars($_raw['post']['content']) : '' ?></textarea>
                </div>
                
                <!-- Hidden input to store HTML content -->
                <input type="hidden" name="content" id="content">
            </div>

            <div class="form-group" style="margin-top: 2rem;">
                <label class="attachments-label">📎 Attachments (Max 5)</label>
                
                <?php if (isset($files) && !empty($files)): ?>
                    <div class="existing-files-wrapper">
                        <p class="existing-files-note">Existing Files:</p>
                        <?php foreach ($files as $file): ?>
                            <div class="existing-file-item">
                                <span class="file-name"><?= htmlspecialchars($file['original_name']) ?></span>
                                <label class="delete-file-label">
                                    <input type="checkbox" name="delete_files[]" value="<?= $file['id'] ?>"> Delete
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div id="file-inputs">
                    <div class="file-input-group">
                        <input type="file" name="attachments[]" class="form-control">
                        <button type="button" class="btn btn-primary btn-add-file">+</button>
                    </div>
                </div>
            </div>
            
            <div class="write-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <?= isset($post) ? '✏️ Update Post' : '📝 Submit Post' ?>
                </button>
                <a href="/board/<?= $board['slug'] ?>" class="btn btn-cancel">❌ Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php $currentSlug = isset($board) ? $board['slug'] : (isset($post['board_slug']) ? $post['board_slug'] : ''); ?>
<?php do_action('board_editor_scripts', $csrf_token, $currentSlug); ?>
<script>
$(document).ready(function() {
    // Fallback when the editor plugin is absent: fall back to raw HTML mode
    if (!window.asamiyaEditor) {
        $('input[name="editor_mode"][value="html"]').prop('checked', true);
        $('#visual-editor-wrapper').hide();
        $('#html-editor-wrapper').show();
    }

    $('#writeForm').on('submit', function() {
        if (!window.asamiyaEditor) {
            $('#content').val($('#html_content').val());
        }
    });

    // Dynamic File Inputs
    $(document).on('click', '.btn-add-file', function() {
        var count = $('.file-input-group').length;
        if (count < 5) {
            var newRow = `
                <div class="file-input-group">
                    <input type="file" name="attachments[]" class="form-control">
                    <button type="button" class="btn btn-remove-file">−</button>
                </div>`;
            $('#file-inputs').append(newRow);
        } else {
            alert('You can upload up to 5 files.');
        }
    });

    $(document).on('click', '.btn-remove-file', function() {
        $(this).closest('.file-input-group').remove();
    });
});
</script>

<?php include_footer($siteConfig); ?>
