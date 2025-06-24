<?php
require_once '../../includes/db.php';
$Item = new DB('items');
$Category = new DB('categories');
$categories = $Category->all();
$Color = new DB('colors');
$colors = $Color->all();
$Variant = new DB('item_variants');
$allVariants = $Variant->all();
// 商品名稱搜尋
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $items = $Item->all("name LIKE '%" . addslashes($search) . "%'");
} else {
    $items = $Item->all();
}
// 建立分類與顏色的 id=>name 對照表
$catMap = [];
foreach($categories as $c) $catMap[$c['id']] = $c['name'];
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
// 將 item_id 對應到所有 variants
$itemVariantsMap = [];
foreach($allVariants as $v) {
    $itemVariantsMap[$v['item_id']][] = $v;
}
// === 新增：找出有任一規格低庫存的商品 id ===
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
// === 新增：商品依低庫存置頂排序 ===
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
    <link rel="stylesheet" href="../../css/style.css">
    <style>
    @media (max-width: 768px) {
        .main-title { font-size: 1.2em; }
        table, thead, tbody, th, td, tr { display: block; width: 100%; }
        thead { display: none; }
        tr { margin-bottom: 1.2em; background: #fff; border-radius: 10px; box-shadow: 0 1px 6px #ffb34722; }
        td { padding: 0.7em 1em; border: none; border-bottom: 1px solid #ffe0e0; position: relative; }
        td:before { content: attr(data-label); font-weight: bold; color: #b97a56; display: block; margin-bottom: 0.3em; }
        .btn-back { width: 100%; margin-bottom: 0.5em; }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5em;
        }
    }

    /* 搜尋按鈕往上 15px */
    .search-form button { margin-top: -15px; }
    </style>
</head>
<body class="warm-bg">
    <div style="max-width:950px;margin:40px auto 0;">
        <h1 class="main-title">商品列表</h1>
        <div class="action-bar" style="margin-bottom:1.5em;">
            <a href="../../index.php" class="btn-back">返回首頁</a>
            <a href="add.php" class="btn-back">＋ 新增商品</a>
            <a href="../colors/add.php" class="btn-back">＋ 新增顏色</a>
            <a href="../categories/list.php" class="btn-back">＋ 新增分類</a>
        </div>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 16px #ffb34733;padding:2em 1em 1em 1em;">
        <div style="margin-bottom:1.5em;">
            <form method="get" class="search-form" style="margin-bottom:1.5em;display:flex;gap:1em;align-items:center;">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="搜尋商品名稱..." style="padding:0.5em 1em;border:1px solid #ffb347;border-radius:6px;font-size:1em;width:220px;">
                <button type="submit">搜尋</button>
            </form>
        </div>
        <div class="grid">
        <?php foreach($items as $item):
            $variants = $itemVariantsMap[$item['id']] ?? [];
            $isLowStock = isset($lowStockItemIds[$item['id']]);
            if (empty($variants)):
        ?>
            <div class="product-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;<?= $isLowStock ? 'background:#ffeaea;' : '' ?>">
                <div style="text-align:center;">
                    <?php if ($item['image']): ?>
                        <img src="../../uploads/<?= htmlspecialchars($item['image']) ?>" style="max-width:60px;max-height:60px;border-radius:8px;box-shadow:0 1px 6px #ffb34733;">
                    <?php endif; ?>
                </div>
                <div style="font-weight:bold;font-size:1.1em;margin:0.5em 0;">商品：<?= htmlspecialchars($item['name']) ?></div>
                <div>分類：<?= isset($catMap[$item['category_id']]) ? htmlspecialchars($catMap[$item['category_id']]) : '' ?></div>
                <div>顏色：-</div>
                <div>成本：-</div>
                <div>售價：-</div>
                <div>庫存：-</div>
                <div class="card-action-bar" style="margin-top:0.7em;display:flex;gap:0.5em;flex-wrap:wrap;">
                    <a href="edit.php?id=<?= $item['id'] ?>" class="btn-back btn-sm">編輯</a>
                    <a href="delete.php?id=<?= $item['id'] ?>" class="btn-back btn-sm btn-del" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                </div>
            </div>
        <?php else:
            foreach($variants as $v):
                $stock = $v['stock'];
                $minStock = isset($v['min_stock']) && $v['min_stock'] !== '' ? $v['min_stock'] : 0;
        ?>
            <div class="product-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;<?= $isLowStock ? 'background:#ffeaea;' : '' ?>">
                <div style="text-align:center;">
                    <?php if ($item['image']): ?>
                        <img src="../../uploads/<?= htmlspecialchars($item['image']) ?>" style="max-width:60px;max-height:60px;border-radius:8px;box-shadow:0 1px 6px #ffb34733;">
                    <?php endif; ?>
                </div>
                <div style="font-weight:bold;font-size:1.1em;margin:0.5em 0;">商品：<?= htmlspecialchars($item['name']) ?></div>
                <div>分類：<?= isset($catMap[$item['category_id']]) ? htmlspecialchars($catMap[$item['category_id']]) : '' ?></div>
                <div>顏色：<?= isset($colorMap[$v['color_id']]) ? htmlspecialchars($colorMap[$v['color_id']]) : '' ?> <span style="color:#aaa;font-size:0.9em;">#<?= $v['id'] ?></span></div>
                <div>售價：<?= number_format($v['sell_price'], 0) ?></div>
                <div>庫存：<?php if ($stock <= $minStock): ?><span style="color:#d11c1c;font-weight:bold;"><?= htmlspecialchars($stock) ?></span><?php else: ?><?= htmlspecialchars($stock) ?><?php endif; ?></div>
                <div class="card-action-bar" style="margin-top:0.7em;display:flex;gap:0.5em;flex-wrap:wrap;">
                    <a href="edit.php?id=<?= $item['id'] ?>" class="btn-back btn-sm">編輯</a>
                    <a href="delete.php?id=<?= $item['id'] ?>" class="btn-back btn-sm btn-del" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                </div>
            </div>
        <?php endforeach; endif; endforeach; ?>
        </div>
        </div>
    </div>
    <script>
    document.getElementById('addColorMainBtn').onclick = function() {
        let colorName = prompt('請輸入新顏色名稱');
        if (!colorName) return;
        fetch('../colors/ajax_add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'name=' + encodeURIComponent(colorName)
        })
        .then(r => r.json())
        .then(data => {
            if (data.id && !isNaN(data.id)) {
                alert('新增顏色成功！\n請到新增/編輯商品頁選用新顏色。');
            } else {
                alert('新增失敗');
            }
        });
    };
    </script>
</body>
</html>