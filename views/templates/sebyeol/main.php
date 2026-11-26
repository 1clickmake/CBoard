<?php
$GLOBALS['sebyeol_is_main'] = true;
$title = 'Home';
include_header($title, $siteConfig);

// project 게시판 최근 게시물 3개 조회 ('별이 머무는 공간들')
$latestProjects = [];
try {
    $db = \App\Core\Database::getInstance();
    $stmt = $db->prepare("
        SELECT p.*,
               (SELECT filepath FROM post_files WHERE post_id = p.id AND (file_type LIKE 'image/%' OR LOWER(filepath) REGEXP '\\.(jpg|jpeg|png|gif|webp)$') ORDER BY id ASC LIMIT 1) as first_file
        FROM posts p
        INNER JOIN boards b ON p.board_id = b.id
        WHERE b.slug = 'project' AND p.reply_depth = 0
        ORDER BY p.id DESC
        LIMIT 3
    ");
    $stmt->execute();
    $latestProjects = $stmt->fetchAll();
} catch (\Throwable $e) {
    $latestProjects = [];
}

$fallbackImgs = [
    '/images/main/sec1-1.jpg',
    '/images/main/sec1-2.jpg',
    '/images/main/sec1-3.jpg',
];
?>

    <section class="main-visual" aria-label="세별">
        <div class="main-visual__background"></div>
        <canvas id="stars" aria-hidden="true"></canvas>
        <h3 class="main-visual-txt" data-aos="fade-in" data-aos-delay="500">It captures the light of <br>the universe into your space.</h3>
    </section>

    <section class="section-1">
        <div class="textGroup">
            <div class="leftArea">
                <p class="main-stit">Selected Works</p>
                <h3 class="main-tit">별이 머무는 공간들</h3>
            </div>
            <div class="rightArea"><p class="main-txt">주식회사 세별은 공간의 본질과 빛의 리듬을 탐구하여 시간이 흘러도 깊이를 잃지 않는 <br>하이엔드 인테리어 디자인을 선보입니다.</p></div>
        </div>
        <div class="imgGroup">
            <?php if (!empty($latestProjects)): ?>
                <?php foreach ($latestProjects as $idx => $proj): ?>
                    <?php
                    $thumb = get_thumbnail($proj, $fallbackImgs[$idx % 3]);
                    ?>
                    <a href="/board/view/<?= (int)$proj['id'] ?>" title="<?= htmlspecialchars($proj['title']) ?>">
                        <div class="img"><img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($proj['title']) ?>"></div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="/board/project"><div class="img"><img src="/images/main/sec1-1.jpg" alt="Selected Work 1"></div></a>
                <a href="/board/project"><div class="img"><img src="/images/main/sec1-2.jpg" alt="Selected Work 2"></div></a>
                <a href="/board/project"><div class="img"><img src="/images/main/sec1-3.jpg" alt="Selected Work 3"></div></a>
            <?php endif; ?>
        </div>
    </section>

    <section class="section-2" id="about-sec">
        <ul>
            <li>
                <p class="main-stit">Brand Essence</p>
                <h3 class="main-tit">"세상의 별, <br>당신만의 고유한 궤도를 <br>설계합니다."</h3>
                <p class="main-txt">세별(SEBYEOL)은 단순히 구조를 바꾸는 인테리어를 넘어, 공간에 머무는 <br>사람의 호흡과 삶의 서사를 별빛처럼 은은하게 조율합니다. <span>빛과 텍스쳐, <br>그리고 비움의 미학을 통해 가장 평온하면서도 고결한 공간을 선사합니다.</span></p>
            </li>
            <li>
                <div class="infoArea">
                    <div class="box">
                        <p class="num">01</p>
                        <p class="tit">Space Interior</p>
                        <p class="txt">하이엔드 주거, 펜트하우스, 상업 플래그십 스토어의 공간 기획 및 총괄 시공</p>
                    </div>
                    <div class="box">
                        <p class="num">02</p>
                        <p class="tit">Light &amp; Atmosphere</p>
                        <p class="txt">달빛과 별빛을 닮은 간접 조도 설계와 감각적인 머티리얼 큐레이션.</p>
                    </div>
                    <div class="box">
                        <p class="num">03</p>
                        <p class="tit">Branding &amp; Identity</p>
                        <p class="txt">공간과 브랜드의 세계관을 일관되게 엮어내는 공간 브랜딩 솔루션.</p>
                    </div>
                    <div class="box">
                        <p class="num">04</p>
                        <p class="tit">Bespoke Furniture</p>
                        <p class="txt">그 공간만을 위해 단 하나의 작품처럼 제작되는 맞춤형 가구 및 오브제.</p>
                    </div>
                </div>
            </li>
        </ul>
    </section>

<script>
    (() => {
    const canvas = document.getElementById('stars');
    if (!canvas) return;
    const section = canvas.parentElement;
    const ctx = canvas.getContext('2d');
    const IMAGE_WIDTH = 1920, IMAGE_HEIGHT = 1080;
    const COUNT = 300;
    let width = 0, height = 0, dpr = 1, stars = [], raf = 0;
    const rand = (a,b) => a + Math.random() * (b-a);
    function resize() {
        const rect = section.getBoundingClientRect();
        width = rect.width; height = rect.height;
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }
    function makeStars() {
        stars = Array.from({length: COUNT}, () => ({
        x: rand(20, 1900), y: rand(25, 760),
        radius: rand(0.75, 1.8),
        base: rand(0.07, 0.19), peak: rand(0.68, 0.98),
        speed: rand(0.00125, 0.0031), phase: rand(0, Math.PI * 2)
        }));
    }
    function render(time) {
        ctx.clearRect(0, 0, width, height);
        const scale = Math.max(width / IMAGE_WIDTH, height / IMAGE_HEIGHT);
        const offsetX = (width - IMAGE_WIDTH * scale) / 2;
        const offsetY = (height - IMAGE_HEIGHT * scale) / 2;
        for (const s of stars) {
        const x = offsetX + s.x * scale, y = offsetY + s.y * scale;
        if (x < 0 || x > width || y < 0 || y > height) continue;
        const wave = (Math.sin(time * s.speed + s.phase) + 1) / 2;
        const alpha = s.base + (s.peak - s.base) * Math.pow(wave, 2);
        ctx.beginPath();
        ctx.arc(x, y, Math.max(0.65, s.radius * Math.min(scale, 1.5)), 0, Math.PI * 2);
        ctx.fillStyle = `rgba(220,235,255,${alpha})`;
        ctx.fill();
        }
        raf = requestAnimationFrame(render);
    }
    resize(); makeStars();
    window.addEventListener('resize', resize, {passive: true});
    raf = requestAnimationFrame(render);
    })();
</script>

<?php include_footer($siteConfig); ?>
