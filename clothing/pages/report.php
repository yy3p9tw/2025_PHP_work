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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>銷售統計報表</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
    .report-btns { text-align:center; margin-bottom:2em; }
    .report-btns button { margin:0 0.5em 0.5em 0.5em; font-size:1em; padding:0.5em 1.5em; border-radius:8px; border:none; background:linear-gradient(135deg,#ffb347 0%,#ff9966 100%); color:#fff; font-weight:500; cursor:pointer; box-shadow:0 2px 8px #ffb34744; transition:background 0.18s; }
    .report-btns button.active, .report-btns button:focus { background:linear-gradient(135deg,#ff9966 0%,#ffb347 100%); outline:none; }
    .low-stock-row { background: #ffe0e0 !important; }
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
            <div class="card-action-bar" style="text-align:right;margin-bottom:1em;display:flex;gap:0.5em;flex-wrap:wrap;justify-content:flex-end;">
                <a href="export_sales_today.php" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">匯出今日銷售明細(CSV)</a>
            </div>
            <h2 style="color:#d2691e;text-align:center;font-size:1.1em;margin-bottom:1em;">今日銷售明細</h2>
            <div class="grid">
                <?php foreach($todaySales as $sale): ?>
                <?php $variant = $variantMap[$sale['item_id']] ?? null;
                      $item = $variant && isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : null;
                      $color = $variant && isset($colorMap[$variant['color_id']]) ? $colorMap[$variant['color_id']] : '';
                      $isLowStock = $item && isset($lowStockItemIds[$item['id']]);
                ?>
                <div class="report-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;<?= $isLowStock ? 'background:#ffeaea;' : '' ?>">
                    <div><b>商品：</b><?= $item ? htmlspecialchars($item['name']) : '<span style="color:#aaa;">(已刪除)</span>' ?></div>
                    <div><b>顏色：</b><?= $color ?></div>
                    <div><b>售價：</b><?= $variant ? intval($variant['sell_price']) : '-' ?></div>
                    <div><b>數量：</b><?= intval($sale['quantity']) ?></div>
                    <div><b>總價：</b><?= intval($sale['total_price']) ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($todaySales)): ?>
                <div style="grid-column:1/-1;text-align:center;color:#aaa;">今日無銷售紀錄</div>
                <?php endif; ?>
            </div>
        </div>
        <div id="report-month" class="report-table-block" style="display:none;">
            <div style="text-align:center; margin-bottom:1em;">
                <span style="font-size:1.1em;color:#b97a56;">本月營業額</span><br>
                <span style="font-size:2em;color:#d2691e;"><?= number_format($monthTotal, 0) ?> 元</span>
            </div>
            <div class="card-action-bar" style="text-align:right;margin-bottom:1em;display:flex;gap:0.5em;flex-wrap:wrap;justify-content:flex-end;">
                <a href="export_sales_month.php" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">匯出本月銷售明細(CSV)</a>
            </div>
            <h2 style="color:#d2691e;text-align:center;font-size:1.1em;margin-bottom:1em;">本月銷售明細</h2>
            <div class="grid">
                <?php foreach($monthSales as $sale): ?>
                <?php $variant = $variantMap[$sale['item_id']] ?? null;
                      $item = $variant && isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : null;
                      $color = $variant && isset($colorMap[$variant['color_id']]) ? $colorMap[$variant['color_id']] : '';
                      $isLowStock = $item && isset($lowStockItemIds[$item['id']]);
                ?>
                <div class="report-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;<?= $isLowStock ? 'background:#ffeaea;' : '' ?>">
                    <div><b>商品：</b><?= $item ? htmlspecialchars($item['name']) : '<span style="color:#aaa;">(已刪除)</span>' ?></div>
                    <div><b>顏色：</b><?= $color ?></div>
                    <div><b>售價：</b><?= $variant ? intval($variant['sell_price']) : '-' ?></div>
                    <div><b>數量：</b><?= intval($sale['quantity']) ?></div>
                    <div><b>總價：</b><?= intval($sale['total_price']) ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($monthSales)): ?>
                <div style="grid-column:1/-1;text-align:center;color:#aaa;">本月無銷售紀錄</div>
                <?php endif; ?>
            </div>
        </div>
        <div id="report-all" class="report-table-block" style="display:none;">
            <div style="text-align:center; margin-bottom:1em;">
                <span style="font-size:1.1em;color:#b97a56;">總營業額</span><br>
                <span style="font-size:2em;color:#d2691e;"><?= number_format(array_sum(array_column($allSales, 'total_price')), 0) ?> 元</span>
            </div>
            <div class="card-action-bar" style="text-align:right;margin-bottom:1em;display:flex;gap:0.5em;flex-wrap:wrap;justify-content:flex-end;">
                <a href="export_sales.php" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">匯出全部銷售明細(CSV)</a>
            </div>
            <h2 style="color:#d2691e;text-align:center;font-size:1.1em;margin-bottom:1em;">全部銷售明細</h2>
            <div class="grid">
                <?php foreach($allSales as $sale): ?>
                <?php $variant = $variantMap[$sale['item_id']] ?? null;
                      $item = $variant && isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : null;
                      $color = $variant && isset($colorMap[$variant['color_id']]) ? $colorMap[$variant['color_id']] : '';
                      $isLowStock = $item && isset($lowStockItemIds[$item['id']]);
                ?>
                <div class="report-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;<?= $isLowStock ? 'background:#ffeaea;' : '' ?>">
                    <div><b>商品：</b><?= $item ? htmlspecialchars($item['name']) : '<span style="color:#aaa;">(已刪除)</span>' ?></div>
                    <div><b>顏色：</b><?= $color ?></div>
                    <div><b>售價：</b><?= $variant ? intval($variant['sell_price']) : '-' ?></div>
                    <div><b>數量：</b><?= intval($sale['quantity']) ?></div>
                    <div><b>總價：</b><?= intval($sale['total_price']) ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($allSales)): ?>
                <div style="grid-column:1/-1;text-align:center;color:#aaa;">無銷售紀錄</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-action-bar" style="text-align:center;margin-top:2em;display:flex;gap:0.5em;flex-wrap:wrap;justify-content:center;">
            <a href="../index.php" class="btn-back btn-sm">回首頁</a>
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
