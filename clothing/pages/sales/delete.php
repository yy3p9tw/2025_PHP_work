<?php
require_once '../../includes/db.php';

// 顯示錯誤訊息
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sale_id = $_GET['id'] ?? null;

if ($sale_id) {
    $Sale = new DB('sales');
    $SaleItem = new DB('sale_items');
    $Variant = new DB('item_variants');

    try {
        $Sale->getPdo()->beginTransaction();

        // 獲取銷售明細
        $sale_items = $SaleItem->all(['sale_id' => $sale_id]);

        // 將數量加回庫存
        foreach ($sale_items as $sale_item) {
            $variant = $Variant->find(['id' => $sale_item['item_variant_id']]);
            if ($variant) {
                $Variant->update($variant['id'], ['stock' => $variant['stock'] + $sale_item['quantity']]);
            }
        }

        // 刪除銷售明細
        $SaleItem->delete(['sale_id' => $sale_id]); // 假設 DB 類別的 delete 方法可以接受 where 條件

        // 刪除銷售主表記錄
        $Sale->delete($sale_id);

        $Sale->getPdo()->commit();

    } catch (Throwable $e) {
        $Sale->getPdo()->rollBack();
        error_log("[delete.php] 刪除銷售記錄時發生錯誤: " . $e->getMessage());
        // 可以重定向回列表頁並顯示錯誤訊息
        header('Location: list.php?error=' . urlencode('刪除銷售記錄時發生錯誤。'));
        exit();
    }
}

header('Location: list.php');
exit;
?>