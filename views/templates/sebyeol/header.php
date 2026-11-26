<?php
$now = time();
$siteTitle = !empty($title) ? htmlspecialchars($title) . ' | ' . htmlspecialchars($siteConfig['site_name'] ?? '세별') : htmlspecialchars($siteConfig['site_name'] ?? '세별');
?>
<!doctype html>
<html lang="ko">
<head>
<title><?= $siteTitle ?></title>
<meta charset="utf-8">
<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, width=device-width">
<meta name="author" content="세별">
<meta name="description" content="<?= htmlspecialchars($siteConfig['site_description'] ?? '세별 - 하이엔드 인테리어 디자인') ?>">
<meta name="keywords" content="세별, SEBYEOL, 인테리어, 공간디자인">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= $siteTitle ?>">

<link rel="SHORTCUT ICON" href="/images/favicon.ico">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/sebyeol/css/reset.css?t=<?= $now ?>" media="all">
<link rel="stylesheet" type="text/css" href="/sebyeol/css/font.css?t=<?= $now ?>" media="all">
<link rel="stylesheet" type="text/css" href="/sebyeol/css/style.css?t=<?= $now ?>" media="all">
<link rel="stylesheet" type="text/css" href="/sebyeol/css/responsive.css?t=<?= $now ?>" media="all">
<link rel="stylesheet" type="text/css" href="/sebyeol/css/animate.min.css?t=<?= $now ?>" media="all">
<link rel="stylesheet" type="text/css" href="/sebyeol/css/swiper.min.css?t=<?= $now ?>" media="all">
<link rel="stylesheet" type="text/css" href="/sebyeol/css/aos.css?t=<?= $now ?>" media="all">
<link rel="stylesheet" type="text/css" href="/sebyeol/css/iziModal.css?t=<?= $now ?>" media="all">

<script type="text/javascript" src="/sebyeol/js/jquery.min.js?t=<?= $now ?>"></script>
<script type="text/javascript" src="/sebyeol/js/front.js?t=<?= $now ?>"></script>
<script type="text/javascript" src="/sebyeol/js/iziModal.js?t=<?= $now ?>"></script>
<script type="text/javascript" src="/sebyeol/js/ui.min.js?t=<?= $now ?>"></script>
<script type="text/javascript" src="/sebyeol/js/aos.js?t=<?= $now ?>"></script>
<script type="text/javascript" src="/sebyeol/js/lenis.min.js?t=<?= $now ?>"></script>
<script type="text/javascript" src="/sebyeol/js/plugins.min.js?t=<?= $now ?>"></script>
<script type="text/javascript" src="/sebyeol/js/lenis.js?t=<?= $now ?>"></script>
<script type="text/javascript" src="/sebyeol/js/header_scroll.js"></script>
<?php if (function_exists('do_action')) { do_action('public_head'); } ?>
</head>
<body>
    <header class="">
        <section id="header">
            <h1 class="logoArea">
                <a href="/" class="logo"><span class="blind">logo</span></a>
            </h1>
            <!-- gnb -->
            <nav id="gnb" class="">
                <h2>메인메뉴</h2>
                <div class="gnb_wrap">
                    <ul id="gnb1_ul">
                        <li class="gnb1_li"><a href="/board/project" class="gnb1_a">Project</a></li>
                        <li class="gnb1_li"><a href="/board/branding" class="gnb1_a">Branding</a></li>
                        <li class="gnb1_li"><a href="/board/contact" class="gnb1_a">Contact</a></li>
                        <li class="gnb1_li"><a href="/#about-sec" class="gnb1_a">About</a></li>
                        <?php if (!empty($is_admin)): ?>
                            <li class="gnb1_li"><a href="/admin" class="gnb1_a">Admin</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
            <!-- // gnb -->
        </section>

        <div class="nav-but-wrap">
            <div class="menu-icon">
                <span class="menu-icon__line menu-icon__line-left"></span>
                <span class="menu-icon__line"></span>
                <span class="menu-icon__line menu-icon__line-right"></span>
            </div>
        </div>

        <div class="nav">
            <div class="nav__content" data-lenis-prevent-wheel>
                <div class="lnbtop"><div class="lnb_logo"><img src="/images/layout/logo-w.png" alt="세별"></div></div>
                <ul class="lnb_ul">
                    <li class="lnb__li"><a href="/">Home</a></li>
                    <li class="lnb__li"><a href="/board/project">Project</a></li>
                    <li class="lnb__li"><a href="/board/branding">Branding</a></li>
                    <li class="lnb__li"><a href="/board/contact">Contact</a></li>
                    <li class="lnb__li"><a href="/#about-sec">About</a></li>
                    <?php if (!empty($is_member)): ?>
                        <?php if (!empty($is_admin)): ?>
                            <li class="lnb__li"><a href="/admin">Admin</a></li>
                        <?php endif; ?>
                        <li class="lnb__li"><a href="/logout">Logout</a></li>
                    <?php else: ?>
                        <li class="lnb__li"><a href="/login">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>
