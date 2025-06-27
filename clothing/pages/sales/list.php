<?php
require_once '../../includes/db.php';
$Sale = new DB('sales');
$SaleItem = new DB('sale_items');
$Item = new DB('items');
$Customer = new DB('customers');
$Variant = new DB('item_variants');
$Color = new DB('colors');
$Size = new DB('sizes');

$items = $Item->all();
$customers = $Customer->all();
$variants = $Variant->all();
$colors = $Color->all();
$sizes = $Size->all();

// 建立對照表
$itemMap = [];
foreach($items as $i) $itemMap[$i['id']] = $i['name'];
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
$sizeMap = [];
foreach($sizes as $s) $sizeMap[$s['id']] = $s['name'];
$variantMap = [];
foreach($variants as $v) $variantMap[$v['id']] = $v;

$where = [];

if (!empty($_GET['customer_id'])) {
    $where['customer_id'] = $_GET['customer_id'];
}

if (!empty($_GET['item_name'])) {
    $itemName = trim($_GET['item_name']);
    $matchingVariantIds = [];
    foreach ($variants as $v) {
        $itemBaseName = $itemMap[$v['item_id']] ?? '';
        $colorName = $colorMap[$v['color_id']] ?? '';
        $sizeName = $sizeMap[$v['size_id']] ?? '';
        $fullName = $itemBaseName . $colorName . $sizeName;

        if (mb_strpos($itemBaseName, $itemName) !== false || mb_strpos($colorName, $itemName) !== false || mb_strpos($sizeName, $itemName) !== false || mb_strpos($fullName, $itemName) !== false) {
            $matchingVariantIds[] = $v['id'];
        }
    }
    if (!empty($matchingVariantIds)) {
        $sale_items_with_matching_variants = $SaleItem->all(['item_variant_id IN' => '(' . implode(',', $matchingVariantIds) . ')']);
        $matching_sale_ids = array_column($sale_items_with_matching_variants, 'sale_id');
        if (!empty($matching_sale_ids)) {
            $where['id IN'] = '(' . implode(',', array_unique($matching_sale_ids)) . ')';
        } else {
            $where['id ='] = 0;
        }
    } else {
        $where['id ='] = 0;
    }
}

if (!empty($_GET['date_from'])) {
    $where['sale_date >='] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $where['sale_date <='] = $_GET['date_to'];
}

$sales = $Sale->all($where, 'sale_date DESC, customer_id, id DESC');

$groupedSales = [];
foreach ($sales as $sale) {
    $sale_items = $SaleItem->all(['sale_id' => $sale['id']]);
    $sale['items'] = $sale_items;

    $key = $sale['customer_id'] . '_' . $sale['sale_date'];
    if (!isset($groupedSales[$key])) {
        $groupedSales[$key] = [
            'id' => $sale['id'],
            'customer_id' => $sale['customer_id'],
            'sale_date' => $sale['sale_date'],
            'total_amount' => $sale['total_amount'],
            'items' => []
        ];
    }
    $groupedSales[$key]['items'] = array_merge($groupedSales[$key]['items'], $sale['items']);
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
</head>
<body class="warm-bg">
    <div class="container py-4">
        <h1 class="main-title">銷售記錄列表</h1>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="../../index.php" class="btn btn-outline-secondary btn-sm">返回首頁</a>
            <a href="add.php" class="btn btn-primary btn-sm">＋ 新增銷售</a>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="get" class="row g-2 align-items-center mb-3 search-form">
                    <div class="col-auto">
                        <select name="customer_id" class="form-select">
                            <option value="">所有客戶</option>
                            <?php foreach($customers as $cust): ?>
                                <option value="<?= $cust['id'] ?>" <?= (isset($_GET['customer_id']) && $_GET['customer_id'] == $cust['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cust['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="text" name="item_name" value="<?= htmlspecialchars($_GET['item_name'] ?? '') ?>" placeholder="搜尋商品名稱..." class="form-control" autocomplete="off">
                    </div>
                    <div class="col-auto">
                        <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-auto">
                        <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary">搜尋</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($groupedSales)): ?>
            <?php foreach($groupedSales as $group): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">客戶：</span>
                        <?php
                        $customer_name = '無';
                        foreach($customers as $cust) {
                            if ($cust['id'] == $group['customer_id']) {
                                $customer_name = htmlspecialchars($cust['name']);
                                break;
                            }
                        }
                        echo $customer_name;
                        ?>
                    </div>
                    <div><span class="fw-bold">日期：</span><?= htmlspecialchars($group['sale_date']) ?></div>
                    <div><span class="fw-bold">總金額：</span>$<?= number_format($group['total_amount'], 2) ?></div>
                    <div class="d-flex gap-2">
                        <a href="edit.php?id=<?= $group['id'] ?>" class="btn btn-outline-primary btn-sm">編輯</a>
                        <a href="delete.php?id=<?= $group['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('確定要刪除這筆銷售紀錄嗎？')">刪除</a>
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
                            <?php foreach($group['items'] as $saleItem): ?>
                                <?php
                                $variant = $variantMap[$saleItem['item_variant_id']] ?? null;
                                $item_name = '-';
                                $variant_spec = '-';
                                if ($variant) {
                                    $item_name = $itemMap[$variant['item_id']] ?? '-';
                                    $color_name = $colorMap[$variant['color_id']] ?? '-';
                                    $size_name = $sizeMap[$variant['size_id']] ?? '-';
                                    $variant_spec = htmlspecialchars($color_name) . ' / ' . htmlspecialchars($size_name);
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($item_name) ?></td>
                                    <td><?= $variant_spec ?></td>
                                    <td><?= htmlspecialchars($saleItem['quantity']) ?></td>
                                    <td><?= number_format($saleItem['price_at_sale'], 0) ?></td>
                                    <td><?= number_format($saleItem['quantity'] * $saleItem['price_at_sale'], 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info text-center" role="alert">
                目前沒有任何銷售記錄。
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>