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
    $variant_id = $_POST['id'] ?? null;
    $item_id = $_POST['item_id'] ?? null; // 用於跳轉
} else {
    header('Location: index.php');
    exit;
}

if (!$variant_id || !$item_id) {
    header('Location: index.php');
    exit;
}


try {
    $stmt = $pdo->prepare('DELETE FROM item_variants WHERE id = ?');
    $stmt->execute([$variant_id]);
    header('Location: manage_variants.php?item_id=' . $item_id);
    exit;
} catch (PDOException $e) {
    die("刪除規格失敗: " . $e->getMessage());
}
?>