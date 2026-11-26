<?php
$title = 'Home';
include_header($title, $siteConfig);
$siteName = $siteConfig['site_name'] ?? 'CMAKE';
$boardCount = 0;
foreach ($groups as $g) { $boardCount += count($g['boards'] ?? []); }
$freeSlug = null; $gallerySlug = null;
foreach ($groups as $g) {
    foreach (($g['boards'] ?? []) as $b) {
        if ($freeSlug === null) $freeSlug = $b['slug'];
        if (($b['slug'] ?? '') === 'gallery') $gallerySlug = 'gallery';
    }
}
?>

<div class="simple-inner">
    <!-- 히어로 -->
    <section class="simple-hero">
        <div class="simple-hero-text">
            <span class="simple-hero-badge"><?= t('front.hero.badge') ?></span>
            <h2><?= t('front.hero.title') ?></h2>
            <p><?= t('front.hero.desc') ?></p>
            <div class="simple-hero-btns">
                <?php if ($freeSlug): ?>
                    <a href="/board/<?= htmlspecialchars($freeSlug) ?>" class="simple-btn-primary"><?= t('front.hero.go_write') ?></a>
                <?php endif; ?>
                <a href="<?= $gallerySlug ? '/board/gallery' : '/faq' ?>" class="simple-btn-ghost"><?= $gallerySlug ? t('front.hero.view_gallery') : t('front.hero.view_faq') ?></a>
            </div>
            <div class="simple-hero-tags">
                <span><?= t('front.hero.groups') ?> <?= number_format(count($groups)) ?></span>
                <span><?= t('front.hero.boards') ?> <?= number_format($boardCount) ?></span>
            </div>
        </div>
        <div class="simple-hero-side">
            <?php if ($is_member): ?>
                <div class="simple-welcome-box">
                    <div class="simple-welcome-user">
                        <i class="fa-solid fa-circle-user"></i>
                        <div>
                            <strong><?= htmlspecialchars($user['username']) ?></strong>
                            <span><?= $is_super ? t('admin.role.super') : ($is_admin ? t('admin.role.admin') : t('admin.role.user')) ?></span>
                        </div>
                    </div>
                    <div class="simple-welcome-links">
                        <?php if ($freeSlug): ?>
                            <a href="/board/<?= htmlspecialchars($freeSlug) ?>" class="simple-btn-primary simple-btn-sm"><?= t('front.hero.write') ?></a>
                        <?php endif; ?>
                        <a href="/mypage" class="simple-btn-ghost simple-btn-sm"><?= t('front.hero.mypage') ?></a>
                        <?php if ($is_admin): ?>
                            <a href="/admin" class="simple-btn-ghost simple-btn-sm"><?= t('front.admin') ?></a>
                        <?php endif; ?>
                    </div>
                    <a href="/logout" class="simple-logout-link"><?= t('front.logout') ?></a>
                </div>
            <?php else: ?>
                <form class="simple-login-box" action="/login" method="POST">
                    <h4><?= t('front.login') ?></h4>
                    <p><?= t('front.hero.login_desc') ?></p>
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <label class="sound-only" for="hero_user_id" style="display:none;"><?= t('front.hero.user_id') ?></label>
                    <input type="text" id="hero_user_id" name="user_id" placeholder="<?= htmlspecialchars(t('front.hero.user_id')) ?>" required autocomplete="username">
                    <label class="sound-only" for="hero_pw" style="display:none;"><?= t('front.hero.password') ?></label>
                    <input type="password" id="hero_pw" name="password" placeholder="<?= htmlspecialchars(t('front.hero.password')) ?>" required autocomplete="current-password">
                    <button type="submit"><?= t('front.login') ?></button>
                    <a href="/register" class="simple-join-link"><?= t('front.hero.no_account') ?></a>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <!-- 본문: 좌측 aside + 컨텐츠 -->
    <div class="simple-main-layout">
        <aside class="simple-aside">
            <?php foreach ($groups as $group): ?>
                <div class="simple-card simple-aside-card">
                    <h4 class="simple-card-title"><?= htmlspecialchars($group['name']) ?></h4>
                    <ul class="simple-card-links">
                        <?php foreach (($group['boards'] ?? []) as $board): ?>
                            <li>
                                <a href="/board/<?= $board['slug'] ?>">
                                    <?= htmlspecialchars($board['title']) ?>
                                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>

            <?php if (empty($groups)): ?>
                <div class="simple-card simple-aside-card" style="text-align: center;">
                    <?= t('front.aside.empty') ?>
                </div>
            <?php endif; ?>
        </aside>

        <div class="simple-content">
            <!-- 최신글 -->
            <section class="simple-section">
                <div class="simple-section-head">
                    <h3><?= t('front.latest.posts') ?></h3>
                </div>
                <?php
                    $board_slug = 'free';
                    $limit = 5;
                    include CM_VIEWS_PATH . '/partials/latest_posts.php';
                ?>
            </section>

            <!-- 갤러리 -->
            <section class="simple-section">
                <div class="simple-section-head">
                    <h3><?= t('front.latest.gallery') ?></h3>
                    <a href="/board/gallery" class="simple-more"><?= t('front.latest.more') ?> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                </div>
                <?php
                    $board_slug = 'gallery';
                    $limit = 4;
                    include CM_VIEWS_PATH . '/partials/latest_gallery.php';
                ?>
            </section>

            <!-- 블로그 -->
            <section class="simple-section">
                <div class="simple-section-head">
                    <h3>Blog</h3>
                    <a href="/board/blog" class="simple-more"><?= t('front.latest.more') ?> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                </div>
                <?php
                    $board_slug = 'blog';
                    $limit = 3;
                    include CM_VIEWS_PATH . '/partials/latest_blog.php';
                ?>
            </section>
        </div>
    </div>
</div>

<?php include_footer($siteConfig); ?>
