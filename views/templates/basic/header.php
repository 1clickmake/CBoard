<?php
include CM_LAYOUT_PATH . '/header.php';

// Ensure $groups is available
if (!isset($groups)) {
    $db = \App\Core\Database::getInstance();
    $groups = $db->query("SELECT * FROM board_groups ORDER BY id ASC")->fetchAll();
    foreach ($groups as &$group) {
        $stmt = $db->prepare("SELECT * FROM boards WHERE group_id = :id ORDER BY id ASC");
        $stmt->execute(['id' => $group['id']]);
        $group['boards'] = $stmt->fetchAll();
    }
}
$allBoards = [];
foreach ($groups as $gr) {
    foreach (($gr['boards'] ?? []) as $b) { $allBoards[] = $b; }
}
$logoChar = mb_substr($siteConfig['site_name'] ?? 'C', 0, 1, 'UTF-8');
?>
<header id="simple-hd">
    <!-- Utility topbar -->
    <div class="simple-topbar">
        <div class="simple-inner">
            <span class="simple-welcome"><?= htmlspecialchars($siteConfig['site_name'] ?? 'CMAKE') ?>에 오신 것을 환영합니다</span>
            <ul class="simple-topbar-right">
                <li><a href="/faq">FAQ</a></li>
                <?php if ($is_member): ?>
                    <?php if ($is_admin): ?><li><a href="/admin" class="simple-admin">관리자</a></li><?php endif; ?>
                    <li><a href="/mypage">정보수정</a></li>
                    <li><a href="/logout">로그아웃</a></li>
                <?php else: ?>
                    <li><a href="/register">회원가입</a></li>
                    <li><a href="/login" class="simple-login-link">로그인</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Logo bar -->
    <div class="simple-header-main">
        <div class="simple-inner">
            <div id="simple-logo">
                <a href="/" aria-label="<?= htmlspecialchars($siteConfig['site_name'] ?? 'CMAKE') ?>">
                    <?php if (($siteConfig['logo_type'] ?? 'text') === 'image' && !empty($siteConfig['logo_image'])): ?>
                        <img src="<?= sanitize_url($siteConfig['logo_image']) ?>" alt="<?= htmlspecialchars($siteConfig['site_name'] ?? '') ?>" class="simple-logo-img">
                    <?php else: ?>
                        <span class="simple-logo-mark"><?= htmlspecialchars($logoChar) ?></span>
                        <span class="simple-logo-text"><?= htmlspecialchars(!empty($siteConfig['logo_text']) ? $siteConfig['logo_text'] : ($siteConfig['site_name'] ?? 'CMAKE')) ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="simple-header-links">
                <?php if ($is_member): ?>
                    <button type="button" class="simple-btn-contact" data-bs-toggle="modal" data-bs-target="#contactModal">
                        <i class="fa-solid fa-paper-plane"></i> 문의하기
                    </button>
                    <a href="/mypage" class="simple-user-chip">
                        <i class="fa-solid fa-circle-user"></i> <?= htmlspecialchars($user['username']) ?>
                    </a>
                <?php else: ?>
                    <a href="/login" class="simple-btn-login">로그인</a>
                    <a href="/register" class="simple-btn-join">회원가입</a>
                    <button type="button" class="simple-btn-contact" data-bs-toggle="modal" data-bs-target="#contactModal">
                        <i class="fa-solid fa-paper-plane"></i> 문의하기
                    </button>
                <?php endif; ?>
            </div>

            <button class="simple-menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="메뉴 열기">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <!-- GNB -->
    <nav id="simple-gnb" aria-label="메인메뉴">
        <div class="simple-inner">
            <ul class="simple-gnb-list">
                <li class="simple-gnb-home"><a href="/" aria-label="홈"><i class="fa-solid fa-house"></i></a></li>
                <?php foreach ($allBoards as $board): ?>
                    <li class="simple-gnb-item">
                        <a href="/board/<?= $board['slug'] ?>"><?= htmlspecialchars($board['title']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</header>

<!-- Offcanvas Mobile Menu -->
<div class="offcanvas offcanvas-end simple-offcanvas" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileMenuLabel">MENU</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex flex-column">
            <?php if ($is_member): ?>
                <div class="simple-mobile-user">
                    <i class="fa-solid fa-circle-user"></i>
                    <div>
                        <div class="simple-mobile-name"><?= htmlspecialchars($user['username']) ?></div>
                        <div class="simple-mobile-role"><?= $is_super ? 'Super Admin' : ($is_admin ? 'Administrator' : 'Member') ?></div>
                    </div>
                </div>
                <div class="simple-mobile-auth">
                    <?php if ($is_admin): ?><a href="/admin" class="btn btn-sm btn-primary w-100">Admin</a><?php endif; ?>
                    <a href="/mypage" class="btn btn-sm btn-secondary w-100">My Page</a>
                    <a href="/logout" class="btn btn-sm btn-danger w-100">Logout</a>
                </div>
            <?php else: ?>
                <div class="simple-mobile-auth">
                    <a href="/login" class="btn btn-primary w-100">Login</a>
                    <a href="/register" class="btn btn-secondary w-100">Sign Up</a>
                </div>
            <?php endif; ?>

            <a href="/" class="mobile-nav-item"><i class="fa-solid fa-house"></i> Home</a>
            <a href="/faq" class="mobile-nav-item"><i class="fa-solid fa-question-circle"></i> FAQ</a>
            <button type="button" class="mobile-nav-item" data-bs-toggle="modal" data-bs-target="#contactModal" data-bs-dismiss="offcanvas" style="width: 100%; background: none; border: none; text-align: left; cursor: pointer; font-family: inherit;">
                <i class="fa-solid fa-paper-plane"></i> 문의하기
            </button>

            <?php foreach ($groups as $group): ?>
                <div class="simple-mobile-group"><?= htmlspecialchars($group['name']) ?></div>
                <?php foreach ($group['boards'] as $board): ?>
                    <a href="/board/<?= $board['slug'] ?>" class="mobile-nav-item">
                        <i class="fa-solid fa-list-ul"></i> <?= htmlspecialchars($board['title']) ?>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
