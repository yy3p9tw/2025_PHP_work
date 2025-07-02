<?php
session_start();
require_once '../includes/db.php'; // This now provides the $pdo object

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table_id = $_POST['table_id'] ?? null;
    $table_number = $_POST['table_number'] ?? '';
    $cart = $_SESSION['cart'] ?? [];

    if (empty($cart) || !$table_id) {
        header('Location: customer_order.php?table_number=' . urlencode($table_number) . '&error=empty_cart');
        exit();
    }

    $total_amount = 0;
    $order_items_to_process = [];

    try {
        // 開始事務
        $pdo->beginTransaction();

        // 獲取所有客製化選擇項的價格調整 (用於驗證和記錄)
        $all_custom_choices_stmt = $pdo->query("SELECT id, price_adjustment FROM customization_choices");
        $all_custom_choices_prices = $all_custom_choices_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // 遍歷購物車，準備訂單明細數據
        foreach ($cart as $cart_item_key => $cart_item) {
            $item_id = $cart_item['item_id'];
            $quantity = $cart_item['quantity'];
            $custom_options = $cart_item['custom_options'] ?? [];
            $customization_price_adjustment_from_cart = $cart_item['customization_price_adjustment'] ?? 0;

            // 獲取餐點的基本價格
            $stmt_item_price = $pdo->prepare('SELECT price FROM menu_items WHERE id = ?');
            $stmt_item_price->execute([$item_id]);
            $base_price = $stmt_item_price->fetchColumn();

            if (!$base_price) {
                throw new Exception("餐點 ID: {$item_id} 不存在或已下架。");
            }

            // 計算此購物車項的總價格 (基本價格 + 客製化調整) * 數量
            $item_total_price_with_customization = ($base_price + $customization_price_adjustment_from_cart) * $quantity;
            $total_amount += $item_total_price_with_customization;

            $order_items_to_process[] = [
                'item_id' => $item_id,
                'quantity' => $quantity,
                'base_price' => $base_price,
                'custom_options' => $custom_options,
                'customization_price_adjustment' => $customization_price_adjustment_from_cart
            ];
        }

        // 插入訂單主表
        $stmt_order = $pdo->prepare('INSERT INTO orders (table_id, total_amount, status, payment_status) VALUES (?, ?, \'pending\', \'unpaid\')');
        $stmt_order->execute([$table_id, $total_amount]);
        $order_id = $pdo->lastInsertId();

        // 插入訂單明細表和客製化選項
        $stmt_detail = $pdo->prepare('INSERT INTO order_details (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)');
        $stmt_custom_detail = $pdo->prepare('INSERT INTO order_item_customizations (order_detail_id, customization_choice_id, price_at_order) VALUES (?, ?, ?)');

        foreach ($order_items_to_process as $item_data) {
            // 插入訂單明細
            $stmt_detail->execute([$order_id, $item_data['item_id'], $item_data['quantity'], $item_data['base_price']]);
            $order_detail_id = $pdo->lastInsertId();

            // 插入客製化選項明細
            foreach ($item_data['custom_options'] as $option_id => $choice_id) {
                if (!isset($all_custom_choices_prices[$choice_id])) {
                    throw new Exception("客製化選擇項 ID: {$choice_id} 不存在。");
                }
                $choice_price_at_order = $all_custom_choices_prices[$choice_id];

                $stmt_custom_detail->execute([$order_detail_id, $choice_id, $choice_price_at_order]);
            }
        }

        // 提交事務
        $pdo->commit();

        // 清空購物車
        unset($_SESSION['cart']);

        // 重定向到訂單成功頁面
        header('Location: order_success.php?order_id=' . $order_id . '&total_amount=' . $total_amount . '&table_number=' . urlencode($table_number));
        exit();

    } catch (Exception $e) {
        // 發生錯誤時回滾事務
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // 重定向回菜單頁面並顯示錯誤訊息
        header('Location: customer_order.php?table_number=' . urlencode($table_number) . '&error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    // 如果不是 POST 請求，重定向回首頁
    header('Location: index.php');
    exit();
}