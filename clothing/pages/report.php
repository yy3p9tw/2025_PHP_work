<?php
require_once '../includes/db.php';
$Sale = new DB('sales');
$Item = new DB('items');
$Customer = new DB('customers');
$Category = new DB('categories');
$Variant = new DB('item_variants');
$Color = new DB('colors');
$items = $Item->all();
$variants = $Variant->all();
$colors = $Color->all();
// 建立對照表
$itemMap = [];
foreach($items as $i) $itemMap[$i['id']] = $i;
$variantMap = [];
foreach($variants as $v) $variantMap[$v['id']] = $v;
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];

// === 新增：找出有任一規格庫存不足的商品 id ===
$lowStockItemIds = [];
foreach ($variants as $v) {
    if (isset($v['stock'], $v['min_stock']) && $v['stock'] <= $v['min_stock']) {
        $lowStockItemIds[$v['item_id']] = true;
    }
}
// 取得今天與本月日期
$today = date('Y-m-d');
$thisMonth = date('Y-m');
$sales = $Sale->all();
// 篩選
$todaySales = [];
$monthSales = [];
$allSales = $sales;
$todayTotal = 0;
$monthTotal = 0;
foreach ($sales as $sale) {
    if (isset($sale['sale_date'])) {
        if ($sale['sale_date'] == $today) {
            $todayTotal += $sale['total_price'];
            $todaySales[] = $sale;
        }
        if (strpos($sale['sale_date'], $thisMonth) === 0) {
            $monthTotal += $sale['total_price'];
            $monthSales[] = $sale;
        }
    }
}
// 低庫存商品 id 陣列
$lowStockItemIds = [];
foreach ($items as $item) {
    $itemVariants = array_filter($variants, function($v) use ($item) {
        return $v['item_id'] == $item['id'];
    });
    foreach ($itemVariants as $v) {
        if (isset($v['min_stock']) && isset($v['stock']) && $v['stock'] <= $v['min_stock']) {
            $lowStockItemIds[] = $item['id'];
            break; // 只要有一個規格低庫存就記錄
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>銷售統計報表</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
    .report-btns { text-align:center; margin-bottom:2em; }
    .report-btns button { margin:0 0.5em 0.5em 0.5em; font-size:1em; padding:0.5em 1.5em; border-radius:8px; border:none; background:linear-gradient(135deg,#ffb347 0%,#ff9966 100%); color:#fff; font-weight:500; cursor:pointer; box-shadow:0 2px 8px #ffb34744; transition:background 0.18s; }
    .report-btns button.active, .report-btns button:focus { background:linear-gradient(135deg,#ff9966 0%,#ffb347 100%); outline:none; }
    .low-stock-row { background: #ffe0e0 !important; }
    </style>
</head>
<body class="warm-bg">
    <h1 class="main-title">銷售統計報表</h1>
    <div class="card form-card form-container" style="max-width:700px;">
        <div class="report-btns">
            <button id="btn-today" class="active" onclick="showReport('today');return false;">今日</button>
            <button id="btn-month" onclick="showReport('month');return false;">本月</button>
            <button id="btn-all" onclick="showReport('all');return false;">總計</button>
        </div>
        <div id="report-today" class="report-table-block">
            <div style="text-align:center; margin-bottom:1em;">
                <span style="font-size:1.1em;color:#b97a56;">今日營業額</span><br>
                <span style="font-size:2em;color:#d2691e;"><?= number_format($todayTotal, 0) ?> 元</span>
            </div>
            <div style="text-align:right;margin-bottom:1em;">
                <a href="export_sales_today.php" class="btn-back" style="background:#ffb347;color:#fff;padding:0.4em 1.2em;border-radius:6px;">匯出今日銷售明細(CSV)</a>
            </div>
            <h2 style="color:#d2691e;text-align:center;font-size:1.1em;margin-bottom:1em;">今日銷售明細</h2>
            <table style="width:100%;background:#fff7f0;border-radius:8px;box-shadow:0 1px 6px #ffb34722;">
                <tr style="background:#fff0e0;">
                    <th>商品</th><th>顏色</th><th>成本</th><th>售價</th><th>數量</th><th>總價</th>
                </tr>
                <?php foreach($todaySales as $sale): ?>
                <?php $variant = $variantMap[$sale['item_id']] ?? null;
                      $item = $variant && isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : null;
                      $color = $variant && isset($colorMap[$variant['color_id']]) ? $colorMap[$variant['color_id']] : '';
                      $isLowStock = $item && isset($lowStockItemIds[$item['id']]);
                ?>
                <tr<?= $isLowStock ? ' style="background:#ffe0e0;"' : '' ?>>
                    <td><?= $item ? htmlspecialchars($item['name']) : '<span style="color:#aaa;">(已刪除)</span>' ?></td>
                    <td><?= $color ?></td>
                    <td><?= $variant ? intval($variant['cost_price']) : '-' ?></td>
                    <td><?= $variant ? intval($variant['sell_price']) : '-' ?></td>
                    <td><?= intval($sale['quantity']) ?></td>
                    <td><?= intval($sale['total_price']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($todaySales)): ?>
                <tr><td colspan="6" style="text-align:center;color:#aaa;">今日無銷售紀錄</td></tr>
                <?php endif; ?>
            </table>
        </div>
        <div id="report-month" class="report-table-block" style="display:none;">
            <div style="text-align:center; margin-bottom:1em;">
                <span style="font-size:1.1em;color:#b97a56;">本月營業額</span><br>
                <span style="font-size:2em;color:#d2691e;"><?= number_format($monthTotal, 0) ?> 元</span>
            </div>
            <div style="text-align:right;margin-bottom:1em;">
                <a href="export_sales_month.php" class="btn-back" style="background:#ffb347;color:#fff;padding:0.4em 1.2em;border-radius:6px;">匯出本月銷售明細(CSV)</a>
            </div>
            <h2 style="color:#d2691e;text-align:center;font-size:1.1em;margin-bottom:1em;">本月銷售明細</h2>
            <table style="width:100%;background:#fff7f0;border-radius:8px;box-shadow:0 1px 6px #ffb34722;">
                <tr style="background:#fff0e0;">
                    <th>商品</th><th>顏色</th><th>成本</th><th>售價</th><th>數量</th><th>總價</th>
                </tr>
                <?php foreach($monthSales as $sale): ?>
                <?php $variant = $variantMap[$sale['item_id']] ?? null;
                      $item = $variant && isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : null;
                      $color = $variant && isset($colorMap[$variant['color_id']]) ? $colorMap[$variant['color_id']] : '';
                      $isLowStock = $item && isset($lowStockItemIds[$item['id']]);
                ?>
                <tr<?= $isLowStock ? ' style="background:#ffe0e0;"' : '' ?>>
                    <td><?= $item ? htmlspecialchars($item['name']) : '<span style="color:#aaa;">(已刪除)</span>' ?></td>
                    <td><?= $color ?></td>
                    <td><?= $variant ? intval($variant['cost_price']) : '-' ?></td>
                    <td><?= $variant ? intval($variant['sell_price']) : '-' ?></td>
                    <td><?= intval($sale['quantity']) ?></td>
                    <td><?= intval($sale['total_price']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($monthSales)): ?>
                <tr><td colspan="6" style="text-align:center;color:#aaa;">本月無銷售紀錄</td></tr>
                <?php endif; ?>
            </table>
        </div>
        <div id="report-all" class="report-table-block" style="display:none;">
            <div style="text-align:center; margin-bottom:1em;">
                <span style="font-size:1.1em;color:#b97a56;">總營業額</span><br>
                <span style="font-size:2em;color:#d2691e;"><?= number_format(array_sum(array_column($allSales, 'total_price')), 0) ?> 元</span>
            </div>
            <div style="text-align:right;margin-bottom:1em;">
                <a href="export_sales.php" class="btn-back" style="background:#ffb347;color:#fff;padding:0.4em 1.2em;border-radius:6px;">匯出全部銷售明細(CSV)</a>
            </div>
            <h2 style="color:#d2691e;text-align:center;font-size:1.1em;margin-bottom:1em;">全部銷售明細</h2>
            <table style="width:100%;background:#fff7f0;border-radius:8px;box-shadow:0 1px 6px #ffb34722;">
                <tr style="background:#fff0e0;">
                    <th>商品</th><th>顏色</th><th>成本</th><th>售價</th><th>數量</th><th>總價</th>
                </tr>
                <?php foreach($allSales as $sale): ?>
                <?php $variant = $variantMap[$sale['item_id']] ?? null;
                      $item = $variant && isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : null;
                      $color = $variant && isset($colorMap[$variant['color_id']]) ? $colorMap[$variant['color_id']] : '';
                      $isLowStock = $item && isset($lowStockItemIds[$item['id']]);
                ?>
                <tr<?= $isLowStock ? ' style="background:#ffe0e0;"' : '' ?>>
                    <td><?= $item ? htmlspecialchars($item['name']) : '<span style="color:#aaa;">(已刪除)</span>' ?></td>
                    <td><?= $color ?></td>
                    <td><?= $variant ? intval($variant['cost_price']) : '-' ?></td>
                    <td><?= $variant ? intval($variant['sell_price']) : '-' ?></td>
                    <td><?= intval($sale['quantity']) ?></td>
                    <td><?= intval($sale['total_price']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($allSales)): ?>
                <tr><td colspan="6" style="text-align:center;color:#aaa;">無銷售紀錄</td></tr>
                <?php endif; ?>
            </table>
        </div>
        <div style="text-align:center;margin-top:2em;">
            <a href="../index.php" class="btn btn-back">回首頁</a>
        </div>
    </div>
    <script>
    function showReport(type) {
        document.getElementById('report-today').style.display = (type==='today') ? '' : 'none';
        document.getElementById('report-month').style.display = (type==='month') ? '' : 'none';
        document.getElementById('report-all').style.display = (type==='all') ? '' : 'none';
        document.getElementById('btn-today').classList.toggle('active', type==='today');
        document.getElementById('btn-month').classList.toggle('active', type==='month');
        document.getElementById('btn-all').classList.toggle('active', type==='all');
    }
    </script>
</body>
</html>
