<?php $title = t_admin('admin.point.title'); include_admin_header($title); ?>

<div class="glass-card">
    <div class="admin-header-flex">
        <h1><?= t_admin('admin.point.title') ?></h1>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-danger" id="btn-bulk-delete" style="display: none;" onclick="bulkDelete()"><?= t_admin('admin.point.bulk_delete') ?></button>
            <button class="btn btn-primary" onclick="$('#adjustPointForm').slideToggle()"><?= t_admin('admin.point.adjust_btn') ?></button>
        </div>
    </div>

    <!-- Manual Point Adjustment Form -->
    <div id="adjustPointForm" class="glass-card" style="display: none; background: #f8fafc; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;"><?= t_admin('admin.point.adjust_btn') ?></h2>
        <form action="/admin/point/update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="admin-grid">
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.user_id') ?></label>
                    <input type="text" name="user_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.point') ?> (+/-)</label>
                    <input type="number" name="point" class="form-control" required placeholder="500 / -500">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.point.reason') ?></label>
                    <input type="text" name="rel_msg" class="form-control">
                </div>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary"><?= t_admin('admin.point.apply') ?></button>
                <button type="button" class="btn btn-cancel-light" onclick="$('#adjustPointForm').slideUp()"><?= t_admin('admin.common.cancel') ?></button>
            </div>
        </form>
    </div>

    <!-- Search Section -->
    <div class="glass-card" style="background: rgba(255, 255, 255, 0.03); margin-bottom: 2rem;">
        <form method="GET" action="/admin/point" style="display: flex; gap: 1rem;">
            <input type="text" name="search" class="form-control" placeholder="<?= htmlspecialchars(t_admin('admin.point.search_ph')) ?>" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary"><?= t_admin('admin.common.search') ?></button>
            <?php if ($search): ?>
                <a href="/admin/point" class="btn btn-cancel-light"><?= t_admin('admin.common.reset') ?></a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Point Log Table -->
    <div class="table-responsive">
        <table class="table tn-list table-hover text-center">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="check-all" class="form-check-input"></th>
                    <th>ID</th>
                    <th><?= t_admin('admin.users.user_id') ?></th>
                    <th><?= t_admin('admin.users.point') ?></th>
                    <th><?= t_admin('admin.point.col_reason') ?></th>
                    <th><?= t_admin('admin.point.col_date') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" style="padding: 3rem;">
                            <p class="text-muted-small" style="margin: 0;"><?= t_admin('admin.point.empty') ?></p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="<?= $log['id'] ?>" class="form-check-input log-checkbox"></td>
                            <td><?= $log['id'] ?></td>
                            <td>
                                <span class="badge-role" style="background: rgba(99, 102, 241, 0.2); color: var(--primary);">
                                    <?= htmlspecialchars($log['user_id']) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: <?= $log['point'] >= 0 ? '#10b981' : '#ef4444' ?>;">
                                    <?= $log['point'] >= 0 ? '+' : '' ?><?= number_format($log['point']) ?>
                                </strong>
                            </td>
                            <td class="text-start"><?= htmlspecialchars($log['rel_msg']) ?></td>
                            <td class="text-muted-small"><?= $log['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?= get_pagination($page, $totalPages, $search) ?>
</div>

<script>
$(document).ready(function() {
    // Check All functionality
    $('#check-all').on('change', function() {
        $('.log-checkbox').prop('checked', $(this).prop('checked'));
        toggleDeleteButton();
    });

    // Individual checkbox change
    $(document).on('change', '.log-checkbox', function() {
        if ($('.log-checkbox:checked').length === $('.log-checkbox').length) {
            $('#check-all').prop('checked', true);
        } else {
            $('#check-all').prop('checked', false);
        }
        toggleDeleteButton();
    });

    function toggleDeleteButton() {
        if ($('.log-checkbox:checked').length > 0) {
            $('#btn-bulk-delete').fadeIn();
        } else {
            $('#btn-bulk-delete').fadeOut();
        }
    }
});

function bulkDelete() {
    const selectedIds = [];
    $('.log-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) return;

    if (confirm('선택한 ' + selectedIds.length + '개 항목을 삭제하시겠습니까?')) {
        const form = $('<form action="/admin/point/bulk-delete" method="POST"></form>');
        form.append('<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">');
        selectedIds.forEach(id => {
            form.append('<input type="hidden" name="ids[]" value="' + id + '">');
        });
        $('body').append(form);
        form.submit();
    }
}
</script>

<?php include_admin_footer(); ?>
