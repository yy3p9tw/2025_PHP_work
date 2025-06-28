<?php
session_start();
require_once '../includes/db.php';
require_once 'auth_check.php';
require_once '../includes/csrf_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('無效的請求，請重試。');
    }

    $category_id = $_POST['id'] ?? null;

    if (!$category_id) {
        die('未指定分類ID。');
    }

    try {
        $pdo = get_pdo();
        // 檢查是否有商品關聯到此分類
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM items WHERE category_id = ?');
        $stmt->execute([$category_id]);
        if ($stmt->fetchColumn() > 0) {
            // 如果有商品關聯，則將這些商品的 category_id 設為 NULL
            $stmt = $pdo->prepare('UPDATE items SET category_id = NULL WHERE category_id = ?');
            $stmt->execute([$category_id]);
        }

        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$category_id]);

        $_SESSION['message'] = '分類刪除成功！';
    } catch (PDOException $e) {
        $_SESSION['error'] = '資料庫錯誤：' . $e->getMessage();
    }
}

header('Location: manage_categories.php');
exit;
?>
