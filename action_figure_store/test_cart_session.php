<?php
session_start();

echo "<h2>購物車 Session 測試</h2>";

// 1. 檢查 session 狀態
echo "<h3>Session 狀態</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session 狀態: " . (session_status() === PHP_SESSION_ACTIVE ? "活躍" : "非活躍") . "<br>";

// 2. 顯示所有 session 資料
echo "<h3>所有 Session 資料</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 3. 檢查購物車資料
echo "<h3>購物車資料</h3>";
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    echo "購物車物品數量: " . count($_SESSION['cart']) . "<br>";
    echo "<pre>";
    print_r($_SESSION['cart']);
    echo "</pre>";
} else {
    echo "購物車為空<br>";
}

// 4. 手動添加測試商品到購物車
if (isset($_GET['add_test'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][1] = [
        'product_id' => 1,
        'quantity' => 2,
        'added_at' => date('Y-m-d H:i:s')
    ];
    
    $_SESSION['cart'][2] = [
        'product_id' => 2,
        'quantity' => 1,
        'added_at' => date('Y-m-d H:i:s')
    ];
    
    echo "<div style='color: green;'>已手動添加測試商品到購物車</div>";
    echo "<script>setTimeout(() => location.reload(), 1000);</script>";
}

// 5. 清空購物車
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    echo "<div style='color: red;'>購物車已清空</div>";
    echo "<script>setTimeout(() => location.reload(), 1000);</script>";
}

echo "<br><hr>";
echo "<a href='?add_test=1' style='color: blue;'>手動添加測試商品</a> | ";
echo "<a href='?clear=1' style='color: red;'>清空購物車</a> | ";
echo "<a href='?' style='color: green;'>重新整理</a><br><br>";

echo "<a href='api/cart_get.php' target='_blank'>測試購物車 API</a> | ";
echo "<a href='cart.html' target='_blank'>前往購物車頁面</a>";
?>
