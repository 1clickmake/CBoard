<?php $title = t_admin('admin.vis.title'); include_admin_header($title); ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="glass-card" style="margin-bottom: 2rem;">
    <div class="admin-header-flex">
        <div>
            <h1><?= t_admin('admin.vis.title') ?></h1>
            <p class="text-muted-small"><?= t_admin('admin.vis.subtitle') ?></p>
        </div>
        
        <!-- Cleanup Component -->
        <form action="/admin/visitors/cleanup" method="POST" onsubmit="return confirm(<?= htmlspecialchars(json_encode(t_admin('admin.vis.cleanup_confirm')), ENT_QUOTES) ?>)">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div style="display: flex; background: #f1f5f9; padding: 4px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <select name="period" class="form-select form-select-sm" style="background: transparent !important; border: none !important; width: auto;">
                    <option value="3" style=""><?= t_admin('admin.vis.m3') ?></option>
                    <option value="6" style=""><?= t_admin('admin.vis.m6') ?></option>
                    <option value="12" style=""><?= t_admin('admin.vis.m12') ?></option>
                </select>
                <button type="submit" class="btn btn-sm" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600;">
                    <i class="fa-solid fa-trash-can"></i> <?= t_admin('admin.vis.cleanup_btn') ?>
                </button>
            </div>
        </form>
    </div>

    <div class="admin-grid" style="margin-bottom: 2rem;">
        <div class="stat-card" style="background: #ffffff; padding: 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(15,23,42,0.05);">
            <div class="text-muted-small" style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 5px;">
                <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; display: inline-block; box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);"></span>
                <?= t_admin('admin.vis.live') ?>
            </div>
            <div style="font-size: 2rem; font-weight: 700; color: #10b981;"><?= number_format($stats['active']) ?></div>
        </div>
        <div class="stat-card" style="background: #ffffff; padding: 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(15,23,42,0.05);">
            <div class="text-muted-small" style="margin-bottom: 0.5rem;"><?= t_admin('admin.vis.today') ?></div>
            <div class="text-primary-weight" style="font-size: 2rem;"><?= number_format($stats['today']) ?></div>
        </div>
        <div class="stat-card" style="background: #ffffff; padding: 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(15,23,42,0.05);">
            <div class="text-muted-small" style="margin-bottom: 0.5rem;"><?= t_admin('admin.vis.yesterday') ?></div>
            <div style="font-size: 2rem; font-weight: 700; color: #0f172a;"><?= number_format($stats['yesterday']) ?></div>
        </div>
        <div class="stat-card" style="background: #ffffff; padding: 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(15,23,42,0.05);">
            <div class="text-muted-small" style="margin-bottom: 0.5rem;"><?= t_admin('admin.vis.total') ?></div>
            <div style="font-size: 2rem; font-weight: 700; color: #0f172a;"><?= number_format($stats['total']) ?></div>
        </div>
    </div>

    <div class="stat-card" style="background: #ffffff; padding: 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(15,23,42,0.05);">
        <h3 style="margin-bottom: 1.5rem; font-weight: 700;"><?= t_admin('admin.vis.trend') ?></h3>
        <div style="height: 300px; width: 100%;">
            <canvas id="visitorChart"></canvas>
        </div>
    </div>
</div>

<div class="glass-card" style="margin-bottom: 2rem;">
    <div class="admin-header-flex">
        <h1><?= t_admin('admin.vis.recent_logs') ?></h1>
        <p class="text-muted-small"><?= t_admin('admin.vis.recent_sub') ?></p>
    </div>

    <div class="table-responsive">
        <table class="table tn-list table-hover text-center">
            <thead>
                <tr>
                    <th><?= t_admin('admin.vis.col_time') ?></th>
                    <th><?= t_admin('admin.vis.col_ip') ?></th>
                    <th><?= t_admin('admin.vis.col_country') ?></th>
                    <th><?= t_admin('admin.vis.col_ua') ?></th>
                    <th><?= t_admin('admin.vis.col_ref') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentLogs as $log): ?>
                <tr>
                    <td>
                        <div class="text-light" style="font-size: 0.875rem;"><?= $log['visit_date'] ?></div>
                        <div class="text-muted-small"><?= $log['visit_time'] ?></div>
                    </td>
                    <td>
                        <span class="badge-role" style="background: rgba(13, 148, 136, 0.1); color: #0f766e; font-family: monospace;">
                            <?= $log['ip_address'] ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <img src="https://flagcdn.com/20x15/<?= strtolower($log['country'] === 'Unknown' ? 'un' : $log['country']) ?>.png" 
                                 onerror="this.src='https://flagcdn.com/20x15/un.png'"
                                 style="border-radius: 2px;">
                            <span class="text-muted-small"><?= $log['country'] ?></span>
                        </div>
                    </td>
                    <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['user_agent']) ?>">
                        <span class="text-muted-small"><?= htmlspecialchars($log['user_agent']) ?></span>
                    </td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['referer'] ?: 'Direct') ?>">
                        <span class="text-muted-small" style="color: #94a3b8;"><?= htmlspecialchars($log['referer'] ?: 'Direct') ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentLogs)): ?>
                <tr>
                    <td colspan="5" style="padding: 4rem 0;">
                        <i class="fas fa-info-circle text-muted" style="display: block; font-size: 2rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p class="text-muted-small"><?= t_admin('admin.vis.empty') ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?= get_pagination($page, $totalPages) ?>
</div>

<div class="glass-card" style="margin-bottom: 2rem;">
    <h3 class="admin-section-header">
        <i class="fa-solid fa-users-gear"></i> <?= t_admin('admin.vis.acl') ?>
    </h3>
    <form action="/admin/visitors/save-ips" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="admin-grid">
            <div>
                <label class="form-label">
                    <i class="fa-solid fa-check-circle" style="color: #10b981;"></i> <?= t_admin('admin.vis.allowed') ?>
                </label>
                <textarea name="allowed_ips" class="form-control" rows="5" style="font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($config['allowed_ips'] ?? '') ?></textarea>
                <small class="text-muted-small" style="margin-top: 0.5rem; display: block;"><?= t_admin('admin.vis.allowed_help') ?></small>
            </div>
            <div>
                <label class="form-label">
                    <i class="fa-solid fa-ban" style="color: #ef4444;"></i> <?= t_admin('admin.vis.blocked') ?>
                </label>
                <textarea name="blocked_ips" class="form-control" rows="5" style="font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($config['blocked_ips'] ?? '') ?></textarea>
                <small class="text-muted-small" style="margin-top: 0.5rem; display: block;"><?= t_admin('admin.vis.blocked_help') ?></small>
            </div>
        </div>
        <div style="margin-top: 2rem; text-align: right;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> <?= t_admin('admin.vis.save_rules') ?>
            </button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#link-visitors').addClass('active');

        const ctx = document.getElementById('visitorChart').getContext('2d');
        const dailyData = <?= json_encode($dailyData) ?>;
        
        // Helper to format date labels
        const labels = dailyData.map(d => {
            const date = new Date(d.visit_date);
            return (date.getMonth() + 1) + '/' + date.getDate();
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '접속자 수',
                    data: dailyData.map(d => d.count),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                        ticks: { color: '#64748b', font: { size: 11 }, stepSize: 1, padding: 10 }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#64748b', font: { size: 11 }, padding: 10 }
                    }
                }
            }
        });
    });
</script>


<?php include_admin_footer(); ?>
