<?php
require_once '../includes/db.php';
$Sale = new DB('sales');
$SaleItem = new DB('sale_items');
$Item = new DB('items');
$Customer = new DB('customers');
$Category = new DB('categories');
$Variant = new DB('item_variants');
$Color = new DB('colors');
$Size = new DB('sizes');

$items = $Item->all();
$variants = $Variant->all();
$colors = $Color->all();
$sizes = $Size->all();

// 建立對照表
$itemMap = [];
foreach($items as $i) $itemMap[$i['id']] = $i;
$variantMap = [];
foreach($variants as $v) $variantMap[$v['id']] = $v;
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
$sizeMap = [];
foreach($sizes as $s) $sizeMap[$s['id']] = $s['name'];

// 取得今天與本月日期
$today = date('Y-m-d');
$thisMonth = date('Y-m');

// 獲取所有銷售明細 (用於計算)
$allSaleItems = $SaleItem->all();

// 篩選銷售明細並計算總計
$todaySales = [];
$monthSales = [];
$allSales = [];
$todayTotal = 0;
$monthTotal = 0;
$todayCost = 0;
$monthCost = 0;
$allTotal = 0; // 新增：總營業額
$allCost = 0;

foreach ($allSaleItems as $sale_item) {
    $sale = $Sale->find(['id' => $sale_item['sale_id']]);
    if (!$sale) continue; // 如果銷售主表不存在，跳過

    $variant = $variantMap[$sale_item['item_variant_id']] ?? null;
    $cost = $variant ? floatval($variant['cost_price']) : 0;

    $sale_item['sale_date'] = date('Y-m-d', strtotime($sale['sale_date']));
    $sale_item['total_price'] = $sale_item['quantity'] * $sale_item['price_at_sale'];

    // 今日銷售
    if ($sale_item['sale_date'] == $today) {
        $todaySales[] = $sale_item;
        $todayTotal += $sale_item['total_price'];
        $todayCost += $cost * $sale_item['quantity'];
    }

    // 本月銷售
    if (strpos($sale_item['sale_date'], $thisMonth) === 0) {
        $monthSales[] = $sale_item;
        $monthTotal += $sale_item['total_price'];
        $monthCost += $cost * $sale_item['quantity'];
    }

    // 所有銷售
    $allSales[] = $sale_item;
    $allTotal += $sale_item['total_price']; // 計算總營業額
    $allCost += $cost * $sale_item['quantity'];
}

