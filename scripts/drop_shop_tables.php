<?php
// One-time cleanup: drop shop tables + mall columns left by old auto-migration.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}
$db = App\Core\Database::getInstance();
$tables = ['settlements', 'orders', 'products'];
foreach ($tables as $t) {
    try { $db->exec("DROP TABLE IF EXISTS `$t`"); echo "dropped $t\n"; }
    catch (Throwable $e) { echo "skip $t: " . $e->getMessage() . "\n"; }
}
try {
    $cols = $db->query("SHOW COLUMNS FROM config")->fetchAll(PDO::FETCH_COLUMN);
    foreach (['mall_commission', 'mall_name'] as $c) {
        if (in_array($c, $cols, true)) { $db->exec("ALTER TABLE config DROP COLUMN `$c`"); echo "dropped column $c\n"; }
    }
} catch (Throwable $e) { echo 'config skip: ' . $e->getMessage() . "\n"; }
echo "done\n";
