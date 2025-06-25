<?php
require_once '../../includes/db.php';
$Item = new DB('items');
$Category = new DB('categories');
$categories = $Category->all();
$Color = new DB('colors');
$colors = $Color->all();
$Variant = new DB('item_variants');

$id = $_GET['id'] ?? 0;
$item = $Item->all("id = $id")[0] ?? null;
$variants = $Variant->all("item_id = $id");

if (!$item) {
    echo "找不到商品";
    exit;
}

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
        $oldVariants = $Variant->all("item_id = $id");
        $oldIds = array_column($oldVariants, 'id');
        $postIds = [];
        foreach ($_POST['variant'] as $v) {
            // 強制型態轉換，避免送空值
            $color_id = intval($v['color_id']);
            $cost_price = isset($v['cost_price']) ? intval($v['cost_price']) : 0;
            $sell_price = intval($v['sell_price']);
            $stock = intval($v['stock']);
            $min_stock = intval($v['min_stock']);
            if (!empty($v['id'])) {
                // update
                $Variant->update($v['id'], [
                    'color_id' => $color_id,
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
                    'cost_price' => $cost_price,
                    'sell_price' => $sell_price,
                    'stock' => $stock,
                    'min_stock' => $min_stock
                ]);
            }
        }
        // 刪除被移除的規格
        foreach ($oldIds as $oid) {
            $found = false;
            foreach ($_POST['variant'] as $v) {
                if (!empty($v['id']) && $v['id'] == $oid) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
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
    <style>
    .main-title { color: #d2691e; text-align: center; margin-top: 2em; }
    .card { box-shadow: 0 2px 16px #ffb34733; }
    .form-label { font-weight: bold; color: #b97a56; }
    .btn-back { background: #ffb347; color: #fff; border: 1px solid #ffb347; }
    .btn-back:hover { background: #ffa500; color: #fff; }
    .card-action-bar { margin-top:1.2em; display:flex; gap:0.5em; flex-wrap:wrap; }
    @media (min-width: 900px) {
      #variantGrid { display: flex; flex-direction: column; gap: 1.2em; }
      #variantGrid .variant-card { width: 100%; max-width: 100%; }
    }
    @media (max-width: 600px) {
      .main-title { font-size: 1.1em; }
    }
    </style>
</head>
<body class="warm-bg">
    <h1 class="main-title">編輯商品</h1>
    <form method="post" enctype="multipart/form-data" class="card p-4 mx-auto mt-4" style="max-width:520px;" autocomplete="off">
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
                <br><img src="../../uploads/<?= htmlspecialchars($item['image']) ?>" style="max-width:60px;">
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label">描述：</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($item['description']) ?></textarea>
        </div>
        <hr style="margin:2em 0;">
        <h3 style="color:#d2691e;">顏色/規格與庫存</h3>
        <div class="grid" id="variantGrid">
            <?php foreach($variants as $idx => $v): ?>
            <div class="variant-card card mb-3 p-3 bg-light">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label">顏色：</label>
                        <select name="variant[<?= $idx ?>][color_id]" class="form-select" required>
                            <option value="">請選擇</option>
                            <?php foreach($colors as $col): ?>
                                <option value="<?= $col['id'] ?>" <?= $col['id'] == $v['color_id'] ? 'selected' : '' ?>><?= htmlspecialchars($col['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="variant[<?= $idx ?>][id]" value="<?= $v['id'] ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">成本：</label>
                        <input type="number" name="variant[<?= $idx ?>][cost_price]" class="form-control" value="<?= isset($v['cost_price']) ? intval($v['cost_price']) : 0 ?>" step="1" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">售價：</label>
                        <input type="number" name="variant[<?= $idx ?>][sell_price]" class="form-control" value="<?= intval($v['sell_price']) ?>" step="1" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">庫存：</label>
                        <input type="number" name="variant[<?= $idx ?>][stock]" class="form-control" value="<?= $v['stock'] ?>" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">最低庫存：</label>
                        <input type="number" name="variant[<?= $idx ?>][min_stock]" class="form-control" value="<?= $v['min_stock'] ?>" required>
                    </div>
                    <div class="col-12 col-md-1 text-end">
                        <button type="button" class="removeVariant btn btn-outline-danger btn-sm">刪除</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="addVariantBtn" class="btn btn-secondary btn-sm mb-2">＋新增規格</button>
        <div class="card-action-bar">
            <button type="submit" class="btn btn-back btn-sm">儲存</button>
            <a href="list.php" class="btn btn-back btn-sm">返回列表</a>
        </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
// 動態新增/刪除顏色規格（卡片式）
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
    div.className = 'variant-card card mb-3 p-3 bg-light';
    div.innerHTML = `
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">顏色：</label>
                <select name="variant[${variantIdx}][color_id]" class="form-select" required>
                    <option value="">請選擇</option>
                    <?php foreach($colors as $col): ?>
                        <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">成本：</label>
                <input type="number" name="variant[${variantIdx}][cost_price]" class="form-control" step="1" required value="${lastCost}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">售價：</label>
                <input type="number" name="variant[${variantIdx}][sell_price]" class="form-control" step="1" required value="${lastSell}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">庫存：</label>
                <input type="number" name="variant[${variantIdx}][stock]" class="form-control" required>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">最低庫存：</label>
                <input type="number" name="variant[${variantIdx}][min_stock]" class="form-control" value="5" required>
            </div>
            <div class="col-12 col-md-1 text-end">
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
            alert('至少要有一個顏色/規格');
        }
    }
};
// 表單送出前檢查所有顏色必選
    document.querySelector('form').onsubmit = function(e) {
        let selects = document.querySelectorAll("select[name*='color_id']");
        for(let sel of selects) {
            if(!sel.value || isNaN(sel.value) || parseInt(sel.value) <= 0) {
                alert('請為每一行選擇有效顏色！');
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