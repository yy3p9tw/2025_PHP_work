<?php
require_once '../../includes/db.php';
$Item = new DB('items');
$items = $Item->all();
$Customer = new DB('customers');
$customers = $Customer->all();
$Variant = new DB('item_variants');
$allVariants = $Variant->all();
$Category = new DB('categories');
$categories = $Category->all();
$Color = new DB('colors');
$colors = $Color->all();
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
$catMap = [];
foreach($categories as $cat) $catMap[$cat['id']] = $cat['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Sale = new DB('sales');
    $hasSale = false;
    foreach ($_POST['items'] as $item) {
        if (!isset($item['specs']) || !is_array($item['specs'])) continue;
        foreach ($item['specs'] as $spec) {
            $spec_id = $spec['spec_id'] ?? null;
            $qty = $spec['quantity'] ?? null;
            $unit_price = $spec['unit_price'] ?? null;
            $use_discount = isset($spec['use_discount']) && $spec['use_discount'] ? true : false;
            $discount_price = $use_discount && isset($spec['discount_price']) && $spec['discount_price'] !== '' ? floatval($spec['discount_price']) : null;
            $final_price = $use_discount && $discount_price !== null ? $discount_price : $unit_price;
            if ($spec_id && $qty && $final_price) {
                $Sale->insert([
                    'customer_id' => $_POST['customer_id'] ?: null,
                    'item_id' => $spec_id,
                    'quantity' => $qty,
                    'unit_price' => $unit_price,
                    'total_price' => $qty * $final_price,
                    'sale_date' => $_POST['sale_date'],
                    'notes' => $_POST['notes'] ?? ''
                ]);
                // 扣庫存
                $Variant = new DB('item_variants');
                $variant = $Variant->all('id = ' . intval($spec_id));
                if ($variant && isset($variant[0]['stock'])) {
                    $newStock = intval($variant[0]['stock']) - $qty; // 允許負數
                    $Variant->update($spec_id, ['stock' => $newStock]);
                }
                $hasSale = true;
            }
        }
    }
    if ($hasSale) {
        header('Location: list.php');
        exit;
    } else {
        echo '<script>alert("請至少輸入一筆有效的商品規格與數量");</script>';
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
    <style>
        body.warm-bg { background: #fff7f0; }
        .main-title { color: #d2691e; text-align: center; margin-top: 2em; }
        .card { box-shadow: 0 2px 16px #ffb34733; }
        .form-label { font-weight: bold; color: #b97a56; }
        .btn-back { background: #ffb347; color: #fff; border: 1px solid #ffb347; }
        .btn-back:hover { background: #ffa500; color: #fff; }
        .card-action-bar { margin-top:1.2em; display:flex; gap:0.5em; flex-wrap:wrap; }
        @media (max-width: 600px) {
            .main-title { font-size: 1.1em; }
        }
    </style>
</head>
<body class="warm-bg">
    <h1 class="main-title">新增銷售記錄</h1>
    <form method="post" class="card p-4 mx-auto mt-4" style="max-width:520px;" autocomplete="off">
        <div class="card p-3 mb-3">
            <div class="mb-3">
                <label class="form-label">客戶：</label>
                <select name="customer_id" class="form-select">
                    <option value="">無</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['name']) ?>
                            <?php if (!empty($c['phone'])): ?>
                                (<?= htmlspecialchars($c['phone']) ?>)
                            <?php endif; ?>
                            <?php if (!empty($c['email'])): ?>
                                <?= htmlspecialchars($c['email']) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <hr class="my-3">
            <div id="saleItems">
                <div class="sale-item-card card mb-3 p-3 bg-light">
                    <div class="mb-2">
                        <label class="form-label">商品：</label>
                        <select name="items[0][item_id]" class="form-select itemSelect" required>
                            <option value="">請選擇</option>
                            <?php foreach($items as $item): ?>
                                <option value="<?= $item['id'] ?>">
                                    <?= htmlspecialchars($item['name']) ?>
                                    <?php if(isset($catMap[$item['category_id']])): ?>
                                        (<?= htmlspecialchars($catMap[$item['category_id']]) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="spec-list">
                        <div class="spec-item row g-2 align-items-end mb-2 border-bottom pb-2">
                            <div class="col-12 col-md-5">
                                <label class="form-label">規格：</label>
                                <select name="items[0][specs][0][spec_id]" class="form-select specSelect" required>
                                    <option value="">請選擇</option>
                                    <?php foreach($allVariants as $v): ?>
                                        <?php if (isset($colorMap[$v['color_id']])): ?>
                                            <option value="<?= $v['id'] ?>" data-price="<?= $v['sell_price'] ?>">
                                                <?= $colorMap[$v['color_id']] ?>（售價：<?= $v['sell_price'] ?>）
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">單價：</label>
                                <input type="number" name="items[0][specs][0][unit_price]" class="form-control unitPrice" step="1" required readonly>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">數量：</label>
                                <input type="number" name="items[0][specs][0][quantity]" class="form-control quantity" required>
                            </div>
                            <div class="col-12 col-md-1 text-end">
                                <button type="button" class="removeSpecBtn btn btn-outline-danger btn-sm">刪除</button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <button type="button" class="addSpecBtn btn btn-secondary btn-sm">＋新增規格</button>
                        <button type="button" class="removeSaleItem btn btn-outline-danger btn-sm">刪除商品</button>
                    </div>
                </div>
            </div>
            <button type="button" id="addSaleItemBtn" class="btn btn-primary btn-sm mb-2">＋新增商品</button>
            <div class="mb-3">
                <label class="form-label">日期：</label>
                <input type="date" name="sale_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">備註：</label>
                <input type="text" name="notes" class="form-control">
            </div>
        </div>
        <div class="card-action-bar">
            <button type="submit" class="btn btn-back btn-sm">新增</button>
            <a href="list.php" class="btn btn-back btn-sm">返回列表</a>
        </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const allVariants = <?= json_encode($allVariants) ?>;
    const colorMap = <?= json_encode($colorMap) ?>;
    const catMap = <?= json_encode($catMap) ?>;
    let saleItemIdx = 1;
    // 動態新增商品卡片
    document.getElementById('addSaleItemBtn').onclick = function() {
        const saleItems = document.getElementById('saleItems');
        const div = document.createElement('div');
        div.className = 'sale-item-card';
        div.style = 'background:#fff;border-radius:10px;box-shadow:0 2px 8px #ffb34722;padding:1em 0.5em 0.5em 0.5em;margin-bottom:1em;';
        div.innerHTML = `
            <label>商品：
                <select name="items[${saleItemIdx}][item_id]" class="form-select itemSelect" required>
                    <option value="">請選擇</option>
                    <?php foreach($items as $item): ?>
                        <option value="<?= $item['id'] ?>">
                            <?= htmlspecialchars($item['name']) ?>
                            <?php if(isset($catMap[$item['category_id']])): ?>
                                (<?= htmlspecialchars($catMap[$item['category_id']]) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="spec-list"></div>
            <button type="button" class="addSpecBtn btn btn-secondary btn-sm" style="margin-bottom:0.5em;">＋新增規格</button>
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
    document.getElementById('saleItems').onclick = function(e) {
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
            specDiv.className = 'spec-item row g-2 align-items-end mb-2 border-bottom pb-2';
            specDiv.innerHTML = `
                <div class="col-12 col-md-5">
                    <label class="form-label">規格：</label>
                    <select name="items[${saleItemIdx-1}][specs][${idx}][spec_id]" class="form-select specSelect" required>
                        <option value="">請選擇</option>
                        ${specs.map(v => `<option value="${v.id}" data-price="${v.sell_price}">${colorMap[v.color_id] || '無顏色'}（售價：${v.sell_price}）</option>`).join('')}
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">單價：</label>
                    <input type="number" name="items[${saleItemIdx-1}][specs][${idx}][unit_price]" class="form-control unitPrice" step="1" required readonly>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">數量：</label>
                    <input type="number" name="items[${saleItemIdx-1}][specs][${idx}][quantity]" class="form-control quantity" required>
                </div>
                <div class="col-12 col-md-1 text-end">
                    <button type="button" class="removeSpecBtn btn btn-outline-danger btn-sm">刪除</button>
                </div>
            `;
            specList.appendChild(specDiv);
        }
        // 刪除規格卡片
        if (e.target.classList.contains('removeSpecBtn')) {
            e.target.closest('.spec-item').remove();
        }
        // 刪除商品卡片
        if (e.target.classList.contains('removeSaleItem')) {
            if (document.querySelectorAll('#saleItems .sale-item-card').length > 1) {
                e.target.closest('.sale-item-card').remove();
            } else {
                alert('至少要有一個商品');
            }
        }
    };
    // 規格選擇時自動帶入單價
document.getElementById('saleItems').addEventListener('change', function(e) {
    if (e.target.classList.contains('specSelect')) {
        const selected = e.target.options[e.target.selectedIndex];
        const price = selected.getAttribute('data-price');
        const unitPriceInput = e.target.closest('.spec-item').querySelector('.unitPrice');
        // 只有當單價欄位為空時才自動填入
        if (unitPriceInput.value === '' || unitPriceInput.value === null) {
            unitPriceInput.value = price ? price : '';
        }
    }
});
    // 全部折扣價功能
    const globalDiscountCheck = document.getElementById('globalDiscountCheck');
    const globalDiscountPrice = document.getElementById('globalDiscountPrice');

    globalDiscountCheck.onchange = function() {
        globalDiscountPrice.disabled = !this.checked;
        if (!this.checked) {
            // 取消時，恢復所有單價為原本值（不自動還原，僅停用欄位）
            return;
        }
        applyGlobalDiscount();
    };
    globalDiscountPrice.oninput = function() {
        if (globalDiscountCheck.checked) applyGlobalDiscount();
    };

    function applyGlobalDiscount() {
        const price = parseInt(globalDiscountPrice.value, 10) || 0;
        // 編輯區現有商品
        document.querySelectorAll('input[name^="sales"][name$="[unit_price]"]').forEach(inp => {
            inp.value = price;
        });
        // 新增商品區
        document.querySelectorAll('#newSaleItems input[name^="new_sales"][name$="[unit_price]"]').forEach(inp => {
            inp.value = price;
        });
    }
    // 表單送出時將全局折扣價帶入所有規格
    document.querySelector('form').onsubmit = function(e) {
        if (document.getElementById('globalDiscountCheck').checked) {
            const discount = document.getElementById('globalDiscountPrice').value;
            if (!discount || isNaN(discount) || Number(discount) <= 0) {
                alert('請輸入有效的折扣價');
                document.getElementById('globalDiscountPrice').focus();
                e.preventDefault();
                return false;
            }
            document.querySelectorAll('.unitPrice').forEach(inp => {
                inp.value = discount;
            });
        }
        return true;
    };
    </script>
</body>
</html>