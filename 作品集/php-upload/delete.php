<?php
// 檢查 id 是否存在
if (!isset($_GET['id'])) {
    header('Location: manage.php?msg=未指定刪除項目');
    exit;
}
$id = intval($_GET['id']);

// 連接資料庫
$dsn = "mysql:host=localhost;dbname=filen;charset=utf8";
$pdo = new PDO($dsn, "root", "");

// 取得檔案名稱
$stmt = $pdo->prepare("SELECT name FROM uploads WHERE id=?");
$stmt->execute([$id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if ($file) {
    $filename = $file['name'];
    // 刪除資料庫紀錄
    $pdo->prepare("DELETE FROM uploads WHERE id=?")->execute([$id]);
    // 刪除實體檔案
    $filepath = __DIR__ . '/files/' . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    header('Location: manage.php?msg=刪除成功');
    exit;
} else {
    header('Location: manage.php?msg=找不到該檔案');
    exit;
}
