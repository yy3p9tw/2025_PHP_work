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
        table, thead, tbody, th, td, tr { display: block; width: 100%; }
        thead { display: none; }
        tr { margin-bottom: 1.2em; background: #fff; border-radius: 10px; box-shadow: 0 1px 6px #ffb34722; }
        td { padding: 0.7em 1em; border: none; border-bottom: 1px solid #ffe0e0; position: relative; }
        td:before { content: attr(data-label); font-weight: bold; color: #b97a56; display: block; margin-bottom: 0.3em; }
        .btn-back { width: 100%; margin-bottom: 0.5em; }
    }
    </style>
</head>
<body class="warm-bg">
    <div style="max-width:1100px;margin:40px auto 0;">
        <h1 class="main-title">銷售記錄列表</h1>
        <div style="text-align:right;margin-bottom:1.5em;">
            <a href="../../index.php" class="btn-back">返回首頁</a>
            <a href="add.php" class="btn-back" style="margin-left:8px;">＋ 新增銷售記錄</a>
        </div>
        <form method="get" style="margin-bottom:1.5em;display:flex;gap:1em;align-items:center;flex-wrap:wrap;">
            <label>日期：<input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from']??'') ?>"> ~ <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to']??'') ?>"></label>
            <label>商品名稱：<input type="text" name="item_name" value="<?= htmlspecialchars($_GET['item_name']??'') ?>" placeholder="輸入商品名稱關鍵字"></label>
            <label>客戶：<select name="customer_id"><option value="">全部</option>
                <?php foreach($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= isset($_GET['customer_id']) && $_GET['customer_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select></label>
            <button type="submit">搜尋</button>
        </form>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 16px #ffb34733;padding:2em 1em 1em 1em;">
        <table style="width:100%;">
            <tr>
                <th>ID</th>
                <th>客戶</th>
                <th>商品</th>
                <th>數量</th>
                <th>單價</th>
                <th>總價</th>
                <th>日期</th>
                <th>備註</th>
                <th>操作</th>
            </tr>
            <?php foreach($sales as $sale): ?>
            <tr>
                <td data-label="ID"><?= $sale['id'] ?></td>
                <td data-label="客戶"><?php $customer = array_filter($customers, fn($c) => $c['id'] == $sale['customer_id']); echo $customer ? htmlspecialchars(array_values($customer)[0]['name']) : ''; ?></td>
                <td data-label="商品"><?php $variant = $variantMap[$sale['item_id']] ?? null; if (is_array($variant) && isset($variant['item_id'])) { $itemName = isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : ''; $colorName = (isset($variant['color_id']) && isset($colorMap[$variant['color_id']])) ? $colorMap[$variant['color_id']] : ''; echo htmlspecialchars($itemName . $colorName); } else { echo '<span style="color:#aaa;">(已刪除或資料異常)</span>'; } ?></td>
                <td data-label="數量"><?= $sale['quantity'] ?></td>
                <td data-label="單價"><?= intval($sale['unit_price']) ?></td>
                <td data-label="總價"><?= intval($sale['total_price']) ?></td>
                <td data-label="日期"><?= $sale['sale_date'] ?></td>
                <td data-label="備註"><?= htmlspecialchars($sale['notes']) ?></td>
                <td data-label="操作">
                    <a href="edit.php?id=<?= $sale['id'] ?>" class="btn-back" style="padding:0.3em 1em;font-size:0.95em;">編輯</a>
                    <a href="delete.php?id=<?= $sale['id'] ?>" class="btn-back" style="background:#fff0e0;color:#d2691e;padding:0.3em 1em;font-size:0.95em;border:1px solid #ffb347;" onclick="return confirm('確定要刪除嗎？');">刪除</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
    </div>
</body>
</html>