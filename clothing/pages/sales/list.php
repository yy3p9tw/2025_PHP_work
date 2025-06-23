<?php
require_once '../../includes/db.php';
$Sale = new DB('sales');
$Item = new DB('items');
$items = $Item->all();
$Customer = new DB('customers');
$customers = $Customer->all();
$Variant = new DB('item_variants');
$variants = $Variant->all();
$Color = new DB('colors');
$colors = $Color->all();
// 建立查表
$itemMap = [];
foreach($items as $i) $itemMap[$i['id']] = $i['name'];
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
$variantMap = [];
foreach($variants as $v) $variantMap[$v['id']] = $v;
// 搜尋條件
$where = [];
$params = [];
if (!empty($_GET['customer_id'])) {
    $where[] = "customer_id = :customer_id";
    $params['customer_id'] = $_GET['customer_id'];
}
if (!empty($_GET['item_name'])) {
    // 依商品名稱或顏色名稱模糊搜尋
    $itemName = trim($_GET['item_name']);
    $itemIds = [];
    foreach ($variants as $v) {
        $itemBaseName = isset($itemMap[$v['item_id']]) ? $itemMap[$v['item_id']] : '';
        $colorName = isset($colorMap[$v['color_id']]) ? $colorMap[$v['color_id']] : '';
        $fullName = $itemBaseName . $colorName;
        if (mb_strpos($itemBaseName, $itemName) !== false || mb_strpos($colorName, $itemName) !== false || mb_strpos($fullName, $itemName) !== false) {
            $itemIds[] = $v['id']; // 這裡的 id 是 variant id
        }
    }
    if ($itemIds) {
        $where[] = "item_id IN (".implode(",", array_map('intval', $itemIds)).")";
    } else {
        $where[] = "0"; // 無結果
    }
}
if (!empty($_GET['date_from'])) {
    $where[] = "sale_date >= :date_from";
    $params['date_from'] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $where[] = "sale_date <= :date_to";
    $params['date_to'] = $_GET['date_to'];
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
// 取得資料
$dbh = $Sale->getPdo();
$sql = "SELECT * FROM sales $where_sql ORDER BY id DESC";
$stmt = $dbh->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>銷售記錄列表</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
    @media (max-width: 700px) {
        .main-title { font-size: 1.2em; }
        .grid { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
        .sale-card { flex-direction: column; }
        .sale-card div { margin-bottom: 0.5em; }
        .btn-back { width: 100%; margin-bottom: 0.5em; }
    }
    </style>
</head>
<body class="warm-bg">
    <div style="max-width:1100px;margin:40px auto 0;">
        <h1 class="main-title">銷售記錄列表</h1>
        <div class="action-bar" style="margin-bottom:1.5em;">
            <a href="add.php" class="btn-back btn-sm">＋ 新增銷售</a>
            <a href="../items/list.php" class="btn-back btn-sm">返回商品列表</a>
        </div>
        <div class="grid">
        <?php foreach($sales as $sale): ?>
            <div class="product-card">
                <div class="sale-card" style="padding:1.5em;border:1px solid #eee;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.1);margin-bottom:1.5em;background:#fff;">
                    <div style="font-size:1.2em;font-weight:bold;margin-bottom:0.5em;">銷售記錄 ID: <?= $sale['id'] ?></div>
                    <div style="margin-bottom:0.5em;"><strong>客戶：</strong><?php $customer = array_filter($customers, fn($c) => $c['id'] == $sale['customer_id']); echo $customer ? htmlspecialchars(array_values($customer)[0]['name']) : ''; ?></div>
                    <div style="margin-bottom:0.5em;"><strong>商品：</strong><?php $variant = $variantMap[$sale['item_id']] ?? null; if (is_array($variant) && isset($variant['item_id'])) { $itemName = isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : ''; $colorName = (isset($variant['color_id']) && isset($colorMap[$variant['color_id']]))
 ? $colorMap[$variant['color_id']] : ''; echo htmlspecialchars($itemName . $colorName); } else { echo '<span style="color:#aaa;">(已刪除或資料異常)</span>'; } ?></div>
                    <div style="margin-bottom:0.5em;"><strong>數量：</strong><?= $sale['quantity'] ?></div>
                    <div style="margin-bottom:0.5em;"><strong>單價：</strong><?= intval($sale['unit_price']) ?></div>
                    <div style="margin-bottom:0.5em;"><strong>總價：</strong><?= intval($sale['total_price']) ?></div>
                    <div style="margin-bottom:0.5em;"><strong>日期：</strong><?= $sale['sale_date'] ?></div>
                    <div style="margin-bottom:1em;"><strong>備註：</strong><?= htmlspecialchars($sale['notes']) ?></div>
                </div>
                <div class="card-action-bar" style="margin-top:0.7em;display:flex;gap:0.5em;flex-wrap:wrap;">
                    <a href="edit.php?id=<?= $sale['id'] ?>" class="btn-back btn-sm">編輯</a>
                    <a href="delete.php?id=<?= $sale['id'] ?>" class="btn-back btn-sm btn-del" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</body>
</html>