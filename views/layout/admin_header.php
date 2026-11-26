<?php $adminLang = cm_current_lang('admin'); $supportedLangs = cm_supported_langs(); ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($adminLang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? t_admin('admin.brand') ?> - <?= t_admin('admin.panel_title') ?></title>
    <script>
        (function() {
            var t = localStorage.getItem('cm_admin_theme') || 'light';
            document.documentElement.setAttribute('data-admin-theme', t);
        })();
    </script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Admin Custom CSS -->
    <link href="/css/admin.css?v=<?php echo filemtime(CM_PUBLIC_PATH . '/css/admin.css'); ?>" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-4.0.0-beta.min.js"></script>
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    <?php do_action('admin_header_head'); ?>
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="sidebar">
        <a href="/admin" class="sidebar-brand">
            <span class="brand-mark"><i class="fa-solid fa-bolt"></i></span>
            <span><?= t_admin('admin.brand') ?></span>
        </a>

        <div class="sidebar-nav">
            <!-- Category 1: 커뮤니티 (Community) -->
            <div class="nav-category">
                <i class="fa-solid fa-users-gear"></i> <?= t_admin('admin.cat.community') ?>
            </div>
            <a href="/admin" id="link-dashboard">
                <i class="fa-solid fa-house"></i> <?= t_admin('admin.menu.dashboard') ?>
            </a>
            <a href="/admin/config" id="link-config">
                <i class="fa-solid fa-sliders"></i> <?= t_admin('admin.menu.config') ?>
            </a>
            <a href="/admin/users" id="link-users">
                <i class="fa-solid fa-user-group"></i> <?= t_admin('admin.menu.users') ?>
            </a>
            <a href="/admin/point" id="link-point">
                <i class="fa-solid fa-coins"></i> <?= t_admin('admin.menu.point') ?>
            </a>
            <a href="/admin/groups" id="link-groups">
                <i class="fa-solid fa-layer-group"></i> <?= t_admin('admin.menu.groups') ?>
            </a>
            <a href="/admin/boards" id="link-boards">
                <i class="fa-solid fa-list-check"></i> <?= t_admin('admin.menu.boards') ?>
            </a>
            <a href="/admin/faq" id="link-faq">
                <i class="fa-solid fa-question-circle"></i> <?= t_admin('admin.menu.faq') ?>
            </a>
            <a href="/admin/pages" id="link-pages">
                <i class="fa-solid fa-file-lines"></i> <?= t_admin('admin.menu.pages') ?>
            </a>
            <a href="/admin/visitors" id="link-visitors">
                <i class="fa-solid fa-chart-line"></i> <?= t_admin('admin.menu.visitors') ?>
            </a>
            <a href="/admin/mail" id="link-mail">
                <i class="fa-solid fa-envelope"></i> <?= t_admin('admin.menu.mail') ?>
            </a>

            
            <?php /*
            $pluginMenuItems = \App\Core\PluginManager::getInstance()->getAdminMenuItems();
            if (!empty($pluginMenuItems)): 
            ?>
            <div class="nav-category">Plugins</div>
            <?php foreach ($pluginMenuItems as $item): ?>
                <a href="<?= $item['url'] ?>" id="<?= $item['id'] ?>">
                    <i class="<?= $item['icon'] ?>"></i> <?= $item['title'] ?>
                </a>
            <?php endforeach; ?>
            <?php endif;
            */ ?>

            <div class="nav-category"><?= t_admin('admin.cat.system') ?></div>
            <a href="/" target="_blank" style="margin-top: auto; background: rgba(255,255,255,0.03);">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> <?= t_admin('admin.menu.live_site') ?>
            </a>
            <a href="/logout" style="color: #ef4444;">
                <i class="fa-solid fa-power-off"></i> <?= t_admin('admin.menu.logout') ?>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="mobile-toggle" onclick="$('#sidebar').toggleClass('active')">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h5 style="margin: 0; font-weight: 600;"><?= $title ?? t_admin('admin.menu.dashboard') ?></h5>
            </div>

            <div class="topbar-right" style="display:flex;align-items:center;gap:10px;">
                <select class="admin-lang-select" onchange="location.href='/lang/admin/' + this.value" aria-label="Admin Language">
                    <?php foreach ($supportedLangs as $code => $label): ?>
                        <option value="<?= $code ?>" <?= $adminLang === $code ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="adminThemeToggle" class="admin-theme-btn" onclick="toggleAdminTheme()">
                    <i class="fa-solid fa-moon" id="adminThemeIcon"></i>
                    <span id="adminThemeLabel"><?= t_admin('admin.theme.dark') ?></span>
                </button>
                <a href="/" target="_blank" class="tn-live-btn tn-live-btn-sm"><?= t_admin('admin.menu.live_site') ?> <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <div class="user-profile">
                    <div style="text-align: right; line-height: 1.2;">
                        <div style="font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($user['username'] ?? t_admin('admin.role.admin')) ?></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);"><?= $is_super ? t_admin('admin.role.super') : ($is_admin ? t_admin('admin.role.admin') : t_admin('admin.role.user')) ?></div>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username'] ?? 'A') ?>&background=6366f1&color=fff" alt="Profile">
                </div>
            </div>
        </header>
        <script>
            function syncAdminThemeBtn() {
                var cur = document.documentElement.getAttribute('data-admin-theme') || 'light';
                var icon = document.getElementById('adminThemeIcon');
                var label = document.getElementById('adminThemeLabel');
                if (icon) icon.className = (cur === 'dark') ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
                if (label) label.textContent = (cur === 'dark') ? <?= json_encode(t_admin('admin.theme.light')) ?> : <?= json_encode(t_admin('admin.theme.dark')) ?>;
            }
            function toggleAdminTheme() {
                var cur = document.documentElement.getAttribute('data-admin-theme') || 'light';
                var next = (cur === 'dark') ? 'light' : 'dark';
                document.documentElement.setAttribute('data-admin-theme', next);
                localStorage.setItem('cm_admin_theme', next);
                syncAdminThemeBtn();
            }
            syncAdminThemeBtn();
        </script>

        <div class="admin-content">
