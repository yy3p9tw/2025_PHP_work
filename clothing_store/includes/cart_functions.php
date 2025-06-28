<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 初始化購物車
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * 將商品加入購物車或更新數量
 * @param int $item_variant_id 商品規格ID
 * @param int $quantity 數量
 * @param array $variant_info 包含商品名稱、顏色、尺寸、價格等資訊
 */
function add_to_cart($item_variant_id, $quantity, $variant_info) {
    if ($quantity <= 0) {
        remove_from_cart($item_variant_id);
        return;
    }

    if (isset($_SESSION['cart'][$item_variant_id])) {
        // 如果商品已在購物車，更新數量
        $_SESSION['cart'][$item_variant_id]['quantity'] += $quantity;
    } else {
        // 否則，新增商品
        $_SESSION['cart'][$item_variant_id] = [
            'variant_info' => $variant_info,
            'quantity' => $quantity
        ];
    }
}

/**
 * 從購物車中移除商品
 * @param int $item_variant_id 商品規格ID
 */
function remove_from_cart($item_variant_id) {
    if (isset($_SESSION['cart'][$item_variant_id])) {
        unset($_SESSION['cart'][$item_variant_id]);
    }
}

/**
 * 更新購物車中商品的數量
 * @param int $item_variant_id 商品規格ID
 * @param int $new_quantity 新的數量
 */
function update_cart_quantity($item_variant_id, $new_quantity) {
    if ($new_quantity <= 0) {
        remove_from_cart($item_variant_id);
    } else if (isset($_SESSION['cart'][$item_variant_id])) {
        $_SESSION['cart'][$item_variant_id]['quantity'] = $new_quantity;
    }
}

/**
 * 清空購物車
 */
function clear_cart() {
    $_SESSION['cart'] = [];
}

/**
 * 計算購物車總金額
 * @return float 總金額
 */
function get_cart_total() {
    $total = 0;
    foreach ($_SESSION['cart'] as $item_variant_id => $item) {
        $total += $item['variant_info']['sell_price'] * $item['quantity'];
    }
    return $total;
}

/**
 * 獲取購物車中的商品數量
 * @return int 購物車中的商品總數
 */
function get_cart_item_count() {
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

?>