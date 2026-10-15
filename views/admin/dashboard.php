<?php $title = 'Admin Dashboard'; include_admin_header($title); ?>

<div class="glass-card">
    <h1>Dashboard</h1>
    <p class="text-muted-small pb-2">한눈에 보는 사이트 현황입니다.</p>

    <?php do_action('admin_dashboard_before_grid'); ?>

    <div class="admin-grid" style="margin-top: 2rem;">
        <div class="glass-card" style="margin-bottom: 0; text-align: center;">
            <h3 class="text-primary-weight" style="font-size: 2rem;"><?= number_format($stats['users'] ?? 0) ?></h3>
            <p>회원</p>
            <a href="/admin/users" class="btn btn-sm btn-primary">관리</a>
        </div>
        <div class="glass-card" style="margin-bottom: 0; text-align: center;">
            <h3 class="text-primary-weight" style="font-size: 2rem;"><?= number_format($stats['posts'] ?? 0) ?></h3>
            <p>게시글</p>
            <a href="/admin/boards" class="btn btn-sm btn-primary">관리</a>
        </div>
        <div class="glass-card" style="margin-bottom: 0; text-align: center;">
            <h3 class="text-primary-weight" style="font-size: 2rem;"><?= number_format($stats['boards'] ?? 0) ?></h3>
            <p>게시판</p>
            <a href="/admin/boards" class="btn btn-sm btn-primary">관리</a>
        </div>
        <div class="glass-card" style="margin-bottom: 0; text-align: center;">
            <h3 class="text-primary-weight" style="font-size: 2rem;"><?= number_format($stats['today'] ?? 0) ?></h3>
            <p>오늘 방문자</p>
            <a href="/admin/visitors" class="btn btn-sm btn-primary">통계</a>
        </div>
    </div>

    <?php do_action('admin_dashboard_after_grid'); ?>
</div>

<script>
    $('#link-dashboard').addClass('active');
</script>

<?php include_admin_footer(); ?>
