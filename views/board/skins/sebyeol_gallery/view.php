<?php
$title = $post['title'];
include_header($title, $siteConfig);

$slideImages = [];
if (!empty($files)) {
    foreach ($files as $file) {
        $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $slideImages[] = $file['filepath'];
        }
    }
}
if (empty($slideImages) && !empty($_raw['post']['content'])) {
    if (preg_match_all('/<img[^>]+src="([^">]+)"/i', $_raw['post']['content'], $m)) {
        $slideImages = $m[1];
    }
}
if (empty($slideImages)) {
    $slideImages[] = '/images/sub/a1.jpg';
}
?>
<section class="view-wrap" style="z-index:9999;">
    <div class="view-title" style="justify-content:space-between;">
        <h2 class="subject"><?= htmlspecialchars($post['title']) ?></h2>
        <div style="margin-left:auto;display:flex;align-items:center;gap:14px;">
            <?php if (!empty($is_member) && (($user['id'] ?? 0) == ($post['user_id'] ?? -1) || !empty($is_admin))): ?>
                <a href="/board/edit/<?= (int)$post['id'] ?>" style="color:#e5e7eb;font-size:13px;padding:5px 12px;border:1px solid rgba(255,255,255,0.28);border-radius:4px;">수정</a>
                <form action="/board/delete/<?= (int)$post['id'] ?>" method="POST" onsubmit="return confirm('삭제하시겠습니까?')" style="display:inline;margin:0;">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <button type="submit" style="background:rgba(239,68,68,0.18);border:1px solid rgba(239,68,68,0.45);color:#fca5a5;font-size:13px;padding:5px 12px;border-radius:4px;cursor:pointer;">삭제</button>
                </form>
            <?php endif; ?>
            <a href="/board/<?= htmlspecialchars($post['board_slug']) ?>" class="layer-popup-close" style="position:static;line-height:1;">&times;</a>
        </div>
    </div>

    <div class="slider-wrap">
        <div class="swiper-container swiper-detail" id="slider1">
            <div class="swiper-wrapper">
                <?php foreach ($slideImages as $img): ?>
                    <div class="swiper-slide"><div class="detail-image"><img src="<?= htmlspecialchars($img) ?>" alt=""></div></div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="swiper-product-button">
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>

        <div class="tab-wrap">
            <div class="swiper-container tab-ty1" id="tab1">
                <div class="swiper-wrapper">
                    <?php foreach ($slideImages as $img): ?>
                        <div class="swiper-slide"><div class="detail-thumb"><img src="<?= htmlspecialchars($img) ?>" alt=""></div></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    var galleryThumbs = new Swiper('#tab1', {
        spaceBetween: 5,
        slidesPerView: 'auto',
        freeMode: true,
        watchSlidesProgress: true,
        slideToClickedSlide: true,
        breakpoints: { 640: { spaceBetween: 10 } }
    });
    var galleryTop = new Swiper('#slider1', {
        spaceBetween: 0,
        slidesPerView: 1,
        autoHeight: true,
        navigation: {
            nextEl: '.slider-wrap .swiper-product-button .swiper-button-next',
            prevEl: '.slider-wrap .swiper-product-button .swiper-button-prev',
        },
        thumbs: { swiper: galleryThumbs },
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        }
    });
</script>
<?php include_footer($siteConfig); ?>
