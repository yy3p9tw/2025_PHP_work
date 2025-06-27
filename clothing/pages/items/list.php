<?php
require_once '../../includes/db.php';
$Item = new DB('items');
$Category = new DB('categories');
$categories = $Category->all();
$Color = new DB('colors');
$colors = $Color->all();
$Size = new DB('sizes');
$sizes = $Size->all();
$Variant = new DB('item_variants');
$allVariants = $Variant->all();

// 商品名稱搜尋
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$itemWhere = [];
if ($search !== '') {
    $itemWhere['name LIKE'] = '%' . $search . '%';
}
$items = $Item->all($itemWhere);

// 建立分類、顏色與尺寸的 id=>name 對照表
$catMap = [];
foreach($categories as $c) $catMap[$c['id']] = $c['name'];
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
$sizeMap = [];
foreach($sizes as $s) $sizeMap[$s['id']] = $s['name'];

// 將 item_id 對應到所有 variants
$itemVariantsMap = [];
foreach($allVariants as $v) {
    $itemVariantsMap[$v['item_id']][] = $v;
}

// === 找出有任一規格低庫存的商品 id ===
$lowStockItemIds = [];
foreach ($itemVariantsMap as $itemId => $variants) {
    foreach ($variants as $v) {
        $stock = $v['stock'];
        $minStock = isset($v['min_stock']) && $v['min_stock'] !== '' ? $v['min_stock'] : 0;
        if ($stock <= $minStock) {
            $lowStockItemIds[$itemId] = true;
            break;
        }
    }
}

// === 商品依低庫存置頂排序 ===
$lowStockItems = [];
$normalItems = [];
foreach ($items as $item) {
    if (isset($lowStockItemIds[$item['id']])) {
        $lowStockItems[] = $item;
    } else {
        $normalItems[] = $item;
    }
}
$items = array_merge($lowStockItems, $normalItems);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品列表</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <div class="container py-4">
        <h1 class="main-title mb-4">商品列表</h1>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="../../index.php" class="btn btn-outline-secondary">返回首頁</a>
            <a href="add.php" class="btn btn-primary">＋ 新增商品</a>
            <a href="../colors/add.php" class="btn btn-warning">＋ 新增顏色</a>
            <a href="../categories/list.php" class="btn btn-success">＋ 新增分類</a>
            <a href="../sizes/list.php" class="btn btn-info">＋ 新增尺寸</a>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="get" class="row g-2 align-items-center mb-3 search-form">
                    <div class="col-auto">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="搜尋商品名稱..." class="form-control" autocomplete="off">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary">搜尋</button>
                    </div>
                </form>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach($items as $item):
                    $variants = $itemVariantsMap[$item['id']] ?? [];
                    $isLowStock = isset($lowStockItemIds[$item['id']]);
                ?>
                    <div class="col">
                        <div class="card product-card shadow-sm h-100<?= $isLowStock ? ' low-stock' : '' ?>">
                            <div class="card-body">
                                <div class="text-center mb-2">
                                    <?php if ($item['image']): ?>
                                        <img src="../../uploads/<?= htmlspecialchars($item['image']) ?>" class="rounded mb-2" style="max-width:60px;max-height:60px;box-shadow:0 1px 6px #ffb34733;">
                                    <?php endif; ?>
                                </div>
                                <h5 class="card-title mb-1">商品：<?= htmlspecialchars($item['name']) ?></h5>
                                <div class="mb-1">分類：<?= isset($catMap[$item['category_id']]) ? htmlspecialchars($catMap[$item['category_id']]) : '' ?></div>
                                <?php if (empty($variants)): ?>
                                    <div>顏色：-</div>
                                    <div>尺寸：-</div>
                                    <div>售價：-</div>
                                    <div>庫存：-</div>
                                <?php else: ?>
                                    <div class="fw-bold text-secondary small mb-1">規格：</div>
                                    <div class="d-flex flex-column gap-2">
                                    <?php foreach($variants as $v):
                                        $stock = $v['stock'];
                                        $minStock = isset($v['min_stock']) && $v['min_stock'] !== '' ? $v['min_stock'] : 0;
                                        $isVarLow = $stock <= $minStock;
                                    ?>
                                        <div class="variant-row rounded px-2 py-1 bg-light d-flex flex-wrap align-items-center gap-3<?= $isVarLow ? ' low-stock' : '' ?>">
                                            <span>顏色：<?= isset($colorMap[$v['color_id']]) ? htmlspecialchars($colorMap[$v['color_id']]) : '' ?></span>
                                            <span>尺寸：<?= isset($sizeMap[$v['size_id']]) ? htmlspecialchars($sizeMap[$v['size_id']]) : '' ?></span>
                                            <span>成本：<?= isset($v['cost_price']) ? number_format($v['cost_price'], 0) : '-' ?></span>
                                            <span>售價：<?= number_format($v['sell_price'], 0) ?></span>
                                            <span>庫存：<?= $isVarLow ? '<span class=\'text-danger fw-bold\'>' . htmlspecialchars($stock) . '</span>' : htmlspecialchars($stock) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white border-0 d-flex gap-2 flex-wrap">
                                <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">編輯</a>
                                <a href="delete.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>