<?php

/**
 * Asamiya Editor Plugin Hooks
 *
 * Board write skins (basic/gallery/blog) call:
 *   do_action('board_editor_head')
 *   do_action('board_editor', $initialHtml)
 *   do_action('board_editor_scripts', $csrf_token, $boardSlug)
 *
 * Admin page form calls the same hooks with $options.
 * Admin mail form calls head/editor/scripts with mail $options.
 */

// Cache-buster for plugin assets (bump after asset changes)
const ASAMIYA_EDITOR_ASSET_V = '1.0.1';

// 1. Editor stylesheet in <head> area of the write form
add_action('board_editor_head', function() {
?>
<link href="/asamiya-editor/asset/css?v=<?= ASAMIYA_EDITOR_ASSET_V ?>" rel="stylesheet">
<link href="/asamiya-editor/asset/theme?v=<?= ASAMIYA_EDITOR_ASSET_V ?>" rel="stylesheet">
<?php
});

// 2. Editor mount container (initial content = already-sanitized saved HTML)
add_action('board_editor', function($initialHtml = '') {
?>
<div id="asamiya-editor"><?= $initialHtml ?></div>
<?php
});

// 3. Editor runtime + integration (image upload, mode switch, submit)
//
// $options:
//   formId    - form element id (default: writeForm)
//   contentId - hidden input id receiving HTML on submit (default: content)
//   htmlId    - raw HTML textarea id (default: html_content)
//   uploadUrl - image upload endpoint; null = '/board/upload-image/{slug}' (default: null)
//   height    - editor height (default: 400px)
//   exposeAs  - extra window global exposing {clipboard:{dangerouslyPasteHTML}}
//               for legacy integrations, e.g. 'pageEditor' (default: null)
add_action('board_editor_scripts', function($csrf_token = '', $boardSlug = '', $options = []) {
    $formId = $options['formId'] ?? 'writeForm';
    $contentId = $options['contentId'] ?? 'content';
    $htmlId = $options['htmlId'] ?? 'html_content';
    $uploadUrl = $options['uploadUrl'] ?? ('/board/upload-image/' . $boardSlug);
    $height = $options['height'] ?? '400px';
    $exposeAs = $options['exposeAs'] ?? null;
?>
<script src="/asamiya-editor/asset/js?v=<?= ASAMIYA_EDITOR_ASSET_V ?>"></script>
<script>
(function() {
    const ASAMIYA_CSRF = <?= json_encode((string)$csrf_token) ?>;
    const ASAMIYA_UPLOAD_URL = <?= json_encode((string)$uploadUrl) ?>;
    const FORM_ID = <?= json_encode((string)$formId) ?>;
    const CONTENT_ID = <?= json_encode((string)$contentId) ?>;
    const HTML_ID = <?= json_encode((string)$htmlId) ?>;
    const EXPOSE_AS = <?= json_encode($exposeAs) ?>;

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

            fetch(ASAMIYA_UPLOAD_URL, {
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
        height: <?= json_encode((string)$height) ?>
    });

    const editor = window.asamiyaEditor;

    // Legacy compat (e.g. ai-manager expects window.pageEditor.clipboard.dangerouslyPasteHTML)
    if (EXPOSE_AS) {
        window[EXPOSE_AS] = {
            clipboard: {
                dangerouslyPasteHTML: function(html) { editor.setHTML(html); }
            }
        };
    }

    // Visual/HTML mode switching
    document.querySelectorAll('input[name="editor_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'html') {
                document.getElementById(HTML_ID).value = editor.getHTML();
                document.getElementById('visual-editor-wrapper').style.display = 'none';
                document.getElementById('html-editor-wrapper').style.display = '';
            } else {
                editor.setHTML(document.getElementById(HTML_ID).value);
                document.getElementById('html-editor-wrapper').style.display = 'none';
                document.getElementById('visual-editor-wrapper').style.display = '';
            }
        });
    });

    // Submit: copy editor content into the hidden field
    const form = document.getElementById(FORM_ID);
    if (form) {
        form.addEventListener('submit', function() {
            const mode = document.querySelector('input[name="editor_mode"]:checked');
            const content = document.getElementById(CONTENT_ID);
            if (mode && mode.value === 'html') {
                content.value = document.getElementById(HTML_ID).value;
            } else {
                content.value = editor.getHTML();
            }
        });
    }
})();
</script>
<?php
});
