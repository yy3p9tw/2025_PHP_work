<?php
require_once '../../includes/db.php';
$Sale = new DB('sales');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sales_export.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', '客戶ID', '商品ID', '數量', '單價', '總價', '日期', '備註']);

$sales = $Sale->all();
foreach ($sales as $sale) {
    fputcsv($output, [
        $sale['id'],
        $sale['customer_id'],
        $sale['item_id'],
        $sale['quantity'],
        $sale['unit_price'],
        $sale['total_price'],
        $sale['sale_date'],
        $sale['notes'],
    ]);
}
fclose($output);
exit;
