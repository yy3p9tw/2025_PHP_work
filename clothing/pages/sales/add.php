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
    $Sale->insert([
        'customer_id' => $_POST['customer_id'] ?: null,
        'item_id' => $_POST['spec_id'], // 送出規格id
        'quantity' => $_POST['quantity'],
        'unit_price' => $_POST['unit_price'],
        'total_price' => $_POST['quantity'] * $_POST['unit_price'],
        'sale_date' => $_POST['sale_date'],
        'notes' => $_POST['notes'] ?? ''
    ]);
    // 銷售後自動扣除商品規格庫存
    $variantId = $_POST['spec_id'] ?? $_POST['item_id'] ?? null;
    $qty = intval($_POST['quantity'] ?? 0);
    if ($variantId && $qty > 0) {
        $Variant = new DB('item_variants');
        $variant = $Variant->all('id = ' . intval($variantId));
        if ($variant && isset($variant[0]['stock'])) {
            $newStock = max(0, intval($variant[0]['stock']) - $qty);
            $Variant->update($variantId, ['stock' => $newStock]);
        }
    }
    header('Location: list.php');
    exit;
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
            <!-- 商品選單 -->
            <label>商品：
                <select name="item_id" id="itemSelect" required>
                    <option value="">請選擇</option>
                    <?php foreach($items as $item): ?>
                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <!-- 規格選單 -->
            <label>規格：
                <select name="spec_id" id="specSelect" required>
                    <option value="">請選擇商品後再選擇規格</option>
                </select>
            </label>
            <!-- 單價 -->
            <label>單價：<input type="number" name="unit_price" id="unitPrice" step="1" required readonly></label>
            <label>數量：<input type="number" name="quantity" required></label>
            <label>日期：<input type="date" name="sale_date" required></label>
            <label>備註：<input type="text" name="notes"></label>
        </div>
        <div class="card-action-bar" style="margin-top:1.2em;display:flex;gap:0.5em;flex-wrap:wrap;">
            <button type="submit" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">新增</button>
            <a href="list.php" class="btn-back btn-sm">返回列表</a>
        </div>
    </form>
    <script>
    // 將所有規格資料傳給 JS
    const allVariants = <?= json_encode($allVariants) ?>;
    const colorMap = <?= json_encode($colorMap) ?>;
    // 商品選擇時載入規格
    document.getElementById('itemSelect').addEventListener('change', function() {
        const itemId = this.value;
        const specSelect = document.getElementById('specSelect');
        specSelect.innerHTML = '';
        if (!itemId) {
            specSelect.innerHTML = '<option value="">請選擇商品後再選擇規格</option>';
            document.getElementById('unitPrice').value = '';
            return;
        }
        const specs = allVariants.filter(v => v.item_id == itemId);
        if (specs.length === 0) {
            specSelect.innerHTML = '<option value="">無規格</option>';
            document.getElementById('unitPrice').value = '';
            return;
        }
        specSelect.innerHTML = '<option value="">請選擇</option>' + specs.map(v => `<option value="${v.id}" data-price="${v.sell_price}">${colorMap[v.color_id] || '無顏色'}（售價：${v.sell_price}）</option>`).join('');
        document.getElementById('unitPrice').value = '';
    });
    // 規格選擇時自動帶入單價
    document.getElementById('specSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const price = selected.getAttribute('data-price');
        document.getElementById('unitPrice').value = price ? price : '';
    });
    // 頁面載入時初始化
    window.addEventListener('DOMContentLoaded', function() {
        document.getElementById('itemSelect').dispatchEvent(new Event('change'));
    });
    </script>
</body>
</html>