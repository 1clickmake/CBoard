<?php $title = t_admin('admin.faq.title'); include_admin_header($title); ?>

<div class="glass-card">
    <!-- Category Settings Section -->
    <div>
        <div id="categoryConfigForm" class="glass-card mb-3" style="display: none; background: #f8fafc;">
            <form action="/admin/faq/config" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="form-group mb-3">
                    <label class="form-label"><?= t_admin('admin.faq.defined_cat') ?></label>
                    <input type="text" name="faq_category" id="faq_category_input" class="form-control" value="<?= htmlspecialchars($config_categories) ?>">
                    <small class="text-muted-small"><?= t_admin('admin.faq.cat_help') ?></small>
                </div>
                <button type="submit" class="btn btn-danger"><?= t_admin('admin.faq.save_cat') ?></button>
            </form>
        </div>
    </div>

    <div class="admin-header-flex">
        <h1><?= t_admin('admin.faq.heading') ?></h1>
        <div>
            <button class="btn btn-primary" onclick="$('#categoryConfigForm').slideToggle()"><?= t_admin('admin.faq.manage_cat') ?></button>
            <button class="btn btn-primary" onclick="$('#createFaqForm').slideToggle(); $('#editFaqForm').slideUp();"><?= t_admin('admin.faq.new') ?></button>
        </div>
    </div>

    <!-- Create FAQ Form -->
    <div id="createFaqForm" class="glass-card" style="display: none; background: #f8fafc; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;"><?= t_admin('admin.faq.add_title') ?></h2>
        <form action="/admin/faq/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="faq_category_config" id="hidden_faq_category_config">
            <div style="margin-bottom: 1.5rem;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label"><?= t_admin('admin.faq.category') ?></label>
                            <select name="category" id="faq_category_select" class="form-select" required>
                                <option value=""><?= t_admin('admin.faq.cat_select') ?></option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars(trim($cat)) ?>"><?= htmlspecialchars(trim($cat)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label"><?= t_admin('admin.faq.order') ?></label>
                            <input type="number" name="display_order" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label"><?= t_admin('admin.faq.question') ?></label>
                    <input type="text" name="question" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label"><?= t_admin('admin.faq.answer') ?></label>
                    <textarea name="answer" class="form-control" rows="5" required></textarea>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" onclick="syncCategoryConfig('create')"><?= t_admin('admin.faq.create_btn') ?></button>
                <button type="button" class="btn btn-cancel-light" onclick="$('#createFaqForm').slideUp()"><?= t_admin('admin.common.cancel') ?></button>
            </div>
        </form>
    </div>

    <!-- Edit FAQ Form -->
    <div id="editFaqForm" class="glass-card" style="display: none; background: #f8fafc; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;"><?= t_admin('admin.faq.edit_title') ?></h2>
        <form action="/admin/faq/update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="id" id="edit-id">
            <div style="margin-bottom: 1.5rem;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label"><?= t_admin('admin.faq.category') ?></label>
                            <select name="category" id="edit-category" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars(trim($cat)) ?>"><?= htmlspecialchars(trim($cat)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label"><?= t_admin('admin.faq.order') ?></label>
                            <input type="number" name="display_order" id="edit-order" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label"><?= t_admin('admin.faq.question') ?></label>
                    <input type="text" name="question" id="edit-question" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label"><?= t_admin('admin.faq.answer') ?></label>
                    <textarea name="answer" id="edit-answer" class="form-control" rows="5" required></textarea>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary"><?= t_admin('admin.common.save_changes') ?></button>
                <button type="button" class="btn btn-cancel-light" onclick="$('#editFaqForm').slideUp()"><?= t_admin('admin.common.cancel') ?></button>
            </div>
        </form>
    </div>

    <!--//category sort-->
    <div class="category-nav mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="/admin/faq" class="btn <?= empty($currentCategory) ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm"><?= t_admin('admin.faq.all') ?></a>
            <?php foreach ($categories as $cat): $cat = trim($cat); if (!$cat) continue; ?>
                <a href="/admin/faq?category=<?= urlencode($cat) ?>" 
                   class="btn <?= $currentCategory === $cat ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
                    <?= htmlspecialchars($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table tn-list table-hover text-center">
            <thead>
                <tr>
                    <th style="width: 100px;"><?= t_admin('admin.faq.col_order') ?></th>
                    <th style="width: 120px;"><?= t_admin('admin.faq.category') ?></th>
                    <th><?= t_admin('admin.faq.question') ?></th>
                    <th style="width: 150px;"><?= t_admin('admin.faq.col_date') ?></th>
                    <th style="width: 120px;"><?= t_admin('admin.common.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($faqs)): ?>
                    <tr>
                        <td colspan="6" class="p-5 text-muted"><?= t_admin('admin.faq.empty') ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($faqs as $faq): ?>
                        <tr>
                            <td><?= $faq['display_order'] ?></td>
                            <td>
                                <span class="badge-role" style="background: rgba(99, 102, 241, 0.2); border: 1px solid rgba(99, 102, 241, 0.5); color: #818cf8;">
                                    <?= htmlspecialchars($faq['category']) ?>
                                </span>
                            </td>
                            <td class="text-start" style="max-width: 400px; cursor: pointer;" onclick="$(this).closest('tr').next('.faq-answer-row').toggle();">
                                <div style="font-weight: 600;"><?= htmlspecialchars($faq['question']) ?></div>
                                <div class="text-muted-small text-truncate" style="margin-top: 4px;"><?= t_admin('admin.faq.expand') ?></div>
                            </td>
                            <td class="text-muted-small"><?= date('Y-m-d', strtotime($faq['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 1rem; justify-content: center;">
                                    <button onclick='editFaq(<?= json_encode($faq) ?>)' style="background: none; border: none; color: var(--primary); cursor: pointer; font-size: 0.8rem;"><?= t_admin('admin.common.edit') ?></button>
                                    <form action="/admin/faq/delete" method="POST" onsubmit="return confirm(<?= htmlspecialchars(json_encode(t_admin('admin.faq.delete_confirm')), ENT_QUOTES) ?>)" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="id" value="<?= $faq['id'] ?>">
                                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.8rem;"><?= t_admin('admin.common.delete') ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="faq-answer-row" style="display: none; background: #f8fafc;">
                            <td colspan="6" class="text-start p-4">
                                <div style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 8px; border-left: 4px solid var(--primary);">
                                    <div style="font-weight: 700; margin-bottom: 0.5rem; color: var(--primary);"><?= t_admin('admin.faq.answer_label') ?></div>
                                    <div style="line-height: 1.6; color: #cbd5e1;"><?= nl2br(htmlspecialchars($faq['answer'])) ?></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?= get_pagination($page, $totalPages, $currentCategory ? "category=" . urlencode($currentCategory) : "") ?>
</div>

<script>
    $('#link-faq').addClass('active');

    function editFaq(faq) {
        $('#createFaqForm').slideUp();
        $('#edit-id').val(faq.id);
        $('#edit-category').val(faq.category);
        $('#edit-question').val(faq.question);
        $('#edit-answer').val(faq.answer);
        $('#edit-order').val(faq.display_order);
        $('#editFaqForm').slideDown();
        $('html, body').animate({ scrollTop: $('#editFaqForm').offset().top - 100 }, 'slow');
    }

    function syncCategoryConfig(mode) {
        $('#hidden_faq_category_config').val($('#faq_category_input').val());
    }

    // Dynamic Category List Update
    document.getElementById('faq_category_input').addEventListener('input', function() {
        const categories = this.value.split('|');
        const selects = ['#faq_category_select', '#edit-category'];
        
        selects.forEach(selector => {
            const select = document.querySelector(selector);
            const currentVal = select.value;
            
            select.innerHTML = selector === '#faq_category_select' ? '<option value="">Select Category...</option>' : '';
            categories.forEach(function(cat) {
                cat = cat.trim();
                if(cat) {
                    const option = document.createElement('option');
                    option.value = cat;
                    option.textContent = cat;
                    if(cat === currentVal) option.selected = true;
                    select.appendChild(option);
                }
            });
        });
    });
</script>

<?php include_admin_footer(); ?>
