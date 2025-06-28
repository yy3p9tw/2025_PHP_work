<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/cart_functions.php';
require_once 'includes/csrf_functions.php';

$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Location: view_item.php?id=' . ($_POST['item_id'] ?? '') . '&error=invalid_csrf');
        exit;
    }

    $item_id = $_POST['item_id'] ?? null;
    $color_id = $_POST['color_id'] ?? null;
    $size_id = $_POST['size_id'] ?? null;
    $quantity = (int)($_POST['quantity'] ?? 1);

    if (!$item_id || !$color_id || !$size_id || $quantity <= 0) {
        // 錯誤處理，例如導回商品頁面並顯示錯誤訊息
        header('Location: view_item.php?id=' . $item_id . '&error=invalid_selection');
        exit;
    }

    try {
        // 查詢 item_variant_id 和相關資訊
        $stmt = $pdo->prepare(
            'SELECT iv.id as item_variant_id, iv.sell_price, iv.stock, i.name as item_name, c.name as color_name, s.name as size_name
             FROM item_variants iv
             JOIN items i ON iv.item_id = i.id
             JOIN colors c ON iv.color_id = c.id
             JOIN sizes s ON iv.size_id = s.id
             WHERE iv.item_id = ? AND iv.color_id = ? AND iv.size_id = ?'
        );
        $stmt->execute([$item_id, $color_id, $size_id]);
        $variant = $stmt->fetch();

        if (!$variant) {
            header('Location: view_item.php?id=' . $item_id . '&error=variant_not_found');
            exit;
        }

        // 檢查庫存
        if ($variant['stock'] < $quantity) {
            header('Location: view_item.php?id=' . $item_id . '&error=not_enough_stock');
            exit;
        }

        // 將商品加入購物車
        add_to_cart($variant['item_variant_id'], $quantity, [
            'item_name' => $variant['item_name'],
            'color_name' => $variant['color_name'],
            'size_name' => $variant['size_name'],
            'sell_price' => $variant['sell_price'],
            'stock' => $variant['stock'] // 實際庫存，用於前端顯示
        ]);

        // 導向購物車頁面
        header('Location: cart.php');
        exit;

    } catch (PDOException $e) {
        die("加入購物車失敗: " . $e->getMessage());
    }
}

header('Location: index.php'); // 如果不是 POST 請求，導回首頁
exit;
?>