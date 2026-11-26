<?php $title = t_admin('admin.groups.title'); include_admin_header($title); ?>

<div class="glass-card">
    <div class="admin-header-flex">
        <h1><?= t_admin('admin.groups.heading') ?></h1>
        <button class="btn btn-primary" onclick="$('#editGroupForm').hide(); $('#createGroupForm').slideToggle()"><?= t_admin('admin.groups.new') ?></button>
    </div>

    <!-- Create Group Form -->
    <div id="createGroupForm" class="glass-card" style="display: none; background: #f8fafc; margin-bottom: 2rem;">
		<h2 style="margin-bottom: 1.5rem;"><?= t_admin('admin.groups.create_title') ?></h2>
        <form action="/admin/groups" method="POST">
            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.groups.name') ?></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.groups.slug_opt') ?></label>
                    <input type="text" name="slug" class="form-control">
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label"><?= t_admin('admin.groups.desc') ?></label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= t_admin('admin.groups.create_btn') ?></button>
        </form>
    </div>

    <!-- Edit Group Form -->
    <div id="editGroupForm" class="glass-card" style="display: none; background: #f8fafc; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;"><?= t_admin('admin.groups.edit_title') ?></h2>
        <form action="/admin/groups/update" method="POST">
            <input type="hidden" name="id" id="edit-id">
            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.groups.name') ?></label>
                    <input type="text" name="name" id="edit-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.groups.slug') ?></label>
                    <input type="text" name="slug" id="edit-slug" class="form-control" required>
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label"><?= t_admin('admin.groups.desc') ?></label>
                <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary"><?= t_admin('admin.common.save_changes') ?></button>
                <button type="button" class="btn btn-cancel-light" onclick="$('#editGroupForm').slideUp()"><?= t_admin('admin.common.cancel') ?></button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table tn-list table-hover text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= t_admin('admin.groups.name') ?></th>
                    <th><?= t_admin('admin.groups.slug') ?></th>
                    <th><?= t_admin('admin.groups.desc') ?></th>
                    <th><?= t_admin('admin.common.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><?= $group['id'] ?></td>
                        <td class="text-primary-weight"><?= htmlspecialchars($group['name']) ?></td>
                        <td><code><?= htmlspecialchars($group['slug']) ?></code></td>
                        <td class="text-muted-small"><?= htmlspecialchars($group['description']) ?></td>
                        <td>
                            <div style="display: flex; gap: 1rem; justify-content: center;">
                                <button onclick='editGroup(<?= json_encode($group) ?>)' style="background: none; border: none; color: var(--primary); cursor: pointer; font-size: 0.8rem;"><?= t_admin('admin.common.edit') ?></button>
                                <form action="/admin/groups/delete" method="POST" onsubmit="return confirm(<?= htmlspecialchars(json_encode(t_admin('admin.groups.delete_confirm')), ENT_QUOTES) ?>)" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $group['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.8rem;"><?= t_admin('admin.common.delete') ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $('#link-groups').addClass('active');

    function editGroup(group) {
        $('#edit-id').val(group.id);
        $('#edit-name').val(group.name);
        $('#edit-slug').val(group.slug);
        $('#edit-description').val(group.description);
        $('#createGroupForm').slideUp();
        $('#editGroupForm').slideDown();
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }
</script>

<?php include_admin_footer(); ?>
