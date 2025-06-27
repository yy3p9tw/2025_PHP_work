<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $conn = $database->getConnection();

    $table_id = $_POST['table_id'] ?? null;
    $table_number = $_POST['table_number'] ?? '';
    $cart = $_SESSION['cart'] ?? [];

    if (empty($cart) || !$table_id) {
        // 購物車為空或桌號無效，重定向回菜單頁面
        header('Location: customer_order.php?table_number=' . urlencode($table_number) . '&error=empty_cart');
        exit();
    }

    $total_amount = 0;
    $order_items_to_process = [];

    try {
        // 開始事務
        $conn->beginTransaction();

        // 獲取所有客製化選擇項的價格調整 (用於驗證和記錄)
        $all_custom_choices_stmt = $conn->query("SELECT id, price_adjustment FROM customization_choices");
        $all_custom_choices_prices = $all_custom_choices_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // 遍歷購物車，準備訂單明細數據
        foreach ($cart as $cart_item_key => $cart_item) {
            $item_id = $cart_item['item_id'];
            $quantity = $cart_item['quantity'];
            $custom_options = $cart_item['custom_options'] ?? []; // 這是 option_id => choice_id 的映射
            $customization_price_adjustment_from_cart = $cart_item['customization_price_adjustment'] ?? 0;

            // 獲取餐點的基本價格
            $stmt_item_price = $conn->prepare('SELECT price FROM menu_items WHERE id = :item_id');
            $stmt_item_price->bindParam(':item_id', $item_id);
            $stmt_item_price->execute();
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
                'base_price' => $base_price, // 記錄餐點基本價格
                'custom_options' => $custom_options, // 儲存客製化選項的 ID 映射
                'customization_price_adjustment' => $customization_price_adjustment_from_cart // 儲存客製化總調整金額
            ];
        }

        // 插入訂單主表
        $stmt_order = $conn->prepare('INSERT INTO orders (table_id, total_amount, status, payment_status) VALUES (:table_id, :total_amount, \'pending\', \'unpaid\')');
        $stmt_order->bindParam(':table_id', $table_id);
        $stmt_order->bindParam(':total_amount', $total_amount);
        $stmt_order->execute();
        $order_id = $conn->lastInsertId();

        // 插入訂單明細表和客製化選項
        $stmt_detail = $conn->prepare('INSERT INTO order_details (order_id, menu_item_id, quantity, price) VALUES (:order_id, :menu_item_id, :quantity, :price)');
        $stmt_custom_detail = $conn->prepare('INSERT INTO order_item_customizations (order_detail_id, customization_choice_id, price_at_order) VALUES (:order_detail_id, :customization_choice_id, :price_at_order)');

        foreach ($order_items_to_process as $item_data) {
            // 插入訂單明細
            $stmt_detail->bindParam(':order_id', $order_id);
            $stmt_detail->bindParam(':menu_item_id', $item_data['item_id']);
            $stmt_detail->bindParam(':quantity', $item_data['quantity']);
            $stmt_detail->bindParam(':price', $item_data['base_price']); // 這裡記錄的是餐點基本價格
            $stmt_detail->execute();
            $order_detail_id = $conn->lastInsertId();

            // 插入客製化選項明細
            foreach ($item_data['custom_options'] as $option_id => $choice_id) {
                if (!isset($all_custom_choices_prices[$choice_id])) {
                    throw new Exception("客製化選擇項 ID: {$choice_id} 不存在。");
                }
                $choice_price_at_order = $all_custom_choices_prices[$choice_id];

                $stmt_custom_detail->bindParam(':order_detail_id', $order_detail_id);
                $stmt_custom_detail->bindParam(':customization_choice_id', $choice_id);
                $stmt_custom_detail->bindParam(':price_at_order', $choice_price_at_order);
                $stmt_custom_detail->execute();
            }
        }

        // 提交事務
        $conn->commit();

        // 清空購物車
        unset($_SESSION['cart']);

        // 重定向到訂單成功頁面
        header('Location: order_success.php?order_id=' . $order_id . '&total_amount=' . $total_amount . '&table_number=' . urlencode($table_number));
        exit();

    } catch (Exception $e) {
        // 發生錯誤時回滾事務
        $conn->rollBack();
        // 重定向回菜單頁面並顯示錯誤訊息
        header('Location: customer_order.php?table_number=' . urlencode($table_number) . '&error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    // 如果不是 POST 請求，重定向回首頁
    header('Location: index.php');
    exit();
}