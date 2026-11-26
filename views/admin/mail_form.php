<?php $title = t_admin('admin.mail.title'); include_admin_header($title); ?>

<div class="admin-header-flex">
    <div>
        <h1 style="margin: 0;"><?= t_admin('admin.mail.title') ?></h1>
        <p class="text-muted-small" style="margin: 0;"><?= t_admin('admin.mail.subtitle') ?></p>
    </div>
    <div>
        <a href="javascript:void(0)" onclick="openSmtpSettings()" class="btn btn-secondary me-2">
            <i class="fa-solid fa-gear"></i> <?= t_admin('admin.mail.smtp_settings') ?>
        </a>
        <a href="/admin/mail/logs" class="btn btn-secondary">
            <i class="fa-solid fa-list"></i> <?= t_admin('admin.mail.view_logs') ?>
        </a>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-info" style="margin-bottom: 20px;">
    <?= htmlspecialchars(urldecode($_GET['msg'])) ?>
</div>
<?php endif; ?>

<div class="glass-card">
    <form action="/admin/mail/send" method="POST" id="mailForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

        <!-- Target Selection -->
        <h3 class="admin-section-header"><i class="fa-solid fa-users"></i> <?= t_admin('admin.mail.recipients') ?></h3>
        <div class="admin-section-container">
            <div class="form-group mb-4">
                <label class="form-label"><?= t_admin('admin.mail.send_to') ?></label>
                <div class="flex-gap-1">
                    <label class="selection-card active" onclick="selectTarget('all')">
                        <input type="radio" name="target_type" value="all" checked style="display: none;">
                        <i class="fa-solid fa-earth-americas fa-2x mb-2"></i>
                        <span><?= t_admin('admin.mail.target_all') ?></span>
                    </label>
                    <label class="selection-card" onclick="selectTarget('level')">
                        <input type="radio" name="target_type" value="level" style="display: none;">
                        <i class="fa-solid fa-layer-group fa-2x mb-2"></i>
                        <span><?= t_admin('admin.mail.target_level') ?></span>
                    </label>
                    <label class="selection-card" onclick="selectTarget('select')">
                        <input type="radio" name="target_type" value="select" style="display: none;">
                        <i class="fa-solid fa-user-check fa-2x mb-2"></i>
                        <span><?= t_admin('admin.mail.target_ids') ?></span>
                    </label>
                    <label class="selection-card" onclick="selectTarget('email')">
                        <input type="radio" name="target_type" value="email" style="display: none;">
                        <i class="fa-solid fa-at fa-2x mb-2"></i>
                        <span><?= t_admin('admin.mail.target_email') ?></span>
                    </label>
                </div>
            </div>

            <!-- Level Select -->
            <div id="target-level-box" style="display: none;" class="form-group">
                <label class="form-label"><?= t_admin('admin.mail.select_level') ?></label>
                <select name="target_level" class="form-select">
                    <?php foreach ($levels as $lv): ?>
                        <option value="<?= $lv ?>"><?= t_admin('admin.cfg.level_prefix') ?> <?= $lv ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ID Input -->
            <div id="target-select-box" style="display: none;" class="form-group">
                <label class="form-label"><?= t_admin('admin.mail.ids_label') ?></label>
                <textarea name="target_ids" class="form-control" placeholder="user1, user2, user3" rows="3"></textarea>
            </div>

            <!-- Email Input -->
            <div id="target-email-box" style="display: none;" class="form-group">
                <label class="form-label"><?= t_admin('admin.mail.emails_label') ?></label>
                <textarea name="target_emails" class="form-control" placeholder="test@test.com, user@domain.com" rows="3"></textarea>
            </div>
        </div>

        <!-- Content -->
        <h3 class="admin-section-header"><i class="fa-solid fa-envelope"></i> <?= t_admin('admin.mail.message') ?></h3>
        <div class="admin-section-container">
            <div class="form-group mb-4">
                <label class="form-label"><?= t_admin('admin.mail.subject') ?></label>
                <input type="text" name="subject" class="form-control" required>
            </div>

            <div class="form-group mb-4">
                <label class="form-label"><?= t_admin('admin.mail.content') ?></label>
                <?php do_action('board_editor_head'); ?>
                <?php do_action('board_editor', ''); ?>
                <input type="hidden" name="content" id="content">
            </div>

            <div class="form-group">
                <label class="form-label">📎 <?= t_admin('admin.mail.attachments') ?></label>
                <div id="file-inputs">
                    <div class="file-input-group mb-2 d-flex gap-2">
                        <input type="file" name="attachments[]" class="form-control">
                        <button type="button" class="btn btn-primary btn-add-file" style="width: 40px; height: 38px; display: flex; align-items: center; justify-content: center;">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i> <?= t_admin('admin.mail.send_btn') ?>
            </button>
        </div>
    </form>
