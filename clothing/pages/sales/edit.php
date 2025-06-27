<?php
require_once '../../includes/db.php';

// 取得參數
$sale_id = $_GET['id'] ?? null;
if (!$sale_id) {
    header('Location: list.php');
    exit;
}

$Sale = new DB('sales');
$SaleItem = new DB('sale_items');
$Item = new DB('items');
$Customer = new DB('customers');
$Variant = new DB('item_variants');
$Color = new DB('colors');
$Size = new DB('sizes');
$Category = new DB('categories');

$sale = $Sale->find(['id' => $sale_id]);
if (!$sale) {
    echo "找不到銷售記錄";
    exit;
}

$sale_items = $SaleItem->all(['sale_id' => $sale_id]);

$items = $Item->all();
$customers = $Customer->all();
$allVariants = $Variant->all();
$colors = $Color->all();
$sizes = $Size->all();
$categories = $Category->all();

// 建立對照表
$itemMap = [];
foreach($items as $i) $itemMap[$i['id']] = $i['name'];
$itemFullMap = [];
foreach($items as $i) $itemFullMap[$i['id']] = $i; // 方便查分類、圖片
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
$sizeMap = [];
foreach($sizes as $s) $sizeMap[$s['id']] = $s['name'];
$variantMap = [];
foreach($allVariants as $v) $variantMap[$v['id']] = $v;

