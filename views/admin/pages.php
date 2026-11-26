<?php $title = t_admin('admin.pages.title'); include CM_LAYOUT_PATH . '/admin_header.php'; ?>

<div class="glass-card">
    <div class="admin-header-flex">
        <h1><?= t_admin('admin.pages.title') ?></h1>
        <a href="/admin/pages/create" class="btn btn-primary"><?= t_admin('admin.pages.new') ?></a>
    </div>

    <div class="table-responsive">
        <table class="table tn-list table-hover text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= t_admin('admin.dash.col.title') ?></th>
                    <th><?= t_admin('admin.pages.col_slug') ?></th>
                    <th><?= t_admin('admin.pages.col_url') ?></th>
                    <th><?= t_admin('admin.pages.col_updated') ?></th>
                    <th><?= t_admin('admin.common.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                <tr>
                    <td><?= $page['id'] ?></td>
                    <td class="text-primary-weight"><?= htmlspecialchars($page['title']) ?></td>
                    <td><code><?= htmlspecialchars($page['slug']) ?></code></td>
                    <td>
                        <a href="/page/<?= $page['slug'] ?>" target="_blank" class="text-muted-small" style="text-decoration: none; color: #60a5fa;">
                            /page/<?= $page['slug'] ?> <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem;"></i>
                        </a>
                    </td>
                    <td class="text-muted-small"><?= $page['updated_at'] ?></td>
                    <td>
                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <a href="/admin/pages/edit/<?= $page['id'] ?>" style="color: var(--primary); text-decoration: none; font-size: 0.9rem;">
                                <i class="fa-solid fa-pen-to-square"></i> <?= t_admin('admin.common.edit') ?>
                            </a>
                            <form action="/admin/pages/delete" method="POST" onsubmit="return confirm(<?= htmlspecialchars(json_encode(t_admin('admin.pages.delete_confirm')), ENT_QUOTES) ?>)" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $page['id'] ?>">
                                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.9rem; padding: 0;">
                                    <i class="fa-solid fa-trash"></i> <?= t_admin('admin.common.delete') ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($pages)): ?>
                <tr>
                    <td colspan="6" style="padding: 4rem;">
                        <p class="text-muted-small" style="margin: 0;"><?= t_admin('admin.pages.empty') ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $('#link-pages').addClass('active');
</script>

<?php include CM_LAYOUT_PATH . '/admin_footer.php'; ?>
