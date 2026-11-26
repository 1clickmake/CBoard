<?php
if (empty($siteConfig) || empty($siteConfig['template'])) {
    try {
        $siteConfig = \App\Core\Database::getInstance()->query("SELECT * FROM config WHERE id = 1")->fetch() ?: ($siteConfig ?? []);
    } catch (\Throwable $e) {
        $siteConfig = $siteConfig ?? [];
    }
}
$title = '404 Not Found';
include_header($title, $siteConfig);
$tpl = $siteConfig['template'] ?? 'basic';
?>
<?php if ($tpl === 'sebyeol'): ?>
<section class="container">
    <canvas id="stars" aria-hidden="true"></canvas>
    <div class="inner" style="padding:120px 20px 140px;text-align:center;">
        <div style="font-size:84px;font-weight:800;letter-spacing:-2px;color:#fff;line-height:1;">404</div>
        <h2 style="font-size:24px;font-weight:600;color:#f3f4f6;margin:20px 0 12px;">페이지를 찾을 수 없습니다.</h2>
        <p style="color:#9ca3af;font-size:15px;margin-bottom:34px;"><?= htmlspecialchars($message ?? '요청하신 페이지가 존재하지 않거나 이동되었습니다.') ?></p>
        <a href="/" style="display:inline-block;padding:13px 32px;background:#fff;color:#0b0f19;font-weight:700;font-size:14px;border-radius:999px;">홈으로 돌아가기</a>
    </div>
</section>
<?php else: ?>
<div class="container" style="padding: 5rem 0; text-align: center;">
    <div style="font-size: 5rem; font-weight: 800; color: #e2e8f0; line-height: 1;">404</div>
    <h1 style="font-size: 1.5rem; font-weight: 800; margin: 1rem 0 0.5rem;">페이지를 찾을 수 없습니다</h1>
    <p style="color: var(--text-muted); margin-bottom: 2rem;"><?= htmlspecialchars($message ?? '요청하신 페이지가 존재하지 않거나 이동되었습니다.') ?></p>
    <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
</div>
<?php endif; ?>
<?php include_footer($siteConfig ?? []); ?>
