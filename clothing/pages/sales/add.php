<?php
require_once '../../includes/db.php';
$Item = new DB('items');
$items = $Item->all();
$Customer = new DB('customers');
$customers = $Customer->all();
$Variant = new DB('item_variants');
$allVariants = $Variant->all();
$Color = new DB('colors');
$colors = $Color->all();
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];

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
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body.warm-bg { background: #fff7f0; }
        h1.main-title { color: #d2691e; text-align: center; margin-top: 2em; }
        .form-container {
            max-width: 420px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px #ffb34733;
            padding: 2em 1.5em 1.5em 1.5em;
            display: flex;
            flex-direction: column;
            gap: 1.2em;
        }
        .form-container label {
            font-weight: bold;
            color: #b97a56;
            margin-bottom: 0.3em;
        }
        .form-container input, .form-container select, .form-container textarea {
            padding: 0.6em 1em;
            border: 1px solid #ffb347;
            border-radius: 6px;
            font-size: 1em;
            margin-bottom: 0.5em;
        }
        .form-container button, .form-container .btn-back {
            padding: 0.5em 1.2em;
            border-radius: 6px;
            border: 1px solid #ffb347;
            background: #ffb347;
            color: #fff;
            font-size: 1em;
            margin-top: 0.5em;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background 0.2s;
        }
        .form-container button:hover, .form-container .btn-back:hover {
            background: #ffa500;
        }
        @media (max-width: 600px) {
            .form-container {
                max-width: 98vw;
                padding: 1.2em 0.5em 1em 0.5em;
            }
            .main-title { font-size: 1.1em; }
        }
    </style>
</head>
<body class="warm-bg">
    <h1 class="main-title">新增銷售記錄</h1>
    <form method="post" class="form-container card" style="max-width:520px;margin:auto;">
        <div class="card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;">
            <label>客戶：
                <select name="customer_id">
                    <option value="">無</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <hr style="margin:1.2em 0;">
            <div id="saleItems">
                <div class="sale-item-card" style="background:#fff;border-radius:10px;box-shadow:0 2px 8px #ffb34722;padding:1em 0.5em 0.5em 0.5em;margin-bottom:1em;">
                    <label>商品：
                        <select name="items[0][item_id]" class="itemSelect" required>
                            <option value="">請選擇</option>
                            <?php foreach($items as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="spec-list"></div>
                    <button type="button" class="addSpecBtn btn-back" style="margin-bottom:0.5em;">＋新增規格</button>
                    <button type="button" class="removeSaleItem btn-back" style="background:#fff0e0;color:#d2691e;">刪除商品</button>
                </div>
            </div>
            <button type="button" id="addSaleItemBtn">＋新增商品</button>
            <label>日期：<input type="date" name="sale_date" required></label>
            <label>備註：<input type="text" name="notes"></label>
        </div>
        <div class="card-action-bar" style="margin-top:1.2em;display:flex;gap:0.5em;flex-wrap:wrap;">
            <button type="submit" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">新增</button>
            <a href="list.php" class="btn-back btn-sm">返回列表</a>
        </div>
    </form>
    <script>
    const allVariants = <?= json_encode($allVariants) ?>;
    const colorMap = <?= json_encode($colorMap) ?>;
    let saleItemIdx = 1;
    // 動態新增商品卡片
    document.getElementById('addSaleItemBtn').onclick = function() {
        const saleItems = document.getElementById('saleItems');
        const div = document.createElement('div');
        div.className = 'sale-item-card';
        div.style = 'background:#fff;border-radius:10px;box-shadow:0 2px 8px #ffb34722;padding:1em 0.5em 0.5em 0.5em;margin-bottom:1em;';
        div.innerHTML = `
            <label>商品：
                <select name="items[${saleItemIdx}][item_id]" class="itemSelect" required>
                    <option value="">請選擇</option>
                    <?php foreach($items as $item): ?>
                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="spec-list"></div>
            <button type="button" class="addSpecBtn btn-back" style="margin-bottom:0.5em;">＋新增規格</button>
            <button type="button" class="removeSaleItem btn-back" style="background:#fff0e0;color:#d2691e;">刪除商品</button>
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
            specDiv.className = 'spec-item';
            specDiv.style = 'margin-bottom:0.5em;padding:0.5em 0;border-bottom:1px dashed #ffb347;';
            specDiv.innerHTML = `
                <label>規格：
                    <select name="items[${saleItemIdx-1}][specs][${idx}][spec_id]" class="specSelect" required>
                        <option value="">請選擇</option>
                        ${specs.map(v => `<option value="${v.id}" data-price="${v.sell_price}">${colorMap[v.color_id] || '無顏色'}（售價：${v.sell_price}）</option>`).join('')}
                    </select>
                </label>
                <label>單價：<input type="number" name="items[${saleItemIdx-1}][specs][${idx}][unit_price]" class="unitPrice" step="1" required readonly></label>
                <label>數量：<input type="number" name="items[${saleItemIdx-1}][specs][${idx}][quantity]" class="quantity" required></label>
                <button type="button" class="removeSpecBtn btn-back" style="background:#fff0e0;color:#d2691e;">刪除規格</button>
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
            e.target.closest('.spec-item').querySelector('.unitPrice').value = price ? price : '';
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