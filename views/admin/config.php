<?php $title = t_admin('admin.cfg.title'); include_admin_header($title); $supportedLangs = cm_supported_langs(); ?>

<div class="admin-sticky-header admin-header-flex">
    <div>
        <h1 style="margin: 0; font-size: 1.5rem; font-weight: 700;"><?= t_admin('admin.cfg.title') ?></h1>
        <p class="text-muted-small" style="margin: 0;"><?= t_admin('admin.cfg.subtitle') ?></p>
    </div>
    <button type="submit" form="configForm" class="btn btn-primary" style="box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);">
        <i class="fa-solid fa-save"></i> <?= t_admin('admin.cfg.save') ?>
    </button>
</div>

<div class="glass-card">

    <form id="configForm" action="/admin/config" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <!-- Section 1: General Identity -->
        <h3 class="admin-section-header"><i class="fa-solid fa-globe"></i> <?= t_admin('admin.cfg.sec.identity') ?></h3>
        <div class="admin-section-container">
            <div class="form-group mb-4">
                <label class="form-label"><?= t_admin('admin.cfg.site_name') ?></label>
                <input type="text" name="site_name" class="form-control form-control-lg" value="<?= htmlspecialchars($config['site_name'] ?? '') ?>" placeholder="<?= htmlspecialchars(t_admin('admin.cfg.site_name_ph')) ?>" required>
                <small class="help-text"><?= t_admin('admin.cfg.site_name_help') ?></small>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label mb-3"><?= t_admin('admin.cfg.logo_type') ?></label>
                    <div class="flex-gap-1" style="margin-bottom: 1.5rem;">
                        <label class="selection-card <?= ($config['logo_type'] ?? 'text') === 'text' ? 'active' : '' ?>" onclick="selectLogoType('text')">
                            <input type="radio" name="logo_type" value="text" <?= ($config['logo_type'] ?? 'text') === 'text' ? 'checked' : '' ?> style="display: none;">
                            <i class="fa-solid fa-font fa-2x mb-2"></i>
                            <span><?= t_admin('admin.cfg.logo_text_opt') ?></span>
                        </label>
                        <label class="selection-card <?= ($config['logo_type'] ?? 'text') === 'image' ? 'active' : '' ?>" onclick="selectLogoType('image')">
                            <input type="radio" name="logo_type" value="image" <?= ($config['logo_type'] ?? 'text') === 'image' ? 'checked' : '' ?> style="display: none;">
                            <i class="fa-solid fa-image fa-2x mb-2"></i>
                            <span><?= t_admin('admin.cfg.logo_image_opt') ?></span>
                        </label>
                    </div>
                </div>

                <div class="col-md-12">
                    <!-- Text Logo Input -->
                    <div id="logo-text-box" style="display: <?= ($config['logo_type'] ?? 'text') === 'text' ? 'block' : 'none' ?>;">
                        <label class="form-label"><?= t_admin('admin.cfg.logo_text') ?></label>
                        <input type="text" name="logo_text" class="form-control" value="<?= htmlspecialchars($config['logo_text'] ?? '') ?>" placeholder="<?= htmlspecialchars(t_admin('admin.cfg.logo_text_ph')) ?>">
                    </div>

                    <!-- Image Logo Input -->
                    <div id="logo-image-box" style="display: <?= ($config['logo_type'] ?? 'text') === 'image' ? 'block' : 'none' ?>;">
                        <label class="form-label"><?= t_admin('admin.cfg.logo_image') ?></label>
                        <div class="flex-gap-15">
                            <div style="flex: 1;">
                                <input type="file" name="logo_image" class="form-control" accept="image/*">
                                <small class="help-text"><?= t_admin('admin.cfg.logo_image_help') ?></small>
                            </div>
                            <?php if (!empty($config['logo_image'])): ?>
                                <div class="logo-preview-container">
                                    <p class="text-muted-small mb-2"><?= t_admin('admin.cfg.current_logo') ?></p>
                                    <img src="<?= $config['logo_image'] ?>" style="max-height: 40px; display: block; margin: 0 auto;">
                                    <input type="hidden" name="current_logo_image" value="<?= $config['logo_image'] ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Appearance -->
        <h3 class="admin-section-header"><i class="fa-solid fa-palette"></i> <?= t_admin('admin.cfg.sec.appearance') ?></h3>
        <div class="admin-section-container">
            <div class="form-group">
                <label class="form-label"><?= t_admin('admin.cfg.template') ?></label>
                <div class="template-input-group">
                    <input type="text" class="form-control" style="width: auto; flex: 1; max-width: 300px;" value="<?= ucfirst($config['template'] ?? 'basic') ?>" readonly disabled>
                    <select name="template" class="form-select" style="flex: 2; max-width: 400px;">
                        <?php foreach ($templates as $tmpl): ?>
                            <option value="<?= $tmpl['value'] ?>" <?= ($config['template'] ?? 'basic') === $tmpl['value'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tmpl['label']) ?> <?= t_admin('admin.cfg.theme_suffix') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <small class="help-text"><?= t_admin('admin.cfg.template_help') ?></small>
            </div>
        </div>

        <!-- Section 3: Language Settings -->
        <h3 class="admin-section-header"><i class="fa-solid fa-language"></i> <?= t_admin('admin.cfg.sec.lang') ?></h3>
        <div class="admin-section-container">
            <div class="admin-grid">
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.cfg.site_lang') ?></label>
                    <select name="site_lang" class="form-select">
                        <?php foreach ($supportedLangs as $code => $label): ?>
                            <option value="<?= $code ?>" <?= ($config['site_lang'] ?? 'ko') === $code ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted-small"><?= t_admin('admin.cfg.site_lang_help') ?></small>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.cfg.admin_lang') ?></label>
                    <select name="admin_lang" class="form-select">
                        <?php foreach ($supportedLangs as $code => $label): ?>
                            <option value="<?= $code ?>" <?= ($config['admin_lang'] ?? 'ko') === $code ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted-small"><?= t_admin('admin.cfg.admin_lang_help') ?></small>
                </div>
            </div>
        </div>
        
        <!-- Section 4: Member Info -->
        <h3 class="admin-section-header"><i class="fa-solid fa-user-plus"></i> <?= t_admin('admin.cfg.sec.member') ?></h3>
        <div class="admin-section-container">
            <div class="admin-grid">
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.cfg.join_point') ?></label>
                    <input type="number" name="join_point" class="form-control" value="<?= htmlspecialchars($config['join_point'] ?? 0) ?>" placeholder="<?= htmlspecialchars(t_admin('admin.cfg.join_point_ph')) ?>">
                    <small class="text-muted-small"><?= t_admin('admin.cfg.join_point_help') ?></small>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.cfg.join_level') ?></label>
                    <select name="join_level" class="form-select">
                        <?php for($i=1; $i<=9; $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($config['join_level']) && $config['join_level'] == $i) ? 'selected' : '' ?>><?= t_admin('admin.cfg.level_prefix') ?> <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <small class="text-muted-small"><?= t_admin('admin.cfg.join_level_help') ?></small>
                </div>
            </div>
        </div>

        <!-- Section 5: Company Info  -->
        <h3 class="admin-section-header"><i class="fa-solid fa-building"></i> <?= t_admin('admin.cfg.sec.company') ?></h3>
        <div class="admin-section-container">
            <div class="admin-grid">
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.cfg.company_name') ?></label>
                    <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($config['company_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.cfg.company_owner') ?></label>
                    <input type="text" name="company_owner" class="form-control" value="<?= htmlspecialchars($config['company_owner'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.cfg.company_license') ?></label>
                    <input type="text" name="company_license_num" class="form-control" value="<?= htmlspecialchars($config['company_license_num'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.cfg.company_tel') ?></label>
                    <input type="text" name="company_tel" class="form-control" value="<?= htmlspecialchars($config['company_tel'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.cfg.company_email') ?></label>
                    <input type="email" name="company_email" class="form-control" value="<?= htmlspecialchars($config['company_email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.cfg.company_address') ?></label>
                    <input type="text" name="company_address" class="form-control" value="<?= htmlspecialchars($config['company_address'] ?? '') ?>">
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    $('#link-config').addClass('active');

    function selectLogoType(type) {
        // Visual selection update
        $('.selection-card').removeClass('active');
        if (type === 'text') {
            $('.selection-card:first-child').addClass('active');
            $('#logo-text-box').slideDown(200);
            $('#logo-image-box').slideUp(200);
            // Check the radio input
            $('input[name="logo_type"][value="text"]').prop('checked', true);
        } else {
            $('.selection-card:last-child').addClass('active');
            $('#logo-text-box').slideUp(200);
            $('#logo-image-box').slideDown(200);
            // Check the radio input
            $('input[name="logo_type"][value="image"]').prop('checked', true);
        }
    }


</script>

<?php include_admin_footer(); ?>
