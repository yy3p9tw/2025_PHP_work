<?php
require_once '../../includes/db.php';
$Sale = new DB('sales');
$Variant = new DB('item_variants');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // 先查出該筆銷售的規格與數量
    $sale = $Sale->all('id = ' . $id);
    if ($sale && isset($sale[0]['item_id']) && isset($sale[0]['quantity'])) {
        $variantId = intval($sale[0]['item_id']);
        $qty = intval($sale[0]['quantity']);
        // 還原庫存
        $variant = $Variant->all('id = ' . $variantId);
        if ($variant && isset($variant[0]['stock'])) {
            $Variant->update($variantId, ['stock' => intval($variant[0]['stock']) + $qty]);
        }
    }
    $Sale->delete($id);
}
header('Location: list.php');
exit;
