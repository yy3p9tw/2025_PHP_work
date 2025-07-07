<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/auth.php';

// 檢查是否已登入
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$user_id = $_SESSION['user_id'];

// 獲取股票代碼
$stock_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($stock_code)) {
    header('Location: stocks.php');
    exit();
}

// 驗證股票是否存在
$stock = $db->fetchOne('SELECT * FROM stocks WHERE code = ? AND status = ?', [$stock_code, 'active']);

if (empty($stock)) {
    header('Location: stocks.php');
    exit();
}

// 檢查是否已在關注清單中
$existing = $db->fetchOne('
    SELECT id FROM watchlist 
    WHERE user_id = ? AND stock_code = ?
', [$user_id, $stock_code]);

if (empty($existing)) {
    // 加入關注清單
    $db->query('INSERT INTO watchlist (user_id, stock_code) VALUES (?, ?)', [$user_id, $stock_code]);
    $_SESSION['success_message'] = '成功加入關注清單';
} else {
    $_SESSION['info_message'] = '此股票已在您的關注清單中';
}

// 重定向回股票詳情頁
header('Location: stock_detail.php?code=' . $stock_code);
exit();
?>
