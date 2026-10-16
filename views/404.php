<?php
$title = '404 Not Found';
include_header($title, $siteConfig ?? []);
?>
<div class="container" style="padding: 5rem 0; text-align: center;">
    <div style="font-size: 5rem; font-weight: 800; color: #e2e8f0; line-height: 1;">404</div>
    <h1 style="font-size: 1.5rem; font-weight: 800; margin: 1rem 0 0.5rem;">페이지를 찾을 수 없습니다</h1>
    <p style="color: var(--text-muted); margin-bottom: 2rem;"><?= htmlspecialchars($message ?? '요청하신 페이지가 존재하지 않거나 이동되었습니다.') ?></p>
    <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
</div>
<?php include_footer($siteConfig ?? []); ?>
