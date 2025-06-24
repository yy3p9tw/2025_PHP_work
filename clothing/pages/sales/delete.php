<?php
require_once '../../includes/db.php';

$customer_id = $_GET['customer_id'] ?? null;
$sale_date = $_GET['sale_date'] ?? null;

if ($sale_date) {
    $Sale = new DB('sales');
    $pdo = $Sale->getPdo();
    if ($customer_id === null || $customer_id === '') {
        // 刪除 customer_id 為 NULL 或空字串的紀錄
        $pdo->prepare("DELETE FROM sales WHERE (customer_id IS NULL OR customer_id = '') AND sale_date = ?")->execute([$sale_date]);
    } else {
        $pdo->prepare("DELETE FROM sales WHERE customer_id = ? AND sale_date = ?")->execute([$customer_id, $sale_date]);
    }
}
header('Location: list.php');
exit;
?>
