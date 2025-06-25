<?php
require_once '../../includes/db.php';

// 取得參數
$customer_id = $_GET['customer_id'] ?? null;
$sale_date = $_GET['sale_date'] ?? null;
if (!$customer_id || !$sale_date) {
    echo "參數錯誤";
    exit;
}

$Sale = new DB('sales');
$Item = new DB('items');
$items = $Item->all();
$Variant = new DB('item_variants');
$variants = $Variant->all();
$Color = new DB('colors');
$colors = $Color->all();
$Category = new DB('categories');
$categories = $Category->all();
$catMap = [];
foreach($categories as $c) $catMap[$c['id']] = $c['name'];

// 建立查表
$itemMap = [];
foreach($items as $i) $itemMap[$i['id']] = $i['name'];
$itemFullMap = [];
foreach($items as $i) $itemFullMap[$i['id']] = $i; // 方便查分類、圖片
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
$variantMap = [];
foreach($variants as $v) $variantMap[$v['id']] = $v;

// 取得這張卡片所有銷售明細
$sales = $Sale->all("customer_id = $customer_id AND sale_date = '$sale_date'");

// 表單送出處理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 刪除勾選的明細
    if (!empty($_POST['delete_sales']) && is_array($_POST['delete_sales'])) {
        foreach ($_POST['delete_sales'] as $delId) {
            $Sale->delete($delId);
        }
    }
    // 更新所有銷售明細
    if (isset($_POST['sales']) && is_array($_POST['sales'])) {
        foreach ($_POST['sales'] as $saleId => $row) {
            // 先查詢原本的銷售明細
            $oldSale = $Sale->all("id = $saleId");
            $oldSale = $oldSale ? $oldSale[0] : null;
            if ($oldSale) {
                // 補回原本庫存
                $oldVariantId = $oldSale['item_id'];
                $oldQty = $oldSale['quantity'];
                $oldVariant = $Variant->all("id = $oldVariantId");
                $oldVariant = $oldVariant ? $oldVariant[0] : null;
                if ($oldVariant && isset($oldVariant['stock'])) {
                    $Variant->update($oldVariantId, ['stock' => $oldVariant['stock'] + $oldQty]);
                }
            }
            // 再扣除新數量
            $newVariantId = $row['variant_id'];
            $newQty = $row['quantity'];
            $newVariant = $Variant->all("id = $newVariantId");
            $newVariant = $newVariant ? $newVariant[0] : null;
            if ($newVariant && isset($newVariant['stock'])) {
                $Variant->update($newVariantId, ['stock' => $newVariant['stock'] - $newQty]);
            }
            // 更新銷售明細
            $Sale->update($saleId, [
                'item_id' => $row['variant_id'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'total_price' => $row['quantity'] * $row['unit_price'],
                'notes' => $_POST['notes'] ?? ''
            ]);
        }
    }
    header("Location: list.php?customer_id=$customer_id&date_from=$sale_date&date_to=$sale_date");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯銷售紀錄</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
    .main-title { font-size: 2rem; font-weight: bold; color: #b97a56; }
    .sale-variant-card { border: 1px solid #ffe0b2; border-radius: 10px; background: #fffdf7; }
    .sale-variant-card .form-label { font-weight: 500; }
    .discount-card { background: #fff7e0; border-radius: 10px; }
    </style>
</head>
<body class="warm-bg">
    <div class="container py-4">
        <h1 class="main-title mb-4">編輯銷售紀錄</h1>
        <form method="post" class="card shadow-sm mx-auto p-4" style="max-width:700px;">
            <input type="hidden" name="customer_id" value="<?= htmlspecialchars($customer_id) ?>">
            <div class="mb-3">
                <label class="form-label">備註：</label>
                <input type="text" name="notes" value="<?= htmlspecialchars($sales[0]['notes'] ?? '') ?>" class="form-control">
            </div>
            <hr>
            <div class="mb-3">
                <strong class="mb-2 d-block">商品與規格：</strong>
                <?php foreach($sales as $sale): 
                    $variant = $variantMap[$sale['item_id']] ?? null;
                    $item = ($variant && isset($itemFullMap[$variant['item_id']])) ? $itemFullMap[$variant['item_id']] : null;
                ?>
                <div class="sale-variant-card card mb-3 p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label">商品：</label>
                            <select name="sales[<?= $sale['id'] ?>][item_id]" class="form-select" disabled>
                                <?php foreach($items as $it): ?>
                                    <option value="<?= $it['id'] ?>" <?= ($variant && $it['id']==$variant['item_id'])?'selected':'' ?>>
                                        <?= htmlspecialchars($it['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">規格：</label>
                            <select name="sales[<?= $sale['id'] ?>][variant_id]" class="form-select">
                                <?php foreach($variants as $v): 
                                    if ($variant && $v['item_id'] != $variant['item_id']) continue;
                                ?>
                                    <option value="<?= $v['id'] ?>" <?= ($sale['item_id']==$v['id'])?'selected':'' ?>>
                                        <?= htmlspecialchars($colorMap[$v['color_id']] ?? '無顏色') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">分類：</label>
                            <div><?= $item ? htmlspecialchars($catMap[$item['category_id']] ?? '') : '' ?></div>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">數量：</label>
                            <input type="number" name="sales[<?= $sale['id'] ?>][quantity]" value="<?= $sale['quantity'] ?>" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">單價：</label>
                            <input type="number" name="sales[<?= $sale['id'] ?>][unit_price]" value="<?= $sale['unit_price'] ?>" class="form-control" required>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="delete_sales[]" value="<?= $sale['id'] ?>" id="del<?= $sale['id'] ?>">
                                <label class="form-check-label text-danger" for="del<?= $sale['id'] ?>">刪除此規格</label>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- 全部折扣價區塊 -->
            <div class="discount-card card p-3 mb-3">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="globalDiscountCheck">
                    <label class="form-check-label" for="globalDiscountCheck">全部折扣價</label>
                </div>
                <div class="input-group">
                    <input type="number" id="globalDiscountPrice" step="1" min="0" class="form-control" style="max-width:150px;" disabled placeholder="輸入折扣後總價">
                    <span class="input-group-text text-secondary">（勾選後所有商品規格總合為此價）</span>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">儲存</button>
                <a href="list.php" class="btn btn-outline-secondary">返回列表</a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
// 全部折扣價功能
const globalDiscountCheck = document.getElementById('globalDiscountCheck');
const globalDiscountPrice = document.getElementById('globalDiscountPrice');
globalDiscountCheck.onchange = function() {
    globalDiscountPrice.disabled = !this.checked;
};
document.querySelector('form').onsubmit = function(e) {
    if (globalDiscountCheck.checked && globalDiscountPrice.value) {
        // 取得所有商品小計
        let rows = [];
        let total = 0;
        document.querySelectorAll('input[name^="sales"][name$="[quantity]"]')?.forEach(inp => {
            const prefix = inp.name.replace(/\[quantity\].*$/, '');
            const qty = parseFloat(inp.value) || 0;
            const priceInp = document.querySelector(`input[name="${prefix}[unit_price]"]`);
            const unit = parseFloat(priceInp.value) || 0;
            const subtotal = qty * unit;
            rows.push({qty, priceInp, subtotal});
            total += subtotal;
        });
        // 若原本總價為0，則平均分配
        let discountTotal = parseFloat(globalDiscountPrice.value) || 0;
        if (total === 0 && rows.length > 0) {
            let avg = Math.floor(discountTotal / rows.length);
            rows.forEach(r => r.priceInp.value = avg);
            // 若有餘數，分配到前幾個
            let remain = discountTotal - avg * rows.length;
            for (let i = 0; i < remain; i++) rows[i].priceInp.value = avg + 1;
        } else if (total > 0) {
            // 依比例分配
            let remain = discountTotal;
            rows.forEach((r, idx) => {
                let val = idx === rows.length - 1
                    ? remain // 最後一筆補齊
                    : Math.round(discountTotal * r.subtotal / total);
                let price = r.qty > 0 ? Math.floor(val / r.qty) : 0;
                r.priceInp.value = price;
                remain -= price * r.qty;
            });
        }
    }
    // 送出
    return true;
};
</script>
</body>
</html>
