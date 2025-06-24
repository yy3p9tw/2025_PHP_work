<?php
require_once '../../includes/db.php';

$customer_id = $_GET['customer_id'] ?? null;
$sale_date = $_GET['sale_date'] ?? null;

if ($customer_id && $sale_date) {
    $Sale = new DB('sales');
    $Sale->getPdo()->prepare("DELETE FROM sales WHERE customer_id = ? AND sale_date = ?")->execute([$customer_id, $sale_date]);
}
header('Location: list.php');
exit;
?>
