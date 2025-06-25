<?php
require_once '../../includes/db.php';
// 顯示錯誤訊息
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$customer_id = $_GET['customer_id'] ?? null;
$sale_date = $_GET['sale_date'] ?? null;

if ($sale_date) {
    $Sale = new DB('sales');
    $Variant = new DB('item_variants');
    $pdo = $Sale->getPdo();
    // 先查詢所有即將刪除的銷售明細
    if ($customer_id === null || $customer_id === '') {
        $stmt = $pdo->prepare("SELECT item_id, quantity FROM sales WHERE (customer_id IS NULL OR customer_id = '') AND sale_date = ?");
        $stmt->execute([$sale_date]);
    } else {
        $stmt = $pdo->prepare("SELECT item_id, quantity FROM sales WHERE customer_id = ? AND sale_date = ?");
        $stmt->execute([$customer_id, $sale_date]);
    }
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // 將數量加回庫存
    foreach ($sales as $row) {
        $variantId = $row['item_id'];
        $qty = $row['quantity'];
        // 改用 all 查詢單一 variant
        $variantArr = $Variant->all("id = $variantId");
        $variant = $variantArr ? $variantArr[0] : null;
        if (!$variant) {
            error_log("[delete.php] 找不到 variant id: $variantId");
            continue;
        }
        if (!isset($variant['stock'])) {
            error_log("[delete.php] variant id: $variantId 無 stock 欄位");
            continue;
        }
        if (!is_numeric($variant['stock']) || !is_numeric($qty)) {
            error_log("[delete.php] variant id: $variantId stock 或 qty 非數字");
            continue;
        }
        $Variant->update($variantId, ['stock' => $variant['stock'] + $qty]);
    }
    // 再執行刪除
    if ($customer_id === null || $customer_id === '') {
        $pdo->prepare("DELETE FROM sales WHERE (customer_id IS NULL OR customer_id = '') AND sale_date = ?")->execute([$sale_date]);
    } else {
        $pdo->prepare("DELETE FROM sales WHERE customer_id = ? AND sale_date = ?")->execute([$customer_id, $sale_date]);
    }
}
header('Location: list.php');
exit;
?>
