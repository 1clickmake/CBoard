<?php
$now = time();
$isMain = !empty($GLOBALS['sebyeol_is_main']);
?>
<footer class="footer">
    <div class="f_logo"></div>
    <div class="f_cont">
        <ul>
            <li>
                <p class="c_name"><?= htmlspecialchars(!empty($siteConfig['company_name']) ? $siteConfig['company_name'] : '주식회사 세별') ?></p>
                <p><span>대표이사 : </span><?= htmlspecialchars(!empty($siteConfig['company_owner']) ? $siteConfig['company_owner'] : '김영신') ?></p>
                <p><span>사업자등록번호 : </span><?= htmlspecialchars(!empty($siteConfig['company_license_num']) ? $siteConfig['company_license_num'] : '281-87-02777') ?></p>
            </li>
            <li>
                <p><span>주소 : </span><?= htmlspecialchars(!empty($siteConfig['company_address']) ? $siteConfig['company_address'] : '경기도 용인시 수지구 신봉2로 94-9, 1층(신봉동, 하우스 봄)') ?></p>
                <p><span>TEL : </span><?= htmlspecialchars(!empty($siteConfig['company_tel']) ? $siteConfig['company_tel'] : '010-2082-8798') ?></p>
                <p><span>E-mail : </span><?= htmlspecialchars(!empty($siteConfig['company_email']) ? $siteConfig['company_email'] : 'wstsr121@naver.com') ?></p>
            </li>
        </ul>
    </div>
    <div class="f_copy">Copyright &copy; <?= date('Y') ?> SEBYEOL All Rights Reserved</div>
</footer>

<div class="progress-wrap" aria-label="맨 위로 이동">
    <svg class="arrow-icon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12 17V7"></path>
        <path d="M7 12l5-5 5 5"></path>
    </svg>
    <svg class="progress-circle" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"/>
    </svg>
</div>

<script type="text/javascript" src="/sebyeol/js/mobile-responsive.js?t=<?= $now ?>"></script>
<script type="text/javascript" src="/sebyeol/js/default.js?t=<?= $now ?>"></script>
<?php if (!$isMain): ?>
    <script type="text/javascript" src="/sebyeol/js/star.js?t=<?= $now ?>"></script>
<?php endif; ?>
</body>
</html>
