<?php
$title = '500 Server Error';
include_header($title, $siteConfig ?? []);
?>
<div class="container" style="padding: 5rem 0; text-align: center;">
    <div style="font-size: 5rem; font-weight: 800; color: #e2e8f0; line-height: 1;">500</div>
    <h1 style="font-size: 1.5rem; font-weight: 800; margin: 1rem 0 0.5rem;">일시적인 오류가 발생했습니다</h1>
    <p style="color: var(--text-muted); margin-bottom: 2rem;"><?= htmlspecialchars($message ?? '잠시 후 다시 시도해 주세요.') ?></p>
    <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
</div>
<?php include_footer($siteConfig ?? []); ?>
