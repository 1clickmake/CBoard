<?php
$title = '403 Forbidden';
include_header($title, $siteConfig ?? []);
?>
<div class="container" style="padding: 5rem 0; text-align: center;">
    <div style="font-size: 5rem; font-weight: 800; color: #e2e8f0; line-height: 1;">403</div>
    <h1 style="font-size: 1.5rem; font-weight: 800; margin: 1rem 0 0.5rem;">접근 권한이 없습니다</h1>
    <p style="color: var(--text-muted); margin-bottom: 2rem;"><?= htmlspecialchars($message ?? '이 페이지에 접근할 권한이 없습니다.') ?></p>
    <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
</div>
<?php include_footer($siteConfig ?? []); ?>
