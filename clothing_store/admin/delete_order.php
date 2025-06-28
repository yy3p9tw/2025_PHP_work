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
    $order_id = $_POST['id'] ?? null;
} else {
    header('Location: orders.php');
    exit;
}

if (!$order_id) {
    header('Location: orders.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. 獲取訂單中的所有商品，以便歸還庫存
    $stmt = $pdo->prepare('SELECT item_variant_id, quantity FROM sale_items WHERE sale_id = ?');
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();

    foreach ($order_items as $item) {
        // 歸還庫存
        $update_stmt = $pdo->prepare('UPDATE item_variants SET stock = stock + ? WHERE id = ?');
        $update_stmt->execute([$item['quantity'], $item['item_variant_id']]);
    }

    // 2. 刪除 sale_items 中的紀錄
    $stmt = $pdo->prepare('DELETE FROM sale_items WHERE sale_id = ?');
    $stmt->execute([$order_id]);

    // 3. 刪除 sales 中的訂單紀錄
    $stmt = $pdo->prepare('DELETE FROM sales WHERE id = ?');
    $stmt->execute([$order_id]);

    $pdo->commit();
    header('Location: orders.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    die("刪除訂單失敗: " . $e->getMessage());
}
?>