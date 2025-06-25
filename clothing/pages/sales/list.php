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
$itemMap = [];
foreach($items as $i) $itemMap[$i['id']] = $i['name'];
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
$variantMap = [];
foreach($variants as $v) $variantMap[$v['id']] = $v;
$where = [];
$params = [];
if (!empty($_GET['customer_id'])) {
    $where[] = "customer_id = :customer_id";
    $params['customer_id'] = $_GET['customer_id'];
}
if (!empty($_GET['item_name'])) {
    $itemName = trim($_GET['item_name']);
    $itemIds = [];
    foreach ($variants as $v) {
        $itemBaseName = isset($itemMap[$v['item_id']]) ? $itemMap[$v['item_id']] : '';
        $colorName = isset($colorMap[$v['color_id']]) ? $colorMap[$v['color_id']] : '';
        $fullName = $itemBaseName . $colorName;
        if (mb_strpos($itemBaseName, $itemName) !== false || mb_strpos($colorName, $itemName) !== false || mb_strpos($fullName, $itemName) !== false) {
            $itemIds[] = $v['id'];
        }
    }
    if ($itemIds) {
        $where[] = "item_id IN (".implode(",", array_map('intval', $itemIds)).")";
    } else {
        $where[] = "0";
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
$dbh = $Sale->getPdo();
$sql = "SELECT * FROM sales $where_sql ORDER BY sale_date DESC, customer_id, id DESC";
$stmt = $dbh->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
$grouped = [];
foreach ($sales as $sale) {
    $key = $sale['customer_id'] . '_' . $sale['sale_date'];
    if (!isset($grouped[$key])) $grouped[$key] = [
        'customer_id' => $sale['customer_id'],
        'sale_date' => $sale['sale_date'],
        'notes' => $sale['notes'],
        'items' => []
    ];
    $grouped[$key]['items'][] = $sale;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>銷售記錄列表</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .main-title { color: #d2691e; text-align: center; margin-top: 2em; }
        .card { box-shadow: 0 2px 16px #ffb34733; }
        .btn-back { background: #ffb347; color: #fff; border: 1px solid #ffb347; }
        .btn-back:hover { background: #ffa500; color: #fff; }
        .card-action-bar { margin-top:0.7em; display:flex; gap:0.5em; flex-wrap:wrap; }
        @media (max-width: 700px) {
            .main-title { font-size: 1.2em; }
        }
    </style>
</head>
<body class="warm-bg">
    <div class="container py-4">
        <h1 class="main-title">銷售記錄列表</h1>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="../../index.php" class="btn btn-back btn-sm">返回首頁</a>
            <a href="add.php" class="btn btn-back btn-sm">＋ 新增銷售</a>
        </div>
        <?php foreach($grouped as $group): ?>
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <span class="fw-bold">客戶：</span>
                    <?php
                    $c = array_filter($customers, function($cu) use ($group) { return $cu['id'] == $group['customer_id']; });
                    $c = $c ? array_values($c)[0] : null;
                    echo $c ? htmlspecialchars($c['name']) : '無';
                    ?>
                </div>
                <div><span class="fw-bold">日期：</span><?= htmlspecialchars($group['sale_date']) ?></div>
                <div><span class="fw-bold">備註：</span><?= htmlspecialchars($group['notes']) ?></div>
                <div class="card-action-bar">
                    <a href="edit.php?customer_id=<?= $group['customer_id'] ?>&sale_date=<?= $group['sale_date'] ?>" class="btn btn-back btn-sm">編輯</a>
                    <a href="delete.php?customer_id=<?= $group['customer_id'] ?>&sale_date=<?= $group['sale_date'] ?>" class="btn btn-back btn-sm btn-danger" onclick="return confirm('確定要刪除這筆銷售紀錄嗎？')">刪除</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>商品</th>
                                <th>規格</th>
                                <th>數量</th>
                                <th>單價</th>
                                <th>總價</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($group['items'] as $sale): ?>
                            <tr>
                                <td><?= isset($variantMap[$sale['item_id']]) && isset($itemMap[$variantMap[$sale['item_id']]['item_id']]) ? htmlspecialchars($itemMap[$variantMap[$sale['item_id']]['item_id']]) : '' ?></td>
                                <td><?= isset($variantMap[$sale['item_id']]) && isset($colorMap[$variantMap[$sale['item_id']]['color_id']]) ? htmlspecialchars($colorMap[$variantMap[$sale['item_id']]['color_id']]) : '' ?></td>
                                <td><?= htmlspecialchars($sale['quantity']) ?></td>
                                <td><?= htmlspecialchars($sale['unit_price']) ?></td>
                                <td><?= htmlspecialchars($sale['total_price']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>