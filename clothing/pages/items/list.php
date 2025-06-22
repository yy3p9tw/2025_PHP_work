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
    @media (max-width: 700px) {
        .main-title { font-size: 1.2em; }
        table, thead, tbody, th, td, tr { display: block; width: 100%; }
        thead { display: none; }
        tr { margin-bottom: 1.2em; background: #fff; border-radius: 10px; box-shadow: 0 1px 6px #ffb34722; }
        td { padding: 0.7em 1em; border: none; border-bottom: 1px solid #ffe0e0; position: relative; }
        td:before { content: attr(data-label); font-weight: bold; color: #b97a56; display: block; margin-bottom: 0.3em; }
        .btn-back { width: 100%; margin-bottom: 0.5em; }
    }
    /* 搜尋按鈕往上 15px */
    .search-form button { margin-top: -15px; }
    </style>
</head>
<body class="warm-bg">
    <div style="max-width:950px;margin:40px auto 0;">
        <h1 class="main-title">商品列表</h1>
        <div style="text-align:right;margin-bottom:1.5em;">
            <a href="../../index.php" class="btn-back">返回首頁</a>
            <a href="add.php" class="btn-back" style="margin-left:8px;">＋ 新增商品</a>
            <a href="../colors/add.php" class="btn-back" style="margin-left:8px;">＋ 新增顏色</a>
            <a href="../categories/list.php" class="btn-back" style="margin-left:8px;">＋ 新增分類</a>
        </div>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 16px #ffb34733;padding:2em 1em 1em 1em;">
        <div style="margin-bottom:1.5em;">
            <form method="get" class="search-form" style="margin-bottom:1.5em;display:flex;gap:1em;align-items:center;">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="搜尋商品名稱..." style="padding:0.5em 1em;border:1px solid #ffb347;border-radius:6px;font-size:1em;width:220px;">
                <button type="submit">搜尋</button>
            </form>
        </div>
        <table>
            <tr>
                <th>圖片</th>
                <th>名稱</th>
                <th>分類</th>
                <th>顏色</th>
                <th>成本</th>
                <th>售價</th>
                <th>庫存</th>
                <th>操作</th>
            </tr>
            <?php
            foreach($items as $item) {
                $variants = $itemVariantsMap[$item['id']] ?? [];
                $isLowStock = isset($lowStockItemIds[$item['id']]);
                if (empty($variants)) {
                    echo '<tr' . ($isLowStock ? ' style="background:#ffeaea;"' : '') . '>' .
                        '<td data-label="圖片" style="text-align:center;">' .
                            ($item['image'] ? '<img src="../../uploads/' . htmlspecialchars($item['image']) . '" style="max-width:60px;max-height:60px;border-radius:8px;box-shadow:0 1px 6px #ffb34733;">' : '') .
                        '</td>' .
                        '<td data-label="名稱">' . htmlspecialchars($item['name']) . '</td>' .
                        '<td data-label="分類">' . (isset($catMap[$item['category_id']]) ? htmlspecialchars($catMap[$item['category_id']]) : '') . '</td>' .
                        '<td data-label="顏色">-</td><td data-label="成本">-</td><td data-label="售價">-</td><td data-label="庫存">-</td>' .
                        '<td data-label="操作">' .
                            '<a href="edit.php?id=' . $item['id'] . '" class="btn-back" style="padding:0.3em 1em;font-size:0.95em;">編輯</a>' .
                            '<a href="delete.php?id=' . $item['id'] . '" class="btn-back" style="background:#fff0e0;color:#d2691e;padding:0.3em 1em;font-size:0.95em;border:1px solid #ffb347;" onclick="return confirm(\'確定要刪除嗎？\')">刪除</a>' .
                        '</td>' .
                    '</tr>';
                } else {
                    foreach($variants as $idx => $v) {
                        $stock = $v['stock'];
                        $minStock = isset($v['min_stock']) && $v['min_stock'] !== '' ? $v['min_stock'] : 0;
                        echo '<tr' . ($isLowStock ? ' style="background:#ffeaea;"' : '') . '>';
                        if($idx === 0) {
                            echo '<td data-label="圖片" style="text-align:center;" rowspan="' . count($variants) . '">' .
                                ($item['image'] ? '<img src="../../uploads/' . htmlspecialchars($item['image']) . '" style="max-width:60px;max-height:60px;border-radius:8px;box-shadow:0 1px 6px #ffb34733;">' : '') .
                            '</td>';
                            echo '<td data-label="名稱" rowspan="' . count($variants) . '">' . htmlspecialchars($item['name']) . '</td>';
                            echo '<td data-label="分類" rowspan="' . count($variants) . '">' . (isset($catMap[$item['category_id']]) ? htmlspecialchars($catMap[$item['category_id']]) : '') . '</td>';
                        }
                        echo '<td data-label="顏色">' . (isset($colorMap[$v['color_id']]) ? htmlspecialchars($colorMap[$v['color_id']]) : '') . ' <span style="color:#aaa;font-size:0.9em;">#' . $v['id'] . '</span></td>';
                        echo '<td data-label="成本" class="cost-price">' . number_format($v['cost_price'], 0) . '</td>';
                        echo '<td data-label="售價" class="sell-price">' . number_format($v['sell_price'], 0) . '</td>';
                        echo '<td data-label="庫存" class="stock-cell">';
                        if ($stock <= $minStock) {
                            echo '<span style="color:#d11c1c;font-weight:bold;">' . htmlspecialchars($stock) . '</span>';
                        } else {
                            echo htmlspecialchars($stock);
                        }
                        echo '</td>';
                        if($idx === 0) {
                            echo '<td data-label="操作" rowspan="' . count($variants) . '"><a href="edit.php?id=' . $item['id'] . '" class="btn-back" style="padding:0.3em 1em;font-size:0.95em;">編輯</a>';
                            echo '<a href="delete.php?id=' . $item['id'] . '" class="btn-back" style="background:#fff0e0;color:#d2691e;padding:0.3em 1em;font-size:0.95em;border:1px solid #ffb347;" onclick="return confirm(\'確定要刪除嗎？\')">刪除</a>';
                        }
                        if($idx === 0) echo '</td>';
                        echo '</tr>';
                    }
                }
            }
            ?>
        </table>
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