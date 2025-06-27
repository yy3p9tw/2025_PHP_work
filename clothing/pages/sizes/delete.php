<?php
require_once '../../includes/db.php';
$Size = new DB('sizes');
$Variant = new DB('item_variants');

$id = $_GET['id'] ?? null;

if ($id) {
    // 檢查是否有商品規格關聯到此尺寸，如果有則不允許刪除
    $related_variants = $Variant->all(['size_id' => $id]);

    if (!empty($related_variants)) {
        // 如果有相關聯的規格，重定向回列表頁並顯示錯誤訊息
        header('Location: list.php?error=' . urlencode('尺寸已被商品規格使用，無法刪除。'));
        exit();
    }

    $Size->delete($id);
}

header('Location: list.php');
exit();
?>