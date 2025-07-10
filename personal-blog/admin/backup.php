<?php
// 管理端：資料庫備份下載
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['download'])) {
    $db = db();
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
    $sql = "";
    foreach ($tables as $row) {
        $table = $row[0];
        $create = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $sql .= "\n-- ----------------------------\n-- Table structure for `$table`\n-- ----------------------------\n$create;\n";
        $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $sql .= "\n-- ----------------------------\n-- Records of `$table`\n-- ----------------------------\n";
            foreach ($rows as $r) {
                $vals = array_map(function($v) use ($db) {
                    return isset($v) ? $db->quote($v) : 'NULL';
                }, $r);
                $sql .= "INSERT INTO `$table` VALUES (".implode(",", $vals).");\n";
            }
        }
    }
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="blog_backup_'.date('Ymd_His').'.sql"');
    echo $sql;
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>資料庫備份 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">資料庫備份</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">返回</a>
    <a href="?download=1" class="btn btn-primary">下載 SQL 備份檔</a>
    <div class="mt-4 text-muted">本功能可下載目前所有資料表結構與資料，建議定期備份。</div>
</div>
</body>
</html>
