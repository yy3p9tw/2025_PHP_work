<?php
require_once '../../includes/db.php';
$Item = new DB('items');
$Category = new DB('categories');
$categories = $Category->all();
$Color = new DB('colors');
$colors = $Color->all();
$Size = new DB('sizes');
$sizes = $Size->all();
$Variant = new DB('item_variants');

$id = $_GET['id'] ?? 0;
$item = $Item->find(['id' => $id]); // 使用 find() 方法

if (!$item) {
    echo "找不到商品";
    exit;
}

$variants = $Variant->all(['item_id' => $id]); // 使用 all() 方法的陣列條件

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $imageName = $item['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowExt = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($ext, $allowExt)) {
                $imageName = uniqid('img_', true) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], '../../uploads/' . $imageName);
            }
        }
        $Item->update($id, [
            'name' => $_POST['name'],
            'category_id' => $_POST['category_id'],
            'description' => $_POST['description'],
            'image' => $imageName
        ]);
        // --- variants ---
        $oldVariants = $Variant->all(['item_id' => $id]); // 使用 all() 方法的陣列條件
        $oldIds = array_column($oldVariants, 'id');
        $postIds = [];
        foreach ($_POST['variant'] as $v) {
            // 強制型態轉換，避免送空值
            $color_id = intval($v['color_id']);
            $size_id = intval($v['size_id']);
            $cost_price = isset($v['cost_price']) ? floatval($v['cost_price']) : 0;
            $sell_price = floatval($v['sell_price']);
            $stock = intval($v['stock']);
            $min_stock = intval($v['min_stock']);
            if (!empty($v['id'])) {
                // update
                $Variant->update($v['id'], [
                    'color_id' => $color_id,
                    'size_id' => $size_id,
                    'cost_price' => $cost_price,
                    'sell_price' => $sell_price,
                    'stock' => $stock,
                    'min_stock' => $min_stock
                ]);
                $postIds[] = $v['id'];
            } else {
                // insert
                $Variant->insert([
                    'item_id' => $id,
                    'color_id' => $color_id,
                    'size_id' => $size_id,
                    'cost_price' => $cost_price,
                    'sell_price' => $sell_price,
                    'stock' => $stock,
                    'min_stock' => $min_stock
                ]);
            }
        }
        // 刪除被移除的規格
        foreach ($oldIds as $oid) {
            if (!in_array($oid, $postIds)) {
                $Variant->delete($oid);
            }
        }
        header('Location: list.php');
        exit;
    } catch (Throwable $e) {
        echo '<pre style="color:red;">錯誤：' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯商品</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <h1 class="main-title">編輯商品</h1>
    <form method="post" enctype="multipart/form-data" class="form-container card mx-auto mt-4" style="max-width:520px;" autocomplete="off">
        <div class="mb-3">
            <label class="form-label">名稱：</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">分類：</label>
            <select name="category_id" id="category_id" class="form-select" required>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $item['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">商品圖片：</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <?php if($item['image']): ?>
                <br><img src="../../uploads/<?= htmlspecialchars($item['image']) ?>" class="img-thumbnail mt-2" style="max-width:100px;">
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label">描述：</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($item['description']) ?></textarea>
        </div>
        <hr class="my-4">
        <h3 class="text-center mb-3" style="color:#d2691e;">顏色/尺寸/規格與庫存</h3>
        <div class="grid" id="variantGrid">
            <?php foreach($variants as $idx => $v): ?>
            <div class="variant-card p-3 mb-3">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">顏色：</label>
                        <select name="variant[<?= $idx ?>][color_id]" class="form-select" required>
                            <option value="">請選擇</option>
                            <?php foreach($colors as $col): ?>
                                <option value="<?= $col['id'] ?>" <?= $col['id'] == $v['color_id'] ? 'selected' : '' ?>><?= htmlspecialchars($col['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="variant[<?= $idx ?>][id]" value="<?= $v['id'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">尺寸：</label>
                        <select name="variant[<?= $idx ?>][size_id]" class="form-select" required>
                            <option value="">請選擇</option>
                            <?php foreach($sizes as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $s['id'] == $v['size_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">成本：</label>
                        <input type="number" name="variant[<?= $idx ?>][cost_price]" class="form-control" value="<?= htmlspecialchars($v['cost_price']) ?>" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">售價：</label>
                        <input type="number" name="variant[<?= $idx ?>][sell_price]" class="form-control" value="<?= htmlspecialchars($v['sell_price']) ?>" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">庫存：</label>
                        <input type="number" name="variant[<?= $idx ?>][stock]" class="form-control" value="<?= htmlspecialchars($v['stock']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">最低庫存：</label>
                        <input type="number" name="variant[<?= $idx ?>][min_stock]" class="form-control" value="<?= htmlspecialchars($v['min_stock']) ?>" required>
                    </div>
                    <div class="col-12 text-end">
                        <button type="button" class="removeVariant btn btn-outline-danger btn-sm">刪除</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="addVariantBtn" class="btn btn-secondary btn-sm mb-3">＋新增規格</button>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">儲存</button>
            <a href="list.php" class="btn btn-outline-secondary">返回列表</a>
        </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
// 動態新增/刪除顏色/尺寸規格（卡片式）
let variantIdx = <?= count($variants) ?>;
document.getElementById('addVariantBtn').onclick = function() {
    const grid = document.getElementById('variantGrid');
    let lastCost = '', lastSell = '';
    const cards = grid.querySelectorAll('.variant-card');
    if (cards.length > 0) {
        const lastCard = cards[cards.length - 1];
        const costInput = lastCard.querySelector("input[name*='[cost_price]']");
        const sellInput = lastCard.querySelector("input[name*='[sell_price]']");
        if (costInput) lastCost = costInput.value;
        if (sellInput) lastSell = sellInput.value;
    }
    const div = document.createElement('div');
    div.className = 'variant-card p-3 mb-3';
    div.innerHTML = `
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">顏色：</label>
                <select name="variant[${variantIdx}][color_id]" class="form-select" required>
                    <option value="">請選擇</option>
                    <?php foreach($colors as $col): ?>
                        <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">尺寸：</label>
                <select name="variant[${variantIdx}][size_id]" class="form-select" required>
                    <option value="">請選擇</option>
                    <?php foreach($sizes as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">成本：</label>
                <input type="number" name="variant[${variantIdx}][cost_price]" class="form-control" step="0.01" required value="${lastCost}">
            </div>
            <div class="col-md-6">
                <label class="form-label">售價：</label>
                <input type="number" name="variant[${variantIdx}][sell_price]" class="form-control" step="0.01" required value="${lastSell}">
            </div>
            <div class="col-md-6">
                <label class="form-label">庫存：</label>
                <input type="number" name="variant[${variantIdx}][stock]" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">最低庫存：</label>
                <input type="number" name="variant[${variantIdx}][min_stock]" class="form-control" value="5" required>
            </div>
            <div class="col-12 text-end">
                <button type="button" class="removeVariant btn btn-outline-danger btn-sm">刪除</button>
            </div>
        </div>
    `;
    grid.appendChild(div);
    variantIdx++;
};
document.getElementById('variantGrid').onclick = function(e) {
    if (e.target.classList.contains('removeVariant')) {
        if (document.querySelectorAll('#variantGrid .variant-card').length > 1) {
            e.target.closest('.variant-card').remove();
        } else {
            alert('至少要有一個顏色/尺寸規格');
        }
    }
};
// 表單送出前檢查所有顏色和尺寸必選
    document.querySelector('form').onsubmit = function(e) {
        let colorSelects = document.querySelectorAll("select[name*='color_id']");
        let sizeSelects = document.querySelectorAll("select[name*='size_id']");

        for(let sel of colorSelects) {
            if(!sel.value || isNaN(sel.value) || parseInt(sel.value) <= 0) {
                alert('請為每一行選擇有效顏色！');
                sel.focus();
                e.preventDefault();
                return false;
            }
        }
        for(let sel of sizeSelects) {
            if(!sel.value || isNaN(sel.value) || parseInt(sel.value) <= 0) {
                alert('請為每一行選擇有效尺寸！');
                sel.focus();
                e.preventDefault();
                return false;
            }
        }
        return true;
    };
    </script>
</body>
</html>