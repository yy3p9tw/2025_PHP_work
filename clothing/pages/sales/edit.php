<?php
require_once '../../includes/db.php';
$Sale = new DB('sales');
$Item = new DB('items');
$Customer = new DB('customers');
$items = $Item->all();
$customers = $Customer->all();
$Variant = new DB('item_variants');
$allVariants = $Variant->all();
$Color = new DB('colors');
$colors = $Color->all();
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}
$id = intval($_GET['id']);
$sale = $Sale->all("id = $id");
if (!$sale) {
    echo '查無此銷售記錄';
    exit;
}
$sale = $sale[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 取得原本的數量與規格id
    $oldSale = $Sale->all("id = $id");
    $oldQty = $oldSale ? intval($oldSale[0]['quantity']) : 0;
    $oldVariantId = $oldSale ? intval($oldSale[0]['item_id']) : 0;
    $newQty = intval($_POST['quantity']);
    // 取得新規格id（來自 spec_id）
    $newVariantId = isset($_POST['spec_id']) ? intval($_POST['spec_id']) : (isset($_POST['item_id']) ? intval($_POST['item_id']) : 0);
    // 若規格有變更，先還原舊規格庫存，再扣除新規格庫存
    if ($oldVariantId && $oldQty) {
        $Variant = new DB('item_variants');
        $variant = $Variant->all('id = ' . $oldVariantId);
        if ($variant && isset($variant[0]['stock'])) {
            $Variant->update($oldVariantId, ['stock' => intval($variant[0]['stock']) + $oldQty]);
        }
    }
    if ($newVariantId && $newQty) {
        $Variant = new DB('item_variants');
        $variant = $Variant->all('id = ' . $newVariantId);
        if ($variant && isset($variant[0]['stock'])) {
            $Variant->update($newVariantId, ['stock' => max(0, intval($variant[0]['stock']) - $newQty)]);
        }
    }
    $data = [
        'customer_id' => $_POST['customer_id'] ?: null,
        'item_id' => $newVariantId,
        'quantity' => $_POST['quantity'],
        'unit_price' => $_POST['unit_price'],
        'total_price' => $_POST['quantity'] * $_POST['unit_price'],
        'sale_date' => $_POST['sale_date'],
        'notes' => $_POST['notes'],
    ];
    $Sale->update($id, $data);
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯銷售記錄</title>
    <link rel="stylesheet" href="/clothing/css/style.css">
</head>
<body class="warm-bg">
    <h1 class="main-title">編輯銷售記錄</h1>
    <div class="card form-card form-container">
    <form method="post">
        <div class="form-group">
        <label>客戶：
            <select name="customer_id" class="input input-select">
                <option value="">無</option>
                <?php foreach($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $sale['customer_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        </div>
        <div class="form-group">
        <label>商品：
            <select name="item_id" id="itemSelect" required class="input input-select">
                <option value="">請選擇</option>
                <?php foreach($items as $item): ?>
                    <option value="<?= $item['id'] ?>" <?= $sale['item_id']==$item['id']?'selected':'' ?>><?= htmlspecialchars($item['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        </div>
        <div class="form-group">
        <label>規格：
            <select name="spec_id" id="specSelect" required class="input input-select">
                <option value="">請選擇商品後再選擇規格</option>
            </select>
        </label>
        </div>
        <div class="form-group">
        <label>單價：<input type="number" name="unit_price" id="unitPrice" value="<?= intval($sale['unit_price']) ?>" required min="0" step="1" class="input input-number"></label>
        </div>
        <div class="form-group">
        <label>數量：<input type="number" name="quantity" value="<?= $sale['quantity'] ?>" required min="1" step="1" class="input input-number"></label>
        </div>
        <div class="form-group">
        <label>日期：<input type="date" name="sale_date" value="<?= $sale['sale_date'] ?>" required class="input input-date"></label>
        </div>
        <div class="form-group">
        <label>備註：<input type="text" name="notes" value="<?= htmlspecialchars($sale['notes']) ?>" class="input input-text"></label>
        </div>
        <div class="form-actions" style="text-align:center;margin-top:1.5em;">
            <button type="submit" class="btn btn-main">儲存</button>
            <a href="list.php" class="btn btn-back">回銷售記錄列表</a>
        </div>
    </form>
    </div>
    <script>
    // 將所有規格資料傳給 JS
    const allVariants = <?= json_encode($allVariants) ?>;
    const colorMap = <?= json_encode($colorMap) ?>;
    // 商品選擇時載入規格
    function loadSpecs(itemId, selectedId) {
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
        specSelect.innerHTML = '<option value="">請選擇</option>' + specs.map(v => `<option value="${v.id}" data-price="${v.sell_price}" ${selectedId==v.id?'selected':''}>${colorMap[v.color_id] || '無顏色'}（售價：${v.sell_price}）</option>`).join('');
    }
    document.getElementById('itemSelect').addEventListener('change', function() {
        loadSpecs(this.value);
    });
    document.getElementById('specSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const price = selected.getAttribute('data-price');
        document.getElementById('unitPrice').value = price ? price : '';
    });
    // 頁面載入時初始化
    window.addEventListener('DOMContentLoaded', function() {
        // 取得目前的規格id
        var currentVariantId = <?= $sale['item_id'] ?>;
        // 找到該規格對應的商品id
        var currentVariant = allVariants.find(v => v.id == currentVariantId);
        var currentItemId = currentVariant ? currentVariant.item_id : '';
        // 設定商品選單
        document.getElementById('itemSelect').value = currentItemId;
        // 載入規格並選中
        loadSpecs(currentItemId, currentVariantId);
        // 自動帶入單價
        const specSelect = document.getElementById('specSelect');
        if (specSelect.value) {
            const selected = specSelect.options[specSelect.selectedIndex];
            const price = selected.getAttribute('data-price');
            document.getElementById('unitPrice').value = price ? price : '';
        }
    });
    </script>
</body>
</html>
