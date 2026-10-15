<?php

/**
 * Asamiya Editor Plugin Hooks
 *
 * Board write skins (basic/gallery/blog) call:
 *   do_action('board_editor_head')
 *   do_action('board_editor', $initialHtml)
 *   do_action('board_editor_scripts', $csrf_token, $boardSlug)
 */

// 1. Editor stylesheet in <head> area of the write form
add_action('board_editor_head', function() {
?>
<link href="/asamiya-editor/asset/css" rel="stylesheet">
<?php
});

// 2. Editor mount container (initial content = already-sanitized saved HTML)
add_action('board_editor', function($initialHtml = '') {
?>
<div id="asamiya-editor"><?= $initialHtml ?></div>
<?php
});

// 3. Editor runtime + board integration (image upload, mode switch, submit)
add_action('board_editor_scripts', function($csrf_token = '', $boardSlug = '') {
?>
<script src="/asamiya-editor/asset/js"></script>
<script>
(function() {
    const ASAMIYA_CSRF = <?= json_encode((string)$csrf_token) ?>;
    const ASAMIYA_SLUG = <?= json_encode((string)$boardSlug) ?>;

    // Route the Asamiya image button through our secured upload endpoint
    // (field: image + csrf_token, matching BoardController::uploadEditorImage)
    AsamiyaEditor.prototype.handleImageUpload = function() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = () => {
            const file = input.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('image', file);
            formData.append('csrf_token', ASAMIYA_CSRF);

            fetch('/board/upload-image/' + encodeURIComponent(ASAMIYA_SLUG), {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.error) {
                    alert('Image upload failed: ' + result.error);
                    return;
                }
                this.contentArea.focus();
                document.execCommand('insertImage', false, result.url);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Image upload failed');
            });
        };
    };

    // Initial content comes from #asamiya-editor innerHTML
    window.asamiyaEditor = new AsamiyaEditor('#asamiya-editor', {
        placeholder: 'Write your content here...',
        height: '400px'
    });

    const editor = window.asamiyaEditor;

    // Visual/HTML mode switching
    document.querySelectorAll('input[name="editor_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'html') {
                document.getElementById('html_content').value = editor.getHTML();
                document.getElementById('visual-editor-wrapper').style.display = 'none';
                document.getElementById('html-editor-wrapper').style.display = '';
            } else {
                editor.setHTML(document.getElementById('html_content').value);
                document.getElementById('html-editor-wrapper').style.display = 'none';
                document.getElementById('visual-editor-wrapper').style.display = '';
            }
        });
    });

    // Submit: copy editor content into the hidden field
    const form = document.getElementById('writeForm');
    if (form) {
        form.addEventListener('submit', function() {
            const mode = document.querySelector('input[name="editor_mode"]:checked');
            const content = document.getElementById('content');
            if (mode && mode.value === 'html') {
                content.value = document.getElementById('html_content').value;
            } else {
                content.value = editor.getHTML();
            }
        });
    }
})();
</script>
<?php
});
