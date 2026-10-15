<?php

namespace App\Controllers;

use PDO;
use PDOException;

class InstallController extends BaseController {
    public function index() {
        if (file_exists(__DIR__ . '/../../.env') || file_exists(__DIR__ . '/../../install.lock')) {
            $this->redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->install();
            return;
        }

        $this->view('install/index');
    }

    private function envQuote($v) {
        $v = (string)$v;
        return '"' . addcslashes($v, '"\\') . '"';
    }

    private function runSqlFile(PDO $pdo, $file) {
        $sql = file_get_contents($file);
        $sql = preg_replace('/USE `?ai_php`?;/i', '', $sql);
        // Split on semicolon + newline to avoid single exec() multi-statement issues
        $statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));
        foreach ($statements as $stmt) {
            if ($stmt === '') continue;
            $pdo->exec($stmt);
        }
    }

    private function writeHtaccess($dir) {
        $ht = rtrim($dir, '/\\') . '/.htaccess';
        if (!file_exists($ht)) {
            @file_put_contents($ht, "Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar|pl|py|jsp|asp|sh|cgi)\$\">\n    Require all denied\n</FilesMatch>\nphp_flag engine off\nRemoveHandler .php .phtml\n");
        }
    }

    private function install() {
        if (file_exists(__DIR__ . '/../../.env') || file_exists(__DIR__ . '/../../install.lock')) {
            $this->redirect('/');
            return;
        }
        $dbHost = trim($_POST['db_host'] ?? 'localhost');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = $_POST['db_pass'] ?? '';
        $appUrl = trim($_POST['app_url'] ?? 'http://localhost');

        $adminUserid = trim($_POST['admin_user_id'] ?? 'admin');
        $adminUsername = trim($_POST['admin_username'] ?? 'Admin');
        $adminPassword = $_POST['admin_password'] ?? '';
        $adminEmail = trim($_POST['admin_email'] ?? '');

        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $dbHost) && !preg_match('/^[A-Za-z0-9_.-]+(:[0-9]+)?$/', $dbHost)) {
            return $this->view('install/index', ['error' => 'Invalid DB host']);
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
            return $this->view('install/index', ['error' => 'Invalid DB name (letters, numbers, underscore only)']);
        }
        if ($adminUserid === '' || $adminPassword === '' || $adminEmail === '') {
            return $this->view('install/index', ['error' => 'Admin ID/password/email are required']);
        }
        if (strlen($adminPassword) < 8) {
            return $this->view('install/index', ['error' => 'Admin password must be at least 8 characters']);
        }
        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->view('install/index', ['error' => 'Invalid admin email']);
        }

        try {
            // 1. Try to connect and create DB if it doesn't exist
            try {
                $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $safeName = str_replace('`', '', $dbName);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$safeName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (PDOException $e) {
                // Ignore if CREATE DATABASE fails (shared hosting)
            }

            // Reconnect to the created/existing database
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("SET SESSION sql_mode = ''");

            // 2. Run setup.sql
            if (file_exists(__DIR__ . '/../../views/install/setup.sql')) {
                $this->runSqlFile($pdo, __DIR__ . '/../../views/install/setup.sql');
            }

            // 3. Insert Admin User from Form (skip if exists)
            if (!empty($adminPassword)) {
                $chk = $pdo->prepare("SELECT id FROM users WHERE user_id = :u OR email = :e LIMIT 1");
                $chk->execute(['u' => $adminUserid, 'e' => $adminEmail]);
                if (!$chk->fetch()) {
                    $hashed = password_hash($adminPassword, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (user_id, username, password, email, role, level) VALUES (:user_id, :username, :password, :email, :role, :level)");
                    $stmt->execute([
                        'user_id' => $adminUserid,
                        'username' => $adminUsername,
                        'password' => $hashed,
                        'email' => $adminEmail,
                        'role' => 'admin',
                        'level' => 10
                    ]);
                }
            }

            // 4. Create .env file (quoted values)
            $envContent = "DB_HOST=" . $this->envQuote($dbHost) . "\n";
            $envContent .= "DB_NAME=" . $this->envQuote($dbName) . "\n";
            $envContent .= "DB_USER=" . $this->envQuote($dbUser) . "\n";
            $envContent .= "DB_PASS=" . $this->envQuote($dbPass) . "\n";
            $envContent .= "APP_URL=" . $this->envQuote(rtrim($appUrl, '/')) . "\n";

            file_put_contents(__DIR__ . '/../../.env', $envContent);
            @chmod(__DIR__ . '/../../.env', 0600);
            @file_put_contents(__DIR__ . '/../../install.lock', date('c'));
            
            // 5. Create data directory and essential sub-directories
            $baseDataPath = __DIR__ . '/../../public/data';
            $subDirs = ['', '/page', '/logo', '/config'];
            
            foreach ($subDirs as $sub) {
                $path = $baseDataPath . $sub;
                if (!file_exists($path)) {
                    @mkdir($path, 0755, true);
                }
                $this->writeHtaccess($path);
            }

            echo "<script>alert('Installation successful!'); window.location.href='/';</script>";
            exit;

        } catch (PDOException $e) {
            error_log('Install failed: ' . $e->getMessage());
            $this->view('install/index', ['error' => 'Installation failed. Please check DB settings and try again.']);
        }
    }
}