// 低庫存商品 id 陣列
$lowStockItemIds = [];
foreach ($variants as $v) {
    if (isset($v['stock'], $v['min_stock']) && $v['stock'] <= $v['min_stock']) {
        $lowStockItemIds[$v['item_id']] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>銷售統計報表</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="warm-bg">
    <div class="container py-4">
        <h1 class="main-title">銷售統計報表</h1>
        <div class="card form-card form-container mx-auto" style="max-width:700px;">
            <div class="report-btns">
                <button id="btn-today" class="btn btn-sm btn-outline-secondary active" onclick="showReport('today');return false;">今日</button>
                <button id="btn-month" class="btn btn-sm btn-outline-secondary" onclick="showReport('month');return false;">本月</button>
                <button id="btn-all" class="btn btn-sm btn-outline-secondary" onclick="showReport('all');return false;">總計</button>
            </div>
            <div id="report-today" class="report-table-block">
                <div class="text-center mb-3">
                    <span class="fs-6 text-secondary">今日營業額</span><br>
                    <span class="fs-2" style="color:#d2691e;"><?= number_format($todayTotal, 0) ?> 元</span><br>
                    <span class="fs-6 text-muted">今日成本：<?= number_format($todayCost, 0) ?> 元</span>
                </div>
                <div class="d-flex justify-content-end mb-2">
                    <a href="export_sales_today.php" class="btn btn-outline-primary btn-sm">匯出今日銷售明細(CSV)</a>
                </div>
                <h2 class="text-center mb-3" style="color:#d2691e;font-size:1.1em;">今日銷售明細</h2>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php if (!empty($todaySales)): ?>
                        <?php foreach($todaySales as $sale_item): ?>
                        <?php 
                            $variant = $variantMap[$sale_item['item_variant_id']] ?? null;
                            $item = $variant && isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : null;
                            $color = $variant && isset($colorMap[$variant['color_id']]) ? $colorMap[$variant['color_id']] : '';
                            $size = $variant && isset($sizeMap[$variant['size_id']]) ? $sizeMap[$variant['size_id']] : '';
                            $isLowStock = $item && isset($lowStockItemIds[$item['id']]);
                        ?>
                        <div class="col">
                            <div class="card h-100<?= $isLowStock ? ' border-danger' : '' ?>">
                                <div class="card-body">
                                    <div><b>商品：</b><?= $item ? htmlspecialchars($item['name']) : '<span class="text-muted">(已刪除)</span>' ?></div>
                                    <div><b>規格：</b><?= htmlspecialchars($color) ?> / <?= htmlspecialchars($size) ?></div>
                                    <div><b>售價：</b><?= number_format($sale_item['price_at_sale'], 0) ?></div>
                                    <div><b>數量：</b><?= intval($sale_item['quantity']) ?></div>
                                    <div><b>總價：</b><?= number_format($sale_item['total_price'], 0) ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col text-center text-muted">今日無銷售紀錄</div>
                    <?php endif; ?>
                </div>
            </div>
            <div id="report-month" class="report-table-block" style="display:none;">
                <div class="text-center mb-3">
                    <span class="fs-6 text-secondary">本月營業額</span><br>
                    <span class="fs-2" style="color:#d2691e;"><?= number_format($monthTotal, 0) ?> 元</span><br>
                    <span class="fs-6 text-muted">本月成本：<?= number_format($monthCost, 0) ?> 元</span>
                </div>
                <div class="d-flex justify-content-end mb-2">
                    <a href="export_sales_month.php" class="btn btn-outline-primary btn-sm">匯出本月銷售明細(CSV)</a>
                </div>
                <h2 class="text-center mb-3" style="color:#d2691e;font-size:1.1em;">本月銷售明細</h2>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php if (!empty($monthSales)): ?>
                        <?php foreach($monthSales as $sale_item): ?>
                        <?php 
                            $variant = $variantMap[$sale_item['item_variant_id']] ?? null;
                            $item = $variant && isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : null;
                            $color = $variant && isset($colorMap[$variant['color_id']]) ? $colorMap[$variant['color_id']] : '';
                            $size = $variant && isset($sizeMap[$variant['size_id']]) ? $sizeMap[$variant['size_id']] : '';
                            $isLowStock = $item && isset($lowStockItemIds[$item['id']]);
                        ?>
                        <div class="col">
                            <div class="card h-100<?= $isLowStock ? ' border-danger' : '' ?>">
                                <div class="card-body">
                                    <div><b>商品：</b><?= $item ? htmlspecialchars($item['name']) : '<span class="text-muted">(已刪除)</span>' ?></div>
                                    <div><b>規格：</b><?= htmlspecialchars($color) ?> / <?= htmlspecialchars($size) ?></div>
                                    <div><b>售價：</b><?= number_format($sale_item['price_at_sale'], 0) ?></div>
                                    <div><b>數量：</b><?= intval($sale_item['quantity']) ?></div>
                                    <div><b>總價：</b><?= number_format($sale_item['total_price'], 0) ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col text-center text-muted">本月無銷售紀錄</div>
                    <?php endif; ?>
                </div>
            </div>
            <div id="report-all" class="report-table-block" style="display:none;">
                <div class="text-center mb-3">
                    <span class="fs-6 text-secondary">總營業額</span><br>
                    <span class="fs-2" style="color:#d2691e;"><?= number_format($allTotal, 0) ?> 元</span><br>
                    <span class="fs-6 text-muted">總計成本：<?= number_format($allCost, 0) ?> 元</span>
                </div>
                <div class="d-flex justify-content-end mb-2">
                    <a href="export_sales.php" class="btn btn-outline-primary btn-sm">匯出全部銷售明細(CSV)</a>
                </div>
                <h2 class="text-center mb-3" style="color:#d2691e;font-size:1.1em;">全部銷售明細</h2>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php if (!empty($allSales)): ?>
                        <?php foreach($allSales as $sale_item): ?>
                        <?php 
                            $variant = $variantMap[$sale_item['item_variant_id']] ?? null;
                            $item = $variant && isset($itemMap[$variant['item_id']]) ? $itemMap[$variant['item_id']] : null;
                            $color = $variant && isset($colorMap[$variant['color_id']]) ? $colorMap[$variant['color_id']] : '';
                            $size = $variant && isset($sizeMap[$variant['size_id']]) ? $sizeMap[$variant['size_id']] : '';
                            $isLowStock = $item && isset($lowStockItemIds[$item['id']]);
                        ?>
                        <div class="col">
                            <div class="card h-100<?= $isLowStock ? ' border-danger' : '' ?>">
                                <div class="card-body">
                                    <div><b>商品：</b><?= $item ? htmlspecialchars($item['name']) : '<span class="text-muted">(已刪除)</span>' ?></div>
                                    <div><b>規格：</b><?= htmlspecialchars($color) ?> / <?= htmlspecialchars($size) ?></div>
                                    <div><b>售價：</b><?= number_format($sale_item['price_at_sale'], 0) ?></div>
                                    <div><b>數量：</b><?= intval($sale_item['quantity']) ?></div>
                                    <div><b>總價：</b><?= number_format($sale_item['total_price'], 0) ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col text-center text-muted">無銷售紀錄</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex justify-content-center mt-4">
                <a href="../index.php" class="btn btn-outline-secondary">回首頁</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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