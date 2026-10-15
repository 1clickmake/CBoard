<?php
$title = 'Home';
include_header($title, $siteConfig);
$siteName = $siteConfig['site_name'] ?? 'CMAKE';
$boardCount = 0;
foreach ($groups as $g) { $boardCount += count($g['boards'] ?? []); }
$firstBoardSlug = ($groups[0]['boards'][0]['slug'] ?? null);
?>

<div class="container">
    <!-- Hero -->
    <section class="basic-hero">
        <div class="basic-hero-inner">
            <span class="basic-hero-badge">COMMUNITY</span>
            <h1 class="basic-hero-title"><?= htmlspecialchars($siteName) ?></h1>
            <p class="basic-hero-desc">이야기를 나누고, 사진을 공유하고, 소식을 전하는 <?= htmlspecialchars($siteName) ?> 커뮤니티입니다.</p>
            <div class="basic-hero-actions">
                <?php if ($firstBoardSlug): ?>
                    <a href="/board/<?= htmlspecialchars($firstBoardSlug) ?>" class="btn basic-hero-btn-primary">
                        <i class="fa-solid fa-pen-nib"></i> 글 쓰러 가기
                    </a>
                <?php endif; ?>
                <a href="/faq" class="btn basic-hero-btn-ghost">
                    <i class="fa-solid fa-circle-question"></i> 자주 묻는 질문
                </a>
            </div>
            <div class="basic-hero-stats">
                <div class="basic-hero-stat">
                    <strong><?= number_format(count($groups)) ?></strong>
                    <span>그룹</span>
                </div>
                <div class="basic-hero-stat">
                    <strong><?= number_format($boardCount) ?></strong>
                    <span>게시판</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Boards -->
    <section class="basic-board-grid">
        <?php foreach ($groups as $group): ?>
            <div class="glass-card basic-board-card">
                <h2 class="basic-board-group"><?= htmlspecialchars($group['name']) ?></h2>
                <?php if (!empty($group['description'])): ?>
                    <p class="basic-board-desc"><?= htmlspecialchars($group['description']) ?></p>
                <?php endif; ?>

                <div class="basic-board-links">
                    <?php foreach ($group['boards'] as $board): ?>
                        <a href="/board/<?= $board['slug'] ?>" class="board-item-link basic-board-link">
                            <div>
                                <div class="basic-board-title"><?= htmlspecialchars($board['title']) ?></div>
                                <?php if (!empty($board['description'])): ?>
                                    <div class="basic-board-sub"><?= htmlspecialchars($board['description']) ?></div>
                                <?php endif; ?>
                            </div>
                            <i class="fa-solid fa-chevron-right basic-board-arrow"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($groups)): ?>
            <div class="glass-card" style="grid-column: 1 / -1; text-align: center; padding: 5rem;">
                <h3 style="color: var(--text-muted);">No categories available. Please log in as admin to create groups and boards.</h3>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- 최신글 리스트 섹션 (공지사항 & 자유게시판) -->
<div class="container" style="margin-top: 3rem; margin-bottom: 4rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2.5rem;">
        <div>
            <?php
                $board_slug = 'free';
                $limit = 5;
                include CM_VIEWS_PATH . '/partials/latest_posts.php';
            ?>
        </div>
    </div>
</div>

<!-- 최신 갤러리 섹션 -->
<div class="container" style="margin-bottom: 4rem;">
    <?php
        $board_slug = 'gallery';
        $limit = 4;
        include CM_VIEWS_PATH . '/partials/latest_gallery.php';
    ?>
</div>

<!-- 최신 블로그 섹션 -->
<div class="container" style="margin-bottom: 5rem;">
    <?php
        $board_slug = 'blog';
        $limit = 3;
        include CM_VIEWS_PATH . '/partials/latest_blog.php';
    ?>
</div>

<!-- 폼메일 섹션 -->
<div class="container">
    <?php include CM_VIEWS_PATH . '/partials/formmail.php'; ?>
</div>

<?php include_footer($siteConfig); ?>
