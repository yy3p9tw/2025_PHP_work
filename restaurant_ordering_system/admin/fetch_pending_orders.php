<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php'; // For status translation

// 確保只有登入的員工/管理員可以訪問
if (!isLoggedIn() || !isStaff()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// 獲取所有訂單，並按時間倒序排列
$orders_stmt = $conn->query("SELECT o.*, t.table_number FROM orders o JOIN tables t ON o.table_id = t.id ORDER BY o.order_time DESC");
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// 翻譯狀態和支付狀態，並格式化金額
foreach ($orders as &$order) {
    $order['status_text'] = getOrderStatusText($order['status']);
    $order['payment_status_text'] = getPaymentStatusText($order['payment_status']);
    $order['total_amount_formatted'] = number_format($order['total_amount'], 2);
}

header('Content-Type: application/json');
echo json_encode($orders);
?>