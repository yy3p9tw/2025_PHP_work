<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'auth_check.php';
require_once '../includes/db.php';
require_once '../includes/csrf_functions.php';

$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('無效的請求，請重試。');
    }
    $id = $_POST['id'] ?? null;
} else {
    header('Location: index.php');
    exit;
}

if (!$id) {
    header('Location: index.php');
    exit;
}

// 在刪除資料庫紀錄前，先取得圖片檔案名稱
try {
    $stmt = $pdo->prepare('SELECT image FROM items WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    $image_to_delete = $item['image'] ?? null;
} catch (PDOException $e) {
    die("無法讀取圖片資訊: " . $e->getMessage());
}

// 刪除資料庫紀錄
try {
    $stmt = $pdo->prepare('DELETE FROM items WHERE id = ?');
    $stmt->execute([$id]);

    // 如果成功刪除紀錄，且有圖片檔案，就刪除圖片檔案
    if ($stmt->rowCount() > 0 && !empty($image_to_delete)) {
        $image_path = '../images/' . $image_to_delete;
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    die("刪除商品失敗: " . $e->getMessage());
}
?>