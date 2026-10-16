<footer class="simple-footer">
    <div class="simple-inner">
        <div class="simple-footer-grid">
            <div class="simple-footer-brand">
                <a href="/" class="simple-footer-logo">
                    <?= htmlspecialchars(!empty($siteConfig['logo_text']) ? $siteConfig['logo_text'] : ($siteConfig['site_name'] ?? 'CMAKE')) ?>
                </a>
                <p class="simple-footer-desc">
                    <?= htmlspecialchars($siteConfig['site_name'] ?? 'CMAKE') ?> 커뮤니티에 오신 것을 환영합니다.
                </p>
            </div>

            <div class="simple-footer-nav">
                <h4>Legal &amp; Info</h4>
                <ul>
                    <?php if (!empty($footerPages)): ?>
                        <?php foreach ($footerPages as $fPage): ?>
                            <li><a href="/page/<?= $fPage['slug'] ?>"><?= htmlspecialchars($fPage['title']) ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="/page/about-us">About Us</a></li>
                        <li><a href="/page/terms-of-service">Terms of Service</a></li>
                        <li><a href="/page/privacy-policy">Privacy Policy</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="simple-footer-contact">
                <h4>Contact Us</h4>
                <ul>
                    <?php if (!empty($siteConfig['company_tel'])): ?>
                        <li><i class="fa-solid fa-phone"></i><span><?= htmlspecialchars($siteConfig['company_tel']) ?></span></li>
                    <?php endif; ?>
                    <?php if (!empty($siteConfig['company_email'])): ?>
                        <li><i class="fa-solid fa-envelope"></i><span><?= htmlspecialchars($siteConfig['company_email']) ?></span></li>
                    <?php endif; ?>
                    <?php if (!empty($siteConfig['company_address'])): ?>
                        <li><i class="fa-solid fa-location-dot"></i><span><?= htmlspecialchars($siteConfig['company_address']) ?></span></li>
                    <?php endif; ?>
                    <?php if (empty($siteConfig['company_tel']) && empty($siteConfig['company_email'])): ?>
                        <li><i class="fa-solid fa-circle-info"></i><span>Please update company info in admin.</span></li>
                    <?php endif; ?>
                </ul>
                <?php if (!empty($siteConfig['company_name']) || !empty($siteConfig['company_owner'])): ?>
                    <p class="simple-footer-meta">
                        <?php if (!empty($siteConfig['company_name'])): ?><?= htmlspecialchars($siteConfig['company_name']) ?><?php endif; ?>
                        <?php if (!empty($siteConfig['company_owner'])): ?> · 대표 <?= htmlspecialchars($siteConfig['company_owner']) ?><?php endif; ?>
                        <?php if (!empty($siteConfig['company_license_num'])): ?> · <?= htmlspecialchars($siteConfig['company_license_num']) ?><?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="simple-footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteConfig['site_name'] ?? 'CMAKE') ?>. All rights reserved.</span>
        </div>
    </div>
</footer>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content simple-contact-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">문의하기</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php global $csrf_token; ?>
                <?php $captcha_q = \App\Core\Captcha::generate(); ?>
                <?php include CM_VIEWS_PATH . '/partials/formmail.php'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php
include CM_LAYOUT_PATH . '/footer.php';
?>
