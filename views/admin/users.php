<?php $title = t_admin('admin.users.title'); include_admin_header($title); ?>

<div class="glass-card">
    <div class="admin-header-flex">
        <h1><?= t_admin('admin.users.title') ?></h1>
        <button class="btn btn-primary" onclick="$('#createUserForm').slideToggle(); $('#editUserForm').slideUp();"><?= t_admin('admin.users.new') ?></button>
    </div>

    <!-- Create User Form -->
    <div id="createUserForm" class="glass-card" style="display: none; background: #f8fafc; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;"><?= t_admin('admin.users.create_title') ?></h2>
        <form action="/admin/users/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.user_id') ?></label>
                    <input type="text" name="user_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.username') ?></label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.email') ?></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.role') ?></label>
                    <select name="role" class="form-select">
                        <option value="user"><?= t_admin('admin.users.role_user') ?></option>
                        <option value="admin"><?= t_admin('admin.users.role_admin') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.password') ?></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.country') ?></label>
                    <input type="text" name="country" class="form-control" placeholder="KR, US">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.point') ?></label>
                    <input type="number" name="point" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.level') ?></label>
                    <select name="level" class="form-select">
                        <?php for($i=1; $i<=10; $i++): ?>
                            <option value="<?= $i ?>" <?= $i === 1 ? 'selected' : '' ?>><?= t_admin('admin.cfg.level_prefix') ?> <?= $i ?> <?= $i === 10 ? t_admin('admin.users.super_admin') : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary"><?= t_admin('admin.users.create_btn') ?></button>
                <button type="button" class="btn btn-cancel-light" onclick="$('#createUserForm').slideUp()"><?= t_admin('admin.common.cancel') ?></button>
            </div>
        </form>
    </div>

    <div id="editUserForm" class="glass-card" style="display: none; background: #f8fafc; margin-bottom: 2rem;">
        <h2 id="editFormTitle" style="margin-bottom: 1.5rem;"><?= t_admin('admin.users.edit_title') ?></h2>
        <form action="/admin/users/update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="id" id="edit-id">
            <div class="admin-grid" style="margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.user_id') ?></label>
                    <input type="text" name="user_id" id="edit-userid" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.username') ?></label>
                    <input type="text" name="username" id="edit-username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.email') ?></label>
                    <input type="email" name="email" id="edit-email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.role') ?></label>
                    <select name="role" id="edit-role" class="form-select">
                        <option value="user"><?= t_admin('admin.users.role_user') ?></option>
                        <option value="admin"><?= t_admin('admin.users.role_admin') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.new_password') ?></label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.country') ?></label>
                    <input type="text" name="country" id="edit-country" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.point') ?></label>
                    <input type="number" name="point" id="edit-point" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= t_admin('admin.users.level') ?></label>
                    <select name="level" id="edit-level" class="form-select">
                        <?php for($i=1; $i<=10; $i++): ?>
                            <option value="<?= $i ?>"><?= t_admin('admin.cfg.level_prefix') ?> <?= $i ?> <?= $i === 10 ? t_admin('admin.users.super_admin') : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary"><?= t_admin('admin.common.save_changes') ?></button>
                <button type="button" class="btn btn-cancel-light" onclick="$('#editUserForm').slideUp()"><?= t_admin('admin.common.cancel') ?></button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table tn-list table-hover text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= t_admin('admin.users.user_id') ?></th>
                    <th><?= t_admin('admin.users.username') ?></th>
                    <th><?= t_admin('admin.users.email') ?></th>
                    <th><?= t_admin('admin.users.role') ?></th>
                    <th><?= t_admin('admin.users.point_level') ?></th>
                    <th><?= t_admin('admin.users.country') ?></th>
                    <th><?= t_admin('admin.users.joined_at') ?></th>
                    <th><?= t_admin('admin.common.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td class="text-primary-weight"><?= htmlspecialchars($user['user_id']) ?></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="badge-role" style="background: <?= $user['role'] === 'admin' ? '#0d9488; color: #fff;' : '#f1f5f9; color: #475569;' ?>">
                                <?= strtoupper($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="text-muted-small" style="line-height: 1.4;">
                                <span style="color: #b45309;"><i class="fa-solid fa-coins"></i> <?= number_format($user['point']) ?></span><br>
                                <span style="color: #2563eb;"><i class="fa-solid fa-shield"></i> LV.<?= $user['level'] ?></span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <img src="https://flagcdn.com/20x15/<?= strtolower($user['country'] === 'Unknown' ? 'un' : $user['country']) ?>.png" 
                                     onerror="this.src='https://flagcdn.com/20x15/un.png'"
                                     style="border-radius: 2px;">
                                <span class="text-muted-small"><?= htmlspecialchars($user['country']) ?></span>
                            </div>
                        </td>
                        <td class="text-muted-small"><?= $user['created_at'] ?></td>
                        <td>
                            <div style="display: flex; gap: 1rem; justify-content: center;">
                                <button onclick='editUser(<?= json_encode($user) ?>)' style="background: none; border: none; color: var(--primary); cursor: pointer; font-size: 0.8rem;"><?= t_admin('admin.common.edit') ?></button>
                                <form action="/admin/users/delete" method="POST" onsubmit="return confirm(<?= htmlspecialchars(json_encode(t_admin('admin.users.delete_confirm')), ENT_QUOTES) ?>)" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.8rem;"><?= t_admin('admin.common.delete') ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?= get_pagination($page, $totalPages) ?>
</div>

<script>
    $('#link-users').addClass('active');

    function editUser(user) {
        $('#createUserForm').slideUp();
        $('#edit-id').val(user.id);
        $('#edit-userid').val(user.user_id);
        $('#edit-username').val(user.username);
        $('#edit-email').val(user.email);
        $('#edit-role').val(user.role);
        $('#edit-country').val(user.country);
        $('#edit-point').val(user.point);
        $('#edit-level').val(user.level);
        $('#editUserForm').slideDown();
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }
</script>

<?php include_admin_footer(); ?>
