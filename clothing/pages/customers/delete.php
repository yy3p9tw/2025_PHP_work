<?php
require_once '../../includes/db.php';
$Customer = new DB('customers');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // 檢查是否有銷售記錄關聯到此客戶，如果有則不允許刪除
    $Sale = new DB('sales');
    $related_sales = $Sale->all(['customer_id' => $id]);

    if (!empty($related_sales)) {
        // 如果有相關聯的銷售記錄，重定向回列表頁並顯示錯誤訊息
        header('Location: list.php?error=' . urlencode('客戶已有銷售記錄，無法刪除。'));
        exit();
    }

    $Customer->delete($id);
}
header('Location: list.php');
exit;