<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Csrf;
use PDO;

class AuthController extends BaseController {
    public function showLogin() {
        $is_member = \App\Services\AuthService::isMember();
        if ($is_member) {
            $this->redirect('/');
        }
        $failCount = (int)($_SESSION['login_fail_count'] ?? 0);
        $data = ['csrf_token' => Csrf::getToken()];
        if ($failCount >= 3) $data['captcha_q'] = \App\Core\Captcha::generate();
        $this->view('auth/login', $data);
    }

    private function completeLogin($user) {
        unset($_SESSION['rate_limit_login'], $_SESSION['login_fail_count'], $_SESSION['2fa_pending']);
        session_regenerate_id(true);
        unset($user['password']);
        // Refresh 2FA status from DB (may have changed)
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $user['two_factor_secret'] = $stmt->fetchColumn() ?: null;
        } catch (\Throwable $e) {}
        $_SESSION['user'] = $user;
        $this->redirect('/');
    }

    public function login() {
        // Rate limiting: max 5 attempts per minute
        if (!check_rate_limit('login', 5, 60)) {
            $this->view('auth/login', ['error' => 'Too many login attempts. Please try again in 1 minute.']);
            return;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->view('auth/login', ['error' => 'Invalid Security Token']);
            return;
        }

        // CAPTCHA after 3 failed attempts
        if ((int)($_SESSION['login_fail_count'] ?? 0) >= 3) {
            if (!\App\Core\Captcha::verify($_POST['captcha_answer'] ?? '')) {
                $this->view('auth/login', ['error' => 'CAPTCHA verification failed', 'captcha_q' => \App\Core\Captcha::generate()]);
                return;
            }
        }

        $userId = $_POST['user_id'] ?? '';
        $password = $_POST['password'] ?? '';

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            unset($_SESSION['login_fail_count']);
            // 2FA enabled? Defer full login until OTP verified
            if (!empty($user['two_factor_secret'])) {
                session_regenerate_id(true);
                $_SESSION['2fa_pending'] = (int)$user['id'];
                $this->redirect('/login/2fa');
                return;
            }
            $this->completeLogin($user);
        } else {
            $_SESSION['login_fail_count'] = (int)($_SESSION['login_fail_count'] ?? 0) + 1;
            $data = ['error' => 'Invalid User ID or password'];
            if ($_SESSION['login_fail_count'] >= 3) $data['captcha_q'] = \App\Core\Captcha::generate();
            $this->view('auth/login', $data);
        }
    }

    public function showTwoFactor() {
        if (empty($_SESSION['2fa_pending'])) $this->redirect('/login');
        $this->view('auth/2fa', ['csrf_token' => Csrf::getToken()]);
    }

    public function verifyTwoFactor() {
        $pendingId = (int)($_SESSION['2fa_pending'] ?? 0);
        if (!$pendingId) $this->redirect('/login');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->view('auth/2fa', ['error' => 'Invalid Security Token']);
            return;
        }
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$pendingId]);
        $user = $stmt->fetch();
        if (!$user || empty($user['two_factor_secret'])) {
            unset($_SESSION['2fa_pending']);
            $this->redirect('/login');
            return;
        }
        if (\App\Core\Totp::verify($user['two_factor_secret'], $_POST['otp'] ?? '')) {
            $this->completeLogin($user);
        } else {
            $this->view('auth/2fa', ['error' => 'Invalid authentication code']);
        }
    }

    public function showRegister() {
        $this->view('auth/register', ['csrf_token' => Csrf::getToken(), 'captcha_q' => \App\Core\Captcha::generate()]);
    }

    public function register() {
        if (!check_rate_limit('register', 5, 300)) {
            return $this->view('auth/register', ['error' => 'Too many registration attempts. Please try again later.']);
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->view('auth/register', ['error' => 'Invalid Security Token']);
            return;
        }
        if (!\App\Core\Captcha::verify($_POST['captcha_answer'] ?? '')) {
            return $this->view('auth/register', ['error' => 'CAPTCHA verification failed', 'captcha_q' => \App\Core\Captcha::generate()]);
        }

        $userId = $_POST['user_id'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $country = $_POST['country'] ?? 'Unknown';

        if ($password !== $confirm) {
            return $this->view('auth/register', ['error' => 'Passwords do not match']);
        }
        if (strlen($password) < 8) {
            return $this->view('auth/register', ['error' => 'Password must be at least 8 characters']);
        }
        if (!is_valid_email($email)) {
            return $this->view('auth/register', ['error' => 'Invalid email format']);
        }

        // Validate User ID
        $blacklistCheck = $this->validateUserId($userId);
        if ($blacklistCheck !== true) {
            return $this->view('auth/register', ['error' => $blacklistCheck]);
        }

        // Validate Username/Nickname
        $usernameCheck = $this->validateUsername($username);
        if ($usernameCheck !== true) {
            return $this->view('auth/register', ['error' => $usernameCheck]);
        }

        $db = Database::getInstance();
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        // Fetch default signup settings
        $config = $db->query("SELECT join_point, join_level FROM config LIMIT 1")->fetch();
        $defaultPoint = $config['join_point'] ?? 0;
        $defaultLevel = $config['join_level'] ?? 1;

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("INSERT INTO users (user_id, username, email, password, country, point, level) VALUES (:user_id, :username, :email, :password, :country, :point, :level)");
            $stmt->execute([
                'user_id' => $userId,
                'username' => $username,
                'email' => $email,
                'password' => $hashed,
                'country' => $country,
                'point' => $defaultPoint,
                'level' => $defaultLevel
            ]);

            // If signup points are given, record in point_log
            if ($defaultPoint > 0) {
                // We need to fetch the last insert ID if needed, but point_log uses user_id (the string ID)
                $stmt = $db->prepare("INSERT INTO point_log (user_id, point, rel_msg) VALUES (:user_id, :point, :rel_msg)");
                $stmt->execute([
                    'user_id' => $userId, 
                    'point' => $defaultPoint,
                    'rel_msg' => 'Signup Bonus'
                ]);
            }

            $db->commit();
            $this->redirect('/register?success=1');
        } catch (\PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            $this->view('auth/register', ['error' => 'User ID or Email already exists']);
        }
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'] ?? '/', $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
        }
        session_destroy();

        $this->redirect('/login');
    }

    public function mypage() {
        $is_member = \App\Services\AuthService::isMember();
        $user = \App\Services\AuthService::user();
        if (!$is_member) {
            $this->redirect('/login');
        }

        $db = Database::getInstance();
        $userId = $user['id'];
        $userStrId = $user['user_id'];

        // Get last 10 posts (posts.user_id stores the string user_id)
        $stmt = $db->prepare("
            SELECT p.*, b.title as board_title, b.slug as board_slug 
            FROM posts p 
            JOIN boards b ON p.board_id = b.id 
            WHERE p.user_id = :user_id 
            ORDER BY p.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute(['user_id' => $userStrId]);
        $posts = $stmt->fetchAll();

        // 2FA status
        $twoFaEnabled = !empty($user['two_factor_secret']);
        $twoFaSetup = null;
        if (!empty($_SESSION['2fa_setup_secret']) && !$twoFaEnabled) {
            $secret = $_SESSION['2fa_setup_secret'];
            $twoFaSetup = [
                'secret' => $secret,
                'uri' => \App\Core\Totp::provisioningUri($secret, $user['user_id'])
            ];
        }

        $this->view('auth/mypage', [
            'user' => $user,
            'posts' => $posts,
            'twoFaEnabled' => $twoFaEnabled,
            'twoFaSetup' => $twoFaSetup
        ]);
    }

    public function enableTwoFactor() {
        $user = \App\Services\AuthService::user();
        if (!\App\Services\AuthService::isMember()) $this->redirect('/login');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) $this->redirect('/mypage?error=csrf');
        $_SESSION['2fa_setup_secret'] = \App\Core\Totp::generateSecret();
        $this->redirect('/mypage?2fa=setup');
    }

    public function confirmTwoFactor() {
        $user = \App\Services\AuthService::user();
        if (!\App\Services\AuthService::isMember()) $this->redirect('/login');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) $this->redirect('/mypage?error=csrf');
        $secret = $_SESSION['2fa_setup_secret'] ?? '';
        if (!$secret || !\App\Core\Totp::verify($secret, $_POST['otp'] ?? '')) {
            $this->redirect('/mypage?2fa=invalid');
            return;
        }
        $db = Database::getInstance();
        // Ensure column exists (older installs)
        try {
            $stmt = $db->prepare("UPDATE users SET two_factor_secret = :s WHERE id = :id");
            $stmt->execute(['s' => $secret, 'id' => $user['id']]);
        } catch (\PDOException $e) {
            $db->exec("ALTER TABLE users ADD COLUMN two_factor_secret VARCHAR(64) DEFAULT NULL");
            $stmt = $db->prepare("UPDATE users SET two_factor_secret = :s WHERE id = :id");
            $stmt->execute(['s' => $secret, 'id' => $user['id']]);
        }
        unset($_SESSION['2fa_setup_secret']);
        $_SESSION['user']['two_factor_secret'] = $secret;
        $this->redirect('/mypage?2fa=enabled');
    }

    public function disableTwoFactor() {
        $user = \App\Services\AuthService::user();
        if (!\App\Services\AuthService::isMember()) $this->redirect('/login');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) $this->redirect('/mypage?error=csrf');
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($_POST['current_password'] ?? '', $row['password'])) {
            $this->redirect('/mypage?error=password');
            return;
        }
        $stmt = $db->prepare("UPDATE users SET two_factor_secret = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        unset($_SESSION['2fa_setup_secret']);
        $_SESSION['user']['two_factor_secret'] = null;
        $this->redirect('/mypage?2fa=disabled');
    }

    public function updateProfile() {
        $is_member = \App\Services\AuthService::isMember();
        $user = \App\Services\AuthService::user();
        if (!$is_member) $this->redirect('/login');

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->redirect('/mypage?error=csrf');
            return;
        }

        $db = Database::getInstance();
        $userId = $user['id'];
        
        $currentPassword = $_POST['current_password'] ?? '';
        
        // Verify current password
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $dbUser = $stmt->fetch();

        if (!$dbUser || !password_verify($currentPassword, $dbUser['password'])) {
            $this->redirect('/mypage?error=password');
            return;
        }

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($password) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET username = :username, email = :email, password = :password WHERE id = :id");
            $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashed, 'id' => $userId]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
            $stmt->execute(['username' => $username, 'email' => $email, 'id' => $userId]);
        }

        // Update session
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $fresh = $stmt->fetch();
        if ($fresh) { unset($fresh['password']); $_SESSION['user'] = $fresh; }


        $this->redirect('/mypage?updated=1');
    }

    public function deleteAccount() {
        $is_member = \App\Services\AuthService::isMember();
        $user = \App\Services\AuthService::user();
        if (!$is_member) $this->redirect('/login');

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->redirect('/mypage?error=csrf');
            return;
        }

        $db = Database::getInstance();
        $userId = $user['id'];

        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        session_destroy();
        $_SESSION = [];

        $this->redirect('/?deleted=1');
    }

    public function checkDuplicate() {
        $field = $_GET['field'] ?? '';
        $value = $_GET['value'] ?? '';

        if (!in_array($field, ['user_id', 'email', 'username'])) {
            return $this->json(['error' => 'Invalid field']);
        }

        if ($field === 'user_id') {
            $validation = $this->validateUserId($value);
            if ($validation !== true) {
                return $this->json(['exists' => true, 'message' => $validation]); // Use 'exists' true to block it on frontend
            }
        }
        
        if ($field === 'username') {
            $validation = $this->validateUsername($value);
            if ($validation !== true) {
                 return $this->json(['exists' => true, 'message' => $validation]);
            }
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $field = :value");
        $stmt->execute(['value' => $value]);
        $exists = $stmt->fetchColumn() > 0;

        return $this->json(['exists' => $exists]);
    }

    private function validateUserId($userId) {
        // 1. Minimum/Maximum length
        if (mb_strlen($userId, 'UTF-8') < 3 || mb_strlen($userId, 'UTF-8') > 20) {
            return 'ID must be between 3 and 20 characters.';
        }

        // 2. Character allowed: alphabets, numbers, hyphen, underscore
        // (Korean keys generally not used for system IDs, but if needed, add logic. Assuming English ID for now based on standard)
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $userId)) {
            return 'ID can only contain letters, numbers, hyphens, and underscores.';
        }

        // 3. Start with number or hyphen check
        if (preg_match('/^[0-9\-]/', $userId)) {
            return 'ID cannot start with a number or hyphen.';
        }
        
        // 4. Forbidden Names Blacklist
        $forbiddenNames = $this->getForbiddenNames();

        if (in_array(strtolower($userId), $forbiddenNames)) {
            return 'This ID is not allowed.';
        }
        
        // Partial match check for 'admin' (e.g., 'super_admin', 'admin_123')
        if (strpos(strtolower($userId), 'admin') !== false) {
             return 'This ID contains forbidden words.';
        }

        return true;
    }

    private function validateUsername($username) {
        $forbiddenNames = $this->getForbiddenNames();

        // Exact match check
        if (in_array(strtolower($username), $forbiddenNames)) {
            return 'This name is not allowed.';
        }
        
        // Partial match check for specific keywords
        foreach ($forbiddenNames as $bad) {
             if (mb_strpos(strtolower($username), $bad) !== false) {
                 return 'This name contains forbidden words.';
             }
        }

        return true;
    }
    private function getForbiddenNames() {
        return [
            // System & Admin
            'admin', 'administrator', 'root', 'superadmin', 'sysadmin', 'master', 'webmaster', 'manager',
            '관리자', '어드민', '운영자', '담당자', '고객센터', '스태프', 'staff',
            'support', 'help', 'helpdesk', 'info', 'contact', 'official', 'notice', 'service',
            'system', 'noreply', 'mail', 'postmaster', 'hostmaster',
            
            // Tech & Infra
            'localhost', 'www', 'ftp', 'smtp', 'pop3', 'ns1', 'ns2', 'api', 'dev', 'test', 'git', 'tester',
            'index', 'home', 'public', 'static', 'assets', 'robots', 'css', 'js', 'images',
            'null', 'undefined', 'void', 'sql', 'mysql', 'database',
            
            // Service Menus
            'login', 'signup', 'join', 'logout', 'settings', 'profile', 'search', 'board', 'chat', 'blog', 'apps', 'shop', 'cart',
            
            // Misc
            '테스트', '임시', '익명', 'google', 'kakao', 'naver', 'facebook', 'apple', 'microsoft'
        ];
    }
}
