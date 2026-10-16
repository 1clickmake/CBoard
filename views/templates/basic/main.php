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
            <span class="simple-hero-badge">누구나 무료로 쓰는 배포형 게시판</span>
            <h2>다운로드 한 번으로,<br>우리 커뮤니티 완성.</h2>
            <p>오픈소스 PHP 게시판 솔루션입니다.</p>
            <div class="simple-hero-btns">
                <?php if ($freeSlug): ?>
                    <a href="/board/<?= htmlspecialchars($freeSlug) ?>" class="simple-btn-primary">글 쓰러 가기</a>
                <?php endif; ?>
                <a href="<?= $gallerySlug ? '/board/gallery' : '/faq' ?>" class="simple-btn-ghost"><?= $gallerySlug ? '갤러리 보기' : '자주 묻는 질문' ?></a>
            </div>
            <div class="simple-hero-tags">
                <span>그룹 <?= number_format(count($groups)) ?></span>
                <span>게시판 <?= number_format($boardCount) ?></span>
            </div>
        </div>
        <div class="simple-hero-side">
            <?php if ($is_member): ?>
                <div class="simple-welcome-box">
                    <div class="simple-welcome-user">
                        <i class="fa-solid fa-circle-user"></i>
                        <div>
                            <strong><?= htmlspecialchars($user['username']) ?></strong>
                            <span><?= $is_super ? 'Super Admin' : ($is_admin ? 'Administrator' : 'Member') ?></span>
                        </div>
                    </div>
                    <div class="simple-welcome-links">
                        <?php if ($freeSlug): ?>
                            <a href="/board/<?= htmlspecialchars($freeSlug) ?>" class="simple-btn-primary simple-btn-sm">글쓰기</a>
                        <?php endif; ?>
                        <a href="/mypage" class="simple-btn-ghost simple-btn-sm">마이페이지</a>
                        <?php if ($is_admin): ?>
                            <a href="/admin" class="simple-btn-ghost simple-btn-sm">관리자</a>
                        <?php endif; ?>
                    </div>
                    <a href="/logout" class="simple-logout-link">로그아웃</a>
                </div>
            <?php else: ?>
                <form class="simple-login-box" action="/login" method="POST">
                    <h4>로그인</h4>
                    <p>간편하게 로그인하고 소통해 보세요.</p>
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <label class="sound-only" for="hero_user_id" style="display:none;">아이디</label>
                    <input type="text" id="hero_user_id" name="user_id" placeholder="아이디" required autocomplete="username">
                    <label class="sound-only" for="hero_pw" style="display:none;">비밀번호</label>
                    <input type="password" id="hero_pw" name="password" placeholder="비밀번호" required autocomplete="current-password">
                    <button type="submit">로그인</button>
                    <a href="/register" class="simple-join-link">아직 회원이 아니세요? 회원가입</a>
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
                    관리자로 로그인해 그룹과 게시판을 만들어 주세요.
                </div>
            <?php endif; ?>
        </aside>

        <div class="simple-content">
            <!-- 최신글 -->
            <section class="simple-section">
                <div class="simple-section-head">
                    <h3>최신 글</h3>
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
                    <h3>갤러리</h3>
                    <a href="/board/gallery" class="simple-more">더 보기 <i class="fa fa-angle-right" aria-hidden="true"></i></a>
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
                    <h3>블로그</h3>
                    <a href="/board/blog" class="simple-more">더 보기 <i class="fa fa-angle-right" aria-hidden="true"></i></a>
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
