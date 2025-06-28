<?php
session_start();
require_once '../includes/db.php';
require_once 'auth_check.php';
require_once '../includes/csrf_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('無效的請求，請重試。');
    }

    $color_id = $_POST['id'] ?? null;

    if (!$color_id) {
        die('未指定顏色ID。');
    }

    try {
        $pdo = get_pdo();
        // 檢查是否有商品規格關聯到此顏色
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM item_variants WHERE color_id = ?');
        $stmt->execute([$color_id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = '無法刪除此顏色，因為有商品規格關聯到它。請先修改或刪除相關商品規格。';
        } else {
            $stmt = $pdo->prepare('DELETE FROM colors WHERE id = ?');
            $stmt->execute([$color_id]);
            $_SESSION['message'] = '顏色刪除成功！';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = '資料庫錯誤：' . $e->getMessage();
    }
}

header('Location: manage_colors.php');
exit;
?>