// 表單送出處理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $Sale->getPdo()->beginTransaction();

        // 更新銷售主表
        $Sale->update($sale_id, [
            'customer_id' => $_POST['customer_id'] ?: null,
            'sale_date' => $_POST['sale_date'],
            'notes' => $_POST['notes'] ?? '',
            'total_amount' => 0 // 暫時設為0，後面會重新計算
        ]);

        $updated_total_amount = 0;
        $existing_sale_item_ids = array_column($sale_items, 'id');
        $post_sale_item_ids = [];

        // 處理現有銷售明細的更新和刪除
        if (isset($_POST['sale_items']) && is_array($_POST['sale_items'])) {
            foreach ($_POST['sale_items'] as $sale_item_id => $row) {
                $post_sale_item_ids[] = $sale_item_id;

                $item_variant_id = intval($row['item_variant_id']);
                $quantity = intval($row['quantity']);
                $unit_price = floatval($row['unit_price']);

                // 獲取舊的銷售明細數據
                $old_sale_item = $SaleItem->find(['id' => $sale_item_id]);

                if ($old_sale_item) {
                    // 回補舊庫存
                    $old_variant = $Variant->find(['id' => $old_sale_item['item_variant_id']]);
                    if ($old_variant) {
                        $Variant->update($old_variant['id'], ['stock' => $old_variant['stock'] + $old_sale_item['quantity']]);
                    }

                    // 更新銷售明細
                    $SaleItem->update($sale_item_id, [
                        'item_variant_id' => $item_variant_id,
                        'quantity' => $quantity,
                        'price_at_sale' => $unit_price
                    ]);

                    // 扣除新庫存
                    $new_variant = $Variant->find(['id' => $item_variant_id]);
                    if ($new_variant) {
                        $Variant->update($new_variant['id'], ['stock' => $new_variant['stock'] - $quantity]);
                    }
                    $updated_total_amount += ($quantity * $unit_price);
                }
            }
        }

        // 處理被刪除的銷售明細 (回補庫存)
        $deleted_sale_item_ids = array_diff($existing_sale_item_ids, $post_sale_item_ids);
        foreach ($deleted_sale_item_ids as $deleted_id) {
            $deleted_sale_item = $SaleItem->find(['id' => $deleted_id]);
            if ($deleted_sale_item) {
                $deleted_variant = $Variant->find(['id' => $deleted_sale_item['item_variant_id']]);
                if ($deleted_variant) {
                    $Variant->update($deleted_variant['id'], ['stock' => $deleted_variant['stock'] + $deleted_sale_item['quantity']]);
                }
                $SaleItem->delete($deleted_id);
            }
        }

        // 處理新增的銷售明細
        if (isset($_POST['new_sale_items']) && is_array($_POST['new_sale_items'])) {
            foreach ($_POST['new_sale_items'] as $row) {
                $item_variant_id = intval($row['item_variant_id']);
                $quantity = intval($row['quantity']);
                $unit_price = floatval($row['unit_price']);

                if ($item_variant_id && $quantity > 0 && $unit_price > 0) {
                    $SaleItem->insert([
                        'sale_id' => $sale_id,
                        'item_variant_id' => $item_variant_id,
                        'quantity' => $quantity,
                        'price_at_sale' => $unit_price
                    ]);

                    // 扣庫存
                    $variant = $Variant->find(['id' => $item_variant_id]);
                    if ($variant) {
                        $Variant->update($variant['id'], ['stock' => $variant['stock'] - $quantity]);
                    }
                    $updated_total_amount += ($quantity * $unit_price);
                }
            }
        }

        // 重新計算總金額並更新銷售主表
        $Sale->update($sale_id, ['total_amount' => $updated_total_amount]);

        $Sale->getPdo()->commit();
        header('Location: list.php');
        exit;

    } catch (Throwable $e) {
        $Sale->getPdo()->rollBack();
        echo '<pre style="color:red;">錯誤：' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯銷售紀錄</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <div class="container py-4">
        <h1 class="main-title mb-4">編輯銷售紀錄</h1>
        <form method="post" class="form-container card mx-auto mt-4" style="max-width:700px;">
            <input type="hidden" name="customer_id" value="<?= htmlspecialchars($sale['customer_id']) ?>">
            <input type="hidden" name="sale_date" value="<?= htmlspecialchars($sale['sale_date']) ?>">
            <div class="mb-3">
                <label class="form-label">客戶：</label>
                <select name="customer_id" class="form-select" required>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($c['id'] == $sale['customer_id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">日期：</label>
                <input type="date" name="sale_date" class="form-control" value="<?= htmlspecialchars(date('Y-m-d', strtotime($sale['sale_date']))) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">備註：</label>
                <input type="text" name="notes" value="<?= htmlspecialchars($sale['notes'] ?? '') ?>" class="form-control">
            </div>
            <hr class="my-4">
            <div class="mb-3">
                <strong class="mb-2 d-block">商品與規格：</strong>
                <div id="saleItemsContainer">
                    <?php foreach($sale_items as $idx => $saleItem): 
                        $variant = $variantMap[$saleItem['item_variant_id']] ?? null;
                        $item = ($variant && isset($itemFullMap[$variant['item_id']])) ? $itemFullMap[$variant['item_id']] : null;
                    ?>
                    <div class="sale-variant-card p-3 mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">商品：</label>
                                <select name="sale_items[<?= $saleItem['id'] ?>][item_id]" class="form-select itemSelect" disabled>
                                    <?php foreach($items as $it): ?>
                                        <option value="<?= $it['id'] ?>" <?= ($item && $it['id']==$item['id'])?'selected':'' ?>>
                                            <?= htmlspecialchars($it['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">規格：</label>
                                <select name="sale_items[<?= $saleItem['id'] ?>][item_variant_id]" class="form-select variantSelect" required>
                                    <?php foreach($allVariants as $v): 
                                        if ($item && $v['item_id'] != $item['id']) continue;
                                    ?>
                                        <option value="<?= $v['id'] ?>" <?= ($saleItem['item_variant_id']==$v['id'])?'selected':'' ?> data-price="<?= $v['sell_price'] ?>">
                                            <?= htmlspecialchars($colorMap[$v['color_id']] ?? '無顏色') ?> / <?= htmlspecialchars($sizeMap[$v['size_id']] ?? '無尺寸') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">數量：</label>
                                <input type="number" name="sale_items[<?= $saleItem['id'] ?>][quantity]" value="<?= $saleItem['quantity'] ?>" class="form-control quantityInput" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">單價：</label>
                                <input type="number" name="sale_items[<?= $saleItem['id'] ?>][unit_price]" value="<?= $saleItem['price_at_sale'] ?>" class="form-control unitPriceInput" step="0.01" required>
                            </div>
                            <div class="col-12 text-end mt-2">
                                <button type="button" class="removeSaleItemBtn btn btn-outline-danger btn-sm" data-sale-item-id="<?= $saleItem['id'] ?>">刪除此規格</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="addSaleItemBtn" class="btn btn-secondary btn-sm mb-3">＋新增商品規格</button>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">儲存</button>
                <a href="list.php" class="btn btn-outline-secondary">返回列表</a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const allVariants = <?= json_encode($allVariants) ?>;
    const colorMap = <?= json_encode($colorMap) ?>;
    const sizeMap = <?= json_encode($sizeMap) ?>;
    let newSaleItemIdx = 0;

    document.getElementById('addSaleItemBtn').onclick = function() {
        const container = document.getElementById('saleItemsContainer');
        const div = document.createElement('div');
        div.className = 'sale-variant-card p-3 mb-3';
        div.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">商品：</label>
                    <select name="new_sale_items[${newSaleItemIdx}][item_id]" class="form-select itemSelect" required>
                        <option value="">請選擇</option>
                        <?php foreach($items as $it): ?>
                            <option value="<?= $it['id'] ?>"><?= htmlspecialchars($it['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">規格：</label>
                    <select name="new_sale_items[${newSaleItemIdx}][item_variant_id]" class="form-select variantSelect" required>
                        <option value="">請先選擇商品</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">數量：</label>
                    <input type="number" name="new_sale_items[${newSaleItemIdx}][quantity]" value="1" class="form-control quantityInput" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">單價：</label>
                    <input type="number" name="new_sale_items[${newSaleItemIdx}][unit_price]" value="0" class="form-control unitPriceInput" step="0.01" required>
                </div>
                <div class="col-12 text-end mt-2">
                    <button type="button" class="removeNewSaleItemBtn btn btn-outline-danger btn-sm">刪除此規格</button>
                </div>
            </div>
        `;
        container.appendChild(div);
        newSaleItemIdx++;
    };

    document.getElementById('saleItemsContainer').addEventListener('change', function(e) {
        // 處理商品選擇，動態載入規格
        if (e.target.classList.contains('itemSelect')) {
            const itemSelect = e.target;
            const variantSelect = itemSelect.closest('.sale-variant-card').querySelector('.variantSelect');
            const itemId = itemSelect.value;

            variantSelect.innerHTML = '<option value="">請選擇</option>';
            if (itemId) {
                const filteredVariants = allVariants.filter(v => v.item_id == itemId);
                if (filteredVariants.length > 0) {
                    filteredVariants.forEach(v => {
                        const option = document.createElement('option');
                        option.value = v.id;
                        option.dataset.price = v.sell_price;
                        option.textContent = `${colorMap[v.color_id] || '無顏色'} / ${sizeMap[v.size_id] || '無尺寸'}（售價：${v.sell_price}）`;
                        variantSelect.appendChild(option);
                    });
                } else {
                    variantSelect.innerHTML = '<option value="">此商品無規格</option>';
                }
            }
            // 清空單價和數量
            itemSelect.closest('.sale-variant-card').querySelector('.unitPriceInput').value = 0;
            itemSelect.closest('.sale-variant-card').querySelector('.quantityInput').value = 1;
        }

        // 處理規格選擇，自動帶入單價
        if (e.target.classList.contains('variantSelect')) {
            const variantSelect = e.target;
            const selectedOption = variantSelect.options[variantSelect.selectedIndex];
            const price = selectedOption.dataset.price;
            variantSelect.closest('.sale-variant-card').querySelector('.unitPriceInput').value = price ? price : 0;
        }
    });

    // 處理刪除按鈕
    document.getElementById('saleItemsContainer').addEventListener('click', function(e) {
        if (e.target.classList.contains('removeSaleItemBtn')) {
            if (confirm('確定要刪除此規格嗎？')) {
                e.target.closest('.sale-variant-card').remove();
            }
        } else if (e.target.classList.contains('removeNewSaleItemBtn')) {
            if (confirm('確定要刪除此新增規格嗎？')) {
                e.target.closest('.sale-variant-card').remove();
            }
        }
    });

    // 表單提交前驗證
    document.querySelector('form').onsubmit = function(e) {
        const variantSelects = document.querySelectorAll('.variantSelect');
        for (let select of variantSelects) {
            if (!select.value) {
                alert('請為所有商品選擇規格！');
                select.focus();
                e.preventDefault();
                return false;
            }
        }
        const quantityInputs = document.querySelectorAll('.quantityInput');
        for (let input of quantityInputs) {
            if (parseInt(input.value) <= 0) {
                alert('數量必須大於0！');
                input.focus();
                e.preventDefault();
                return false;
            }
        }
        const unitPriceInputs = document.querySelectorAll('.unitPriceInput');
        for (let input of unitPriceInputs) {
            if (parseFloat(input.value) <= 0) {
                alert('單價必須大於0！');
                input.focus();
                e.preventDefault();
                return false;
            }
        }
        return true;
    };
    </script>
</body>
</html>