</div>

    <?php do_action('board_editor_scripts', $csrf_token ?? '', '', ['formId' => 'mailForm', 'uploadUrl' => '/admin/pages/upload-image', 'height' => '400px']); ?>

<script>
    $('#link-mail').addClass('active');

    function selectTarget(type) {
        $('.selection-card').removeClass('active');
        $('input[name="target_type"][value="' + type + '"]').closest('.selection-card').addClass('active');
        $('input[name="target_type"][value="' + type + '"]').prop('checked', true);

        if (type === 'level') {
            $('#target-level-box').slideDown();
            $('#target-select-box').slideUp();
            $('#target-email-box').slideUp();
        } else if (type === 'select') {
            $('#target-level-box').slideUp();
            $('#target-select-box').slideDown();
            $('#target-email-box').slideUp();
        } else if (type === 'email') {
            $('#target-level-box').slideUp();
            $('#target-select-box').slideUp();
            $('#target-email-box').slideDown();
        } else {
            $('#target-level-box').slideUp();
            $('#target-select-box').slideUp();
            $('#target-email-box').slideUp();
        }
    }

    // SMTP Config Modal Logic
    const isSmtpConfigured = <?= json_encode($smtpConfigured) ?>;
    
    $(document).ready(function() {
        // Force open ONLY if not configured
        if (!isSmtpConfigured) {
            $('#smtpModal').fadeIn();
        }

        $('#mailForm').on('submit', function() {
            var html = window.asamiyaEditor ? window.asamiyaEditor.getHTML() : '';
            $('#content').val(html);

            // Check if subject/content is empty
            if (!$('input[name="subject"]').val()) {
                alert('메일 제목을 입력해 주세요.');
                return false;
            }
            var text = html.replace(/<[^>]*>/g, '').trim();
            if (text.length === 0 && html.indexOf('<img') === -1) {
                alert('메일 내용을 입력해 주세요.');
                return false;
            }

            return confirm('메일을 발송하시겠습니까?');
        });

        // Dynamic File Inputs
        $(document).on('click', '.btn-add-file', function() {
            var count = $('.file-input-group').length;
            if (count < 5) {
                var newRow = `
                    <div class="file-input-group mb-2 d-flex gap-2">
                        <input type="file" name="attachments[]" class="form-control">
                        <button type="button" class="btn btn-danger btn-remove-file" style="width: 40px; height: 38px; display: flex; align-items: center; justify-content: center;">-</button>
                    </div>`;
                $('#file-inputs').append(newRow);
            } else {
                alert('파일은 최대 5개까지 첨부할 수 있습니다.');
            }
        });

        $(document).on('click', '.btn-remove-file', function() {
            $(this).closest('.file-input-group').remove();
        });
    });

    function openSmtpSettings() {
        $('#smtpModal').fadeIn();
    }

    function closeSmtpSettings() {
        if (!isSmtpConfigured) {
            if(!confirm('메일 발송을 위해 SMTP 설정이 필요합니다. 그래도 닫으시겠습니까?')) return;
        }
        $('#smtpModal').fadeOut();
    }
</script>

<!-- SMTP Settings Modal -->
<div id="smtpModal" class="admin-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; backdrop-filter: blur(5px);">
    <div class="glass-card" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 500px; padding: 2rem;">
        <h3 class="mb-3 d-flex align-items-center">
            <i class="fa-solid fa-gear me-2 text-primary"></i> SMTP 설정
        </h3>
        <p class="text-muted-small mb-4">메일 발송을 위한 Google SMTP 설정을 입력해 주세요.</p>
        
        <form action="/admin/mail/config" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            
            <div class="form-group">
                <label class="form-label">SMTP 호스트</label>
                <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($smtpConfig['host']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">SMTP 포트</label>
                <input type="text" name="smtp_port" class="form-control" value="<?= htmlspecialchars($smtpConfig['port']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Google 이메일 (계정)</label>
                <input type="email" name="smtp_user" class="form-control" value="<?= htmlspecialchars($smtpConfig['user']) ?>" required placeholder="example@gmail.com">
            </div>

            <div class="form-group">
                <label class="form-label">앱 비밀번호</label>
                <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($smtpConfig['pass']) ?>" required placeholder="Google 계정 > 보안 > 앱 비밀번호">
                <small class="text-muted-small">로그인 비밀번호가 아닌 <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: #a855f7;">앱 비밀번호</a>를 입력하세요.</small>
            </div>

            <div class="form-group">
                <label class="form-label">발신자 이름</label>
                <input type="text" name="smtp_from_name" class="form-control" value="<?= htmlspecialchars($smtpConfig['from_name']) ?>" required>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn" onclick="closeSmtpSettings()" class="btn btn-cancel-light">닫기</button>
                <button type="submit" class="btn btn-primary">설정 저장</button>
            </div>
        </form>
    </div>
</div>

<?php include_admin_footer(); ?>
