<?php
require_once '../../includes/db.php';
$Item = new DB('items');
$Customer = new DB('customers');
$Variant = new DB('item_variants');
$Color = new DB('colors');
$Size = new DB('sizes');
$Sale = new DB('sales');
$SaleItem = new DB('sale_items');

$items = $Item->all();
$customers = $Customer->all();
$allVariants = $Variant->all();
$colors = $Color->all();
$sizes = $Size->all();

$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
$sizeMap = [];
foreach($sizes as $s) $sizeMap[$s['id']] = $s['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?: null;
    $sale_date = $_POST['sale_date'];
    $notes = $_POST['notes'] ?? '';
    $total_amount = 0;
    $sale_items_data = [];

    // 計算總金額並準備銷售明細數據
    foreach ($_POST['items'] as $item_data) {
        if (!isset($item_data['specs']) || !is_array($item_data['specs'])) continue;
        foreach ($item_data['specs'] as $spec) {
            $item_variant_id = $spec['item_variant_id'] ?? null;
            $quantity = intval($spec['quantity'] ?? 0);
            $unit_price = floatval($spec['unit_price'] ?? 0);

            if ($item_variant_id && $quantity > 0 && $unit_price > 0) {
                $total_amount += ($quantity * $unit_price);
                $sale_items_data[] = [
                    'item_variant_id' => $item_variant_id,
                    'quantity' => $quantity,
                    'price_at_sale' => $unit_price
                ];
            }
        }
    }

    if (empty($sale_items_data)) {
        echo '<script>alert("請至少輸入一筆有效的商品規格與數量");</script>';
    } else {
        try {
            $Sale->getPdo()->beginTransaction();

            // 插入銷售主表
            $Sale->insert([
                'customer_id' => $customer_id,
                'sale_date' => $sale_date,
                'total_amount' => $total_amount,
                'notes' => $notes
            ]);
            $sale_id = $Sale->getLastInsertId();

            // 插入銷售明細並更新庫存
            foreach ($sale_items_data as $data) {
                $SaleItem->insert([
                    'sale_id' => $sale_id,
                    'item_variant_id' => $data['item_variant_id'],
                    'quantity' => $data['quantity'],
                    'price_at_sale' => $data['price_at_sale']
                ]);

                // 扣庫存
                $variant = $Variant->find(['id' => $data['item_variant_id']]);
                if ($variant) {
                    $newStock = $variant['stock'] - $data['quantity'];
                    $Variant->update($data['item_variant_id'], ['stock' => $newStock]);
                }
            }

            $Sale->getPdo()->commit();
            header('Location: list.php');
            exit;

        } catch (Throwable $e) {
            $Sale->getPdo()->rollBack();
            echo '<pre style="color:red;">錯誤：' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增銷售紀錄</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <h1 class="main-title">新增銷售記錄</h1>
    <form method="post" class="form-container card mx-auto mt-4" style="max-width:520px;">
        <div class="mb-3">
            <label class="form-label">客戶：</label>
            <select name="customer_id" class="form-select">
                <option value="">無</option>
                <?php foreach($customers as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <hr class="my-4">
        <div id="saleItems">
            <div class="sale-item-card p-3 mb-3">
                <div class="mb-3">
                    <label class="form-label">商品：</label>
                    <select name="items[0][item_id]" class="itemSelect form-select" required>
                        <option value="">請選擇</option>
                        <?php foreach($items as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="spec-list"></div>
                <button type="button" class="addSpecBtn btn btn-secondary btn-sm mb-2">＋新增規格</button>
                <button type="button" class="removeSaleItem btn btn-outline-danger btn-sm">刪除商品</button>
            </div>
        </div>
        <button type="button" id="addSaleItemBtn" class="btn btn-primary btn-sm mb-3">＋新增商品</button>
        <div class="mb-3">
            <label class="form-label">日期：</label>
            <input type="date" name="sale_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">備註：</label>
            <input type="text" name="notes" class="form-control">
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">新增</button>
            <a href="list.php" class="btn btn-outline-secondary">返回列表</a>
        </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const allVariants = <?= json_encode($allVariants) ?>;
    const colorMap = <?= json_encode($colorMap) ?>;
    const sizeMap = <?= json_encode($sizeMap) ?>;
    let saleItemIdx = 1;
    // 動態新增商品卡片
    document.getElementById('addSaleItemBtn').onclick = function() {
        const saleItems = document.getElementById('saleItems');
        const div = document.createElement('div');
        div.className = 'sale-item-card p-3 mb-3';
        div.innerHTML = `
            <div class="mb-3">
                <label class="form-label">商品：</label>
                <select name="items[${saleItemIdx}][item_id]" class="itemSelect form-select" required>
                    <option value="">請選擇</option>
                    <?php foreach($items as $item): ?>
                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="spec-list"></div>
            <button type="button" class="addSpecBtn btn btn-secondary btn-sm mb-2">＋新增規格</button>
            <button type="button" class="removeSaleItem btn btn-outline-danger btn-sm">刪除商品</button>
        `;
        saleItems.appendChild(div);
        saleItemIdx++;
    };
    // 刪除商品卡片
    document.getElementById('saleItems').onclick = function(e) {
        if (e.target.classList.contains('removeSaleItem')) {
            if (document.querySelectorAll('#saleItems .sale-item-card').length > 1) {
                e.target.closest('.sale-item-card').remove();
            } else {
                alert('至少要有一個商品');
            }
        }
    };
    // 新增規格卡片
    document.getElementById('saleItems').addEventListener('click', function(e) {
        if (e.target.classList.contains('addSpecBtn')) {
            const saleItemCard = e.target.closest('.sale-item-card');
            const itemSelect = saleItemCard.querySelector('.itemSelect');
            const itemId = itemSelect.value;
            if (!itemId) { alert('請先選擇商品'); return; }
            const specList = saleItemCard.querySelector('.spec-list');
            const specs = allVariants.filter(v => v.item_id == itemId);
            if (specs.length === 0) {
                alert('此商品無規格');
                return;
            }
            // 新增一個規格輸入區
            const idx = specList.children.length;
            const specDiv = document.createElement('div');
            specDiv.className = 'spec-item row g-2 align-items-end mb-2';
            specDiv.innerHTML = `
                <div class="col-md-6">
                    <label class="form-label">規格：</label>
                    <select name="items[${saleItemIdx-1}][specs][${idx}][item_variant_id]" class="specSelect form-select" required>
                        <option value="">請選擇</option>
                        ${specs.map(v => `<option value="${v.id}" data-price="${v.sell_price}">${colorMap[v.color_id] || '無顏色'} / ${sizeMap[v.size_id] || '無尺寸'}（售價：${v.sell_price}）</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">單價：</label>
                    <input type="number" name="items[${saleItemIdx-1}][specs][${idx}][unit_price]" class="unitPrice form-control" step="1" required readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">數量：</label>
                    <input type="number" name="items[${saleItemIdx-1}][specs][${idx}][quantity]" class="quantity form-control" required>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="removeSpecBtn btn btn-outline-danger btn-sm">刪除</button>
                </div>
            `;
            specList.appendChild(specDiv);
        }
        // 刪除規格卡片
        if (e.target.classList.contains('removeSpecBtn')) {
            e.target.closest('.spec-item').remove();
        }
    });
    // 規格選擇時自動帶入單價
    document.getElementById('saleItems').addEventListener('change', function(e) {
        if (e.target.classList.contains('specSelect')) {
            const selected = e.target.options[e.target.selectedIndex];
            const price = selected.getAttribute('data-price');
            e.target.closest('.spec-item').querySelector('.unitPrice').value = price ? price : '';
        }
    });
    // 表單送出時將全局折扣價帶入所有規格
    document.querySelector('form').onsubmit = function(e) {
        // 驗證所有規格的選擇和數量
        const itemSelects = document.querySelectorAll('.itemSelect');
        for (let i = 0; i < itemSelects.length; i++) {
            const itemSelect = itemSelects[i];
            const saleItemCard = itemSelect.closest('.sale-item-card');
            const specSelects = saleItemCard.querySelectorAll('.specSelect');
            const quantityInputs = saleItemCard.querySelectorAll('.quantity');
            const unitPriceInputs = saleItemCard.querySelectorAll('.unitPrice');

            if (!itemSelect.value) {
                alert('請為所有商品選擇商品！');
                itemSelect.focus();
                e.preventDefault();
                return false;
            }

            if (specSelects.length === 0) {
                alert('請為商品 ' + itemSelect.options[itemSelect.selectedIndex].text + ' 新增至少一個規格！');
                e.preventDefault();
                return false;
            }

            for (let j = 0; j < specSelects.length; j++) {
                if (!specSelects[j].value) {
                    alert('請為所有規格選擇規格！');
                    specSelects[j].focus();
                    e.preventDefault();
                    return false;
                }
                if (parseInt(quantityInputs[j].value) <= 0) {
                    alert('數量必須大於0！');
                    quantityInputs[j].focus();
                    e.preventDefault();
                    return false;
                }
                if (parseFloat(unitPriceInputs[j].value) <= 0) {
                    alert('單價必須大於0！');
                    unitPriceInputs[j].focus();
                    e.preventDefault();
                    return false;
                }
            }
        }
        return true;
    };
    </script>
</body>
</html>