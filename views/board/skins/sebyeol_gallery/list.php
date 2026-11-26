<?php
$title = $board['title'];
include_header($title, $siteConfig);
?>
<script type="text/javascript" src="/sebyeol/js/masonry.pkgd.min.js"></script>
<section class="container">
    <h2 class="title"><?= htmlspecialchars($board['title']) ?></h2>

    <?php if (!empty($is_member)): ?>
        <div style="text-align:right;margin-bottom:20px;">
            <a href="/board/write/<?= htmlspecialchars($board['slug']) ?>" style="display:inline-block;padding:10px 22px;background:#fff;color:#111 !important;border-radius:4px;font-weight:600;">등록하기</a>
        </div>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
        <div style="text-align:center;padding:80px 20px;color:#999;">등록된 게시물이 없습니다.</div>
    <?php else: ?>
        <div class="gallery-grid-masonry">
            <?php
            $dbConn = \App\Core\Database::getInstance();
            $imgStmt = $dbConn->prepare("SELECT filepath FROM post_files WHERE post_id = ? AND (file_type LIKE 'image/%' OR LOWER(filepath) REGEXP '\\\\.(jpg|jpeg|png|gif|webp)$') ORDER BY id ASC LIMIT 1");
            ?>
            <?php foreach ($posts as $post): ?>
                <?php
                if (empty($post['first_file'])) {
                    $imgStmt->execute([(int)$post['id']]);
                    $fRow = $imgStmt->fetch();
                    if ($fRow && !empty($fRow['filepath'])) {
                        $post['first_file'] = $fRow['filepath'];
                    }
                }
                $thumb = get_thumbnail($post, '/images/sub/a1.jpg');
                $desc = trim(strip_tags(htmlspecialchars_decode($post['content'] ?? '')));
                $desc = mb_substr($desc, 0, 50, 'UTF-8');
                ?>
                <div class="art-card">
                    <div class="image-wrapper">
                        <a href="/board/view/<?= (int)$post['id'] ?>">
                            <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                            <div class="info">
                                <p class="gallery-name"><?= htmlspecialchars($post['title']) ?></p>
                                <p class="gallery-desc"><?= htmlspecialchars($desc) ?></p>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        $tp = max(1, (int)($totalPages ?? 1));
        $cp = max(1, (int)($page ?? 1));
        $sq = !empty($search) ? '&search=' . urlencode($search) : '';
        ?>
        <?php if ($tp > 1): ?>
        <div class="pagination">
            <a href="?page=1<?= $sq ?>" class="pagination-button pagination-first <?= $cp <= 1 ? 'disabled' : '' ?>">처음으로</a>
            <a href="?page=<?= max(1, $cp - 1) ?><?= $sq ?>" class="pagination-button pagination-prev <?= $cp <= 1 ? 'disabled' : '' ?>">이전</a>
            <?php for ($i = 1; $i <= $tp; $i++): ?>
                <a href="?page=<?= $i ?><?= $sq ?>" class="pagination-button <?= $i === $cp ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <a href="?page=<?= min($tp, $cp + 1) ?><?= $sq ?>" class="pagination-button pagination-next <?= $cp >= $tp ? 'disabled' : '' ?>">다음</a>
            <a href="?page=<?= $tp ?><?= $sq ?>" class="pagination-button pagination-last <?= $cp >= $tp ? 'disabled' : '' ?>">마지막으로</a>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
<script>
document.querySelectorAll('.gallery-grid-masonry').forEach((grid) => {
  const cards = [...grid.children].filter((item) => item.matches('.art-card'));
  function layoutCards() {
    const style = getComputedStyle(grid);
    const columnCount = parseInt(style.getPropertyValue('--gallery-columns'), 10) || 3;
    const gap = parseFloat(style.columnGap) || 20;
    const columnOffsets = Array(columnCount).fill(0);
    const cardHeights = cards.map((card) => card.getBoundingClientRect().height);
    cards.forEach((card, index) => {
      const column = index % columnCount;
      card.style.setProperty('--card-column', column + 1);
      card.style.setProperty('--card-offset', columnOffsets[column] + 'px');
      columnOffsets[column] += cardHeights[index] + gap;
    });
  }
  layoutCards();
  const observer = new ResizeObserver(layoutCards);
  cards.forEach((card) => observer.observe(card));
  window.addEventListener('resize', layoutCards);
  window.addEventListener('load', layoutCards);
});
</script>
<?php include_footer($siteConfig); ?>
