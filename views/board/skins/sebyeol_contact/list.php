<?php
$title = $board['title'];
include_header($title, $siteConfig);
?>
<style>.boardList-top .search-box::before{display:none !important;}</style>
<section class="container">
    <canvas id="stars" aria-hidden="true"></canvas>
    <h2 class="title"><?= htmlspecialchars($board['title']) ?></h2>

    <div class="inner">
        <div class="boardList-top">
            <div class="col">
                <span class="total">총 <em class="concept-color"><?= number_format((int)($totalItems ?? $totalPosts ?? count($posts ?? []))) ?></em> 건</span>
            </div>
            <div class="col">
                <fieldset>
                    <legend class="blind">게시물 검색</legend>
                    <form name="fsearch" method="get" action="/board/<?= htmlspecialchars($board['slug']) ?>">
                        <div class="search">
                            <div class="search-box" style="padding-left:8px;">
                                <label for="stx" class="blind">검색어</label>
                                <input type="text" name="search" id="stx" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="검색어를 입력해 주세요">
                            </div>
                            <button type="submit" class="search-button">검 색</button>
                        </div>
                    </form>
                </fieldset>
            </div>
        </div>

        <div class="boardList-wrap">
            <ul class="board-head">
                <li>
                    <span class="num">No</span>
                    <span class="tit">제목</span>
                    <span class="name">작성자</span>
                    <span class="date">등록일</span>
                    <span class="status">진행상황</span>
                </li>
            </ul>
            <?php if (empty($posts)): ?>
                <ul class="nodata">
                    <li>등록된 게시물이 없습니다.</li>
                </ul>
            <?php else: ?>
                <ul class="board-list">
                    <?php
                    $totalCount = (int)($totalItems ?? $totalPosts ?? count($posts ?? []));
                    $curPage = max(1, (int)($page ?? 1));
                    $perPage = max(1, (int)($limit ?? $board['page_rows'] ?? 20));
                    $listNo = $totalCount - (($curPage - 1) * $perPage);
                    ?>
                    <?php foreach ($posts as $idx => $post): ?>
                        <?php
                        $rowNo = max(1, $listNo - $idx);
                        $commentCount = (int)($post['comment_count'] ?? 0);
                        $isDone = $commentCount > 0;
                        $isSecret = !empty($post['is_secret']);
                        $isNew = !empty($post['created_at']) && (time() - strtotime($post['created_at'])) <= 86400;
                        $hasFile = ((int)($post['file_count'] ?? 0)) > 0;
                        ?>
                        <li>
                            <a href="/board/view/<?= (int)$post['id'] ?>">
                                <span class="num"><?= !empty($post['is_notice']) ? '공지' : $rowNo ?></span>
                                <span class="tit">
                                    <?php if ($isNew): ?><span class="i_new" title="새 글"></span><?php endif; ?>
                                    <?php if ($isSecret): ?><span class="i_secret" title="비밀글"></span><?php endif; ?>
                                    <?= htmlspecialchars($post['title']) ?>
                                    <?php if ($hasFile): ?><span class="i_download_wh" style="vertical-align:middle;margin-left:6px;" title="첨부파일"></span><?php endif; ?>
                                    <?php if ($commentCount > 0): ?><span style="color:var(--concept-color, #b3926b);font-size:13px;font-weight:700;margin-left:6px;">[<?= $commentCount ?>]</span><?php endif; ?>
                                </span>
                                <span class="stit_mo">작성자</span>
                                <span class="name"><?= htmlspecialchars(!empty($post['username']) ? $post['username'] : (!empty($post['author_name']) ? $post['author_name'] : '손님')) ?></span>
                                <span class="stit_mo">등록일</span>
                                <span class="date"><?= date('Y.m.d', strtotime($post['created_at'])) ?></span>
                                <span class="status">
                                    <span class="qna-status <?= $isDone ? 'is-complete' : '' ?>"><?= $isDone ? '답변완료' : '답변대기' ?></span>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
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

        <div class="board-btn-group">
            <a href="/board/write/<?= htmlspecialchars($board['slug']) ?>" class="btn-write">글쓰기</a>
        </div>
    </div>
</section>
<?php include_footer($siteConfig); ?>
