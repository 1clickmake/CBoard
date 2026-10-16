<?php $title = 'Admin Dashboard'; include_admin_header($title); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="tn-dash">
    <div class="tn-dash-head">
        <div>
            <h1>Dashboard</h1>
            <p>한눈에 보는 사이트 현황입니다.</p>
        </div>
        <a href="/" target="_blank" class="tn-live-btn">Live Site <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
    </div>

    <?php do_action('admin_dashboard_before_grid'); ?>

    <!-- Stat cards -->
    <div class="tn-stats">
        <?php
        $cards = [
            ['label' => 'Total Members', 'value' => $stats['users'] ?? 0, 'sub' => '누적 회원', 'icon' => 'fa-users', 'link' => '/admin/users', 'linkText' => '관리'],
            ['label' => 'Total Posts', 'value' => $stats['posts'] ?? 0, 'sub' => '게시판 ' . number_format($stats['boards'] ?? 0) . '개', 'icon' => 'fa-pen-nib', 'link' => '/admin/boards', 'linkText' => '관리'],
            ['label' => 'Today Visitors', 'value' => $stats['today'] ?? 0, 'sub' => '어제 ' . number_format($stats['yesterday'] ?? 0) . '명', 'icon' => 'fa-chart-line', 'link' => '/admin/visitors', 'linkText' => '통계'],
            ['label' => 'Total Visitors', 'value' => $stats['total'] ?? 0, 'sub' => '누적 방문', 'icon' => 'fa-globe', 'link' => '/admin/visitors', 'linkText' => '통계'],
        ];
        foreach ($cards as $i => $c):
        ?>
        <div class="tn-stat tn-stat-<?= $i % 4 ?>">
            <div class="tn-stat-top">
                <div>
                    <div class="tn-stat-label"><?= htmlspecialchars($c['label']) ?></div>
                    <div class="tn-stat-value"><?= number_format($c['value']) ?></div>
                    <div class="tn-stat-sub"><?= htmlspecialchars($c['sub']) ?></div>
                </div>
                <div class="tn-stat-icon"><i class="fa-solid <?= $c['icon'] ?>"></i></div>
            </div>
            <svg class="tn-spark" viewBox="0 0 200 48" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,38 C25,34 35,20 55,24 C75,28 85,10 105,14 C125,18 135,30 155,26 C175,22 190,8 200,10 L200,48 L0,48 Z" fill="rgba(255,255,255,0.22)"></path>
                <path d="M0,38 C25,34 35,20 55,24 C75,28 85,10 105,14 C125,18 135,30 155,26 C175,22 190,8 200,10" fill="none" stroke="rgba(255,255,255,0.85)" stroke-width="2"></path>
            </svg>
            <a href="<?= $c['link'] ?>" class="tn-stat-link"><?= htmlspecialchars($c['linkText']) ?> <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <?php endforeach; ?>
    </div>

    <?php do_action('admin_dashboard_after_grid'); ?>

    <!-- Chart -->
    <div class="tn-panel">
        <div class="tn-panel-head">
            <div>
                <h3>Visitors Overview</h3>
                <p>최근 15일 방문자 추이</p>
            </div>
        </div>
        <div class="tn-chart-wrap"><canvas id="dashVisitorChart"></canvas></div>
    </div>

    <!-- Recent tables -->
    <div class="tn-cols">
        <div class="tn-panel">
            <div class="tn-panel-head">
                <div>
                    <h3>Recent Posts</h3>
                    <p>최근 작성된 게시글</p>
                </div>
                <a href="/admin/boards" class="tn-mini-link">Boards <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="table-responsive tn-table-wrap">
                <table class="tn-table">
                    <thead>
                        <tr><th>제목</th><th>게시판</th><th>작성자</th><th>날짜</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentPosts)): ?>
                            <?php foreach ($recentPosts as $p): ?>
                                <tr>
                                    <td><a href="/board/view/<?= (int)$p['id'] ?>" target="_blank"><?= htmlspecialchars(mb_substr($p['title'] ?? '', 0, 28)) ?></a></td>
                                    <td><span class="tn-pill"><?= htmlspecialchars($p['board_title'] ?? '-') ?></span></td>
                                    <td><?= htmlspecialchars($p['user_id'] ?? '') ?></td>
                                    <td><?= date('m-d H:i', strtotime($p['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="tn-empty">작성된 게시글이 없습니다.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tn-panel">
            <div class="tn-panel-head">
                <div>
                    <h3>New Members</h3>
                    <p>최근 가입 회원</p>
                </div>
                <a href="/admin/users" class="tn-mini-link">Users <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <ul class="tn-users">
                <?php if (!empty($recentUsers)): ?>
                    <?php foreach ($recentUsers as $u): ?>
                        <li>
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['username'] ?? '?') ?>&background=14b8a6&color=fff&size=64" alt="" loading="lazy">
                            <div>
                                <strong><?= htmlspecialchars($u['username'] ?? '') ?></strong>
                                <span><?= htmlspecialchars($u['user_id'] ?? '') ?> · Lv.<?= (int)($u['level'] ?? 1) ?></span>
                            </div>
                            <em><?= date('m-d', strtotime($u['created_at'])) ?></em>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="tn-empty">가입 회원이 없습니다.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<script>
    $('#link-dashboard').addClass('active');

    $(function() {
        const el = document.getElementById('dashVisitorChart');
        if (!el || typeof Chart === 'undefined') return;
        const daily = <?= json_encode($daily ?? []) ?>;
        const labels = daily.map(d => {
            const dt = new Date(d.visit_date);
            return (dt.getMonth() + 1) + '/' + dt.getDate();
        });
        new Chart(el, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '방문자 수',
                    data: daily.map(d => d.count),
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(20, 184, 166, 0.18)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.45,
                    pointBackgroundColor: '#0d9488',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 10
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.18)' }, ticks: { color: '#94a3b8', precision: 0, font: { size: 11 } } }
                }
            }
        });
    });
</script>

<?php include_admin_footer(); ?>
