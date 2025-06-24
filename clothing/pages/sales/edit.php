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
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <h1 class="main-title">編輯銷售紀錄</h1>
    <form method="post" class="form-container card" style="max-width:600px;margin:auto;">
        <input type="hidden" name="customer_id" value="<?= htmlspecialchars($customer_id) ?>">
        <label>備註：<input type="text" name="notes" value="<?= htmlspecialchars($sales[0]['notes'] ?? '') ?>"></label>
        <hr>
        <div>
            <strong>商品與規格：</strong>
            <?php foreach($sales as $sale): 
                $variant = $variantMap[$sale['item_id']] ?? null;
                $item = ($variant && isset($itemFullMap[$variant['item_id']])) ? $itemFullMap[$variant['item_id']] : null;
            ?>
            <div style="border:1px solid #ffe0b2;padding:1em;margin-bottom:1em;border-radius:8px;">
                <label>商品：
                    <select name="sales[<?= $sale['id'] ?>][item_id]" disabled>
                        <?php foreach($items as $it): ?>
                            <option value="<?= $it['id'] ?>" <?= ($variant && $it['id']==$variant['item_id'])?'selected':'' ?>>
                                <?= htmlspecialchars($it['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>規格：
                    <select name="sales[<?= $sale['id'] ?>][variant_id]">
                        <?php foreach($variants as $v): 
                            if ($variant && $v['item_id'] != $variant['item_id']) continue;
                        ?>
                            <option value="<?= $v['id'] ?>" <?= ($sale['item_id']==$v['id'])?'selected':'' ?>>
                                <?= htmlspecialchars($colorMap[$v['color_id']] ?? '無顏色') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>分類：<?= $item ? htmlspecialchars($catMap[$item['category_id']] ?? '') : '' ?></label>
                <label>數量：<input type="number" name="sales[<?= $sale['id'] ?>][quantity]" value="<?= $sale['quantity'] ?>" required></label>
                <label>單價：<input type="number" name="sales[<?= $sale['id'] ?>][unit_price]" value="<?= $sale['unit_price'] ?>" required></label>
                <label style="color:#d2691e;margin-left:1em;">
                    <input type="checkbox" name="delete_sales[]" value="<?= $sale['id'] ?>"> 刪除此規格
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- 全部折扣價區塊 -->
        <div style="margin:1em 0 0.5em 0;padding:1em;background:#fff7e0;border-radius:8px;">
            <label style="display:flex;align-items:center;gap:0.5em;">
                <input type="checkbox" id="globalDiscountCheck"> 全部折扣價
                <input type="number" id="globalDiscountPrice" step="1" min="0" style="width:110px;" disabled placeholder="輸入折扣後總價">
                <span style="color:#b97a56;font-size:0.95em;">（勾選後所有商品規格總合為此價）</span>
            </label>
        </div>
        <div class="card-action-bar" style="margin-top:1.2em;display:flex;gap:0.5em;">
            <button type="submit" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">儲存</button>
            <a href="list.php" class="btn-back btn-sm">返回列表</a>
        </div>
    </form>
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
        document.querySelectorAll('input[name^="sales"][name$="[quantity]"]').forEach(inp => {
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
