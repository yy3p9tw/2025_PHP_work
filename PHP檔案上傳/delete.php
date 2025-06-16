<?php
// 檢查是否有傳入 id 參數，若無則導回管理頁並顯示錯誤訊息
if (!isset($_GET['id'])) {
    header('Location: manage.php?msg=未指定刪除項目');
    exit;
}
$id = intval($_GET['id']); // 取得並轉為整數型態

// 連接 MySQL 資料庫
$dsn = "mysql:host=localhost;dbname=filen;charset=utf8";
$pdo = new PDO($dsn, "root", "");

// 取得要刪除的檔案名稱
$stmt = $pdo->prepare("SELECT name FROM uploads WHERE id=?");
$stmt->execute([$id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if ($file) {
    $filename = $file['name'];
    // 刪除資料庫中的檔案紀錄
    $pdo->prepare("DELETE FROM uploads WHERE id=?")->execute([$id]);
    // 刪除實體檔案（若存在）
    $filepath = __DIR__ . '/files/' . $filename;
    if (file_exists($filepath)) {
        unlink($filepath); // 刪除檔案
    }
    header('Location: manage.php?msg=刪除成功');
    exit;
} else {
    // 找不到檔案紀錄，導回管理頁顯示錯誤
    header('Location: manage.php?msg=找不到該檔案');
    exit;
}
