<?php $title = 'My Page'; include_header($title, $siteConfig); ?>

<div class="container mypage-container">
    <div class="mypage-header-mb">
        <h1 class="mypage-header-title">My Page</h1>
        <div class="mypage-header-divider"></div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success mypage-alert-success">
            Profile updated successfully.
        </div>
    <?php endif; ?>

    <div class="mypage-grid">
        <!-- Left: User Info -->
        <div class="glass-card glass-card-start">
            <div class="mypage-profile-header">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=6366f1&color=fff&size=128" 
                     class="mypage-profile-img">
                <h3 class="mypage-profile-name"><?= htmlspecialchars($user['username'] ?? '') ?></h3>
                <span class="mypage-profile-role"><?= strtoupper($user['role'] ?? 'USER') ?></span>
            </div>

            <form action="/profile/update" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="mypage-form-group">
                    <label>User ID</label>
                    <input type="text" class="form-control mypage-input-disabled" value="<?= htmlspecialchars($user['user_id'] ?? '') ?>" disabled>
                </div>
                <div class="mypage-form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                </div>
                <div class="mypage-form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>
                <div class="mypage-form-group">
                    <label>Current Password (Required for changes)</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mypage-form-group">
                    <label>New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control" placeholder="Optional">
                </div>
                <div class="mypage-form-group">
                    <label>Country (Auto Detected)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['country'] ?? 'Unknown') ?>" disabled style="opacity: 0.6;">
                </div>
                
                <div class="mypage-btn-group">
                    <button type="submit" class="btn btn-primary btn-w-100">Update Profile</button>
                    <a href="/logout" class="btn btn-logout">Logout</a>
                </div>
            </form>

            <hr class="mypage-divider">

            <h3 class="mypage-section-title">Two-Factor Authentication</h3>
            <?php if (isset($_GET['2fa'])): ?>
                <div class="alert <?= in_array($_GET['2fa'], ['enabled']) ? 'alert-success' : 'alert-danger' ?> mypage-alert-success">
                    <?php if ($_GET['2fa'] === 'setup'): ?>Scan the code below with your authenticator app, then enter the 6-digit code.<?php endif; ?>
                    <?php if ($_GET['2fa'] === 'enabled'): ?>Two-factor authentication enabled.<?php endif; ?>
                    <?php if ($_GET['2fa'] === 'disabled'): ?>Two-factor authentication disabled.<?php endif; ?>
                    <?php if ($_GET['2fa'] === 'invalid'): ?>Invalid authentication code. Please try again.<?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($twoFaEnabled)): ?>
                <p class="text-muted-small">2FA is currently <strong>enabled</strong> on your account.</p>
                <form action="/profile/2fa/disable" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="mypage-form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-logout">Disable 2FA</button>
                </form>
            <?php elseif (!empty($twoFaSetup)): ?>
                <p class="text-muted-small">Add this secret to your authenticator app (Google/Microsoft Authenticator, 1Password):</p>
                <p><code><?= htmlspecialchars($twoFaSetup['secret']) ?></code></p>
                <p class="text-muted-small">Manual entry URI: <code><?= htmlspecialchars($twoFaSetup['uri']) ?></code></p>
                <form action="/profile/2fa/confirm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="mypage-form-group">
                        <label>6-digit Code</label>
                        <input type="text" name="otp" class="form-control" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary btn-w-100">Confirm & Enable</button>
                </form>
            <?php else: ?>
                <p class="text-muted-small">Protect your account with an authenticator app.</p>
                <form action="/profile/2fa/enable" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <button type="submit" class="btn btn-primary btn-w-100">Enable 2FA</button>
                </form>
            <?php endif; ?>

            <hr class="mypage-divider">

            <form action="/profile/delete" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete your account? This action cannot be undone.')">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="btn-delete-account">
                    Delete my account
                </button>
            </form>
        </div>

        <!-- Right: My Posts -->
        <div class="glass-card-container">
            <!-- Tabs for Right Column -->
            <ul class="nav nav-pills mb-4" id="mypage-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-posts">내가 쓴 글</button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Tab: My Posts -->
                <div class="tab-pane fade show active" id="tab-posts">
                    <h3 class="mypage-section-title">최근 작성 글 (최대 10개)</h3>
                    <div class="table-responsive">
                        <table class="table table-hover mypage-table">
                            <thead>
                                <tr>
                                    <th>게시판</th>
                                    <th>제목</th>
                                    <th>날짜</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><span class="mypage-badge"><?= htmlspecialchars($post['board_title']) ?></span></td>
                                    <td>
                                        <a href="/board/view/<?= $post['id'] ?>" class="mypage-post-link">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </td>
                                    <td class="mypage-post-date"><?= date('Y-m-d', strtotime($post['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="3" class="mypage-empty-state">
                                        <i class="fa-solid fa-pen-nib mypage-empty-icon"></i>
                                        작성한 게시글이 없습니다.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php include_footer($siteConfig); ?>
