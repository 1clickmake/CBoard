<?php
// Live-DB security/schema migration (idempotent). Run with PHP 8.1+:
//   php scripts/migrate_security.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}
$db = App\Core\Database::getInstance();

function hasColumn($db, $table, $col) {
    $cols = $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
    return in_array($col, $cols, true);
}
function hasIndex($db, $table, $idx) {
    $rows = $db->query("SHOW INDEX FROM `$table`")->fetchAll();
    foreach ($rows as $r) { if (($r['Key_name'] ?? '') === $idx) return true; }
    return false;
}

// 1. 2FA column
if (!hasColumn($db, 'users', 'two_factor_secret')) {
    $db->exec("ALTER TABLE users ADD COLUMN two_factor_secret VARCHAR(64) DEFAULT NULL COMMENT 'TOTP 2FA 시크릿'");
    echo "added users.two_factor_secret\n";
} else { echo "users.two_factor_secret exists\n"; }

// 2. Composite / missing indexes
$idx = [
    ['posts', 'idx_board_list', 'ADD INDEX idx_board_list (board_id, reply_depth, created_at)'],
    ['point_log', 'idx_created_at', 'ADD INDEX idx_created_at (created_at)'],
    ['mail_logs', 'idx_sent_at', 'ADD INDEX idx_sent_at (sent_at)'],
];
foreach ($idx as [$t, $name, $ddl]) {
    try {
        if (!hasIndex($db, $t, $name)) { $db->exec("ALTER TABLE `$t` $ddl"); echo "added $t.$name\n"; }
        else echo "$t.$name exists\n";
    } catch (Throwable $e) { echo "skip $t.$name: " . $e->getMessage() . "\n"; }
}

// 3. Charset conversion to utf8mb4 (table by table)
$tables = ['users','config','pages','board_groups','boards','posts','comments','post_files','file_downloads','visitor_logs','point_log','mail_logs','faq'];
foreach ($tables as $t) {
    try {
        $row = $db->query("SHOW TABLE STATUS WHERE Name = '$t'")->fetch();
        $cs = $row['Collation'] ?? '';
        if (stripos($cs, 'utf8mb4') === false) {
            $db->exec("ALTER TABLE `$t` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "converted $t\n";
        } else echo "$t already utf8mb4\n";
    } catch (Throwable $e) { echo "skip convert $t: " . $e->getMessage() . "\n"; }
}
echo "done\n";
