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
        if (!empty($v['id'])) {
            // update
            $Variant->update($v['id'], [
                'color_id' => $v['color_id'],
                'cost_price' => $v['cost_price'],
                'sell_price' => $v['sell_price'],
                'stock' => $v['stock'],
                'min_stock' => $v['min_stock']
            ]);
            $postIds[] = $v['id'];
        } else {
            // insert
            $Variant->insert([
                'item_id' => $id,
                'color_id' => $v['color_id'],
                'cost_price' => $v['cost_price'],
                'sell_price' => $v['sell_price'],
                'stock' => $v['stock'],
                'min_stock' => $v['min_stock']
            ]);
        }
    }
    // 刪除被移除的規格（只刪除資料庫，畫面上的 JS 行刪除不會送 id）
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
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯商品</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <h1 class="main-title">編輯商品</h1>
    <form method="post" enctype="multipart/form-data" class="form-container">
        <label>名稱：<input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required></label>
        <label>分類：
            <select name="category_id" id="category_id" required>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $item['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>商品圖片：<input type="file" name="image" accept="image/*"></label>
        <?php if($item['image']): ?>
            <br><img src="../../uploads/<?= htmlspecialchars($item['image']) ?>" style="max-width:60px;">
        <?php endif; ?>
        <label>描述：<textarea name="description"><?= htmlspecialchars($item['description']) ?></textarea></label>
        <hr style="margin:2em 0;">
        <h3 style="color:#d2691e;">顏色/規格與庫存</h3>
        <div class="grid" id="variantGrid">
            <?php foreach($variants as $idx => $v): ?>
            <div class="variant-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;">
                <label>顏色：
                    <select name="variant[<?= $idx ?>][color_id]" required>
                        <option value="">請選擇</option>
                        <?php foreach($colors as $col): ?>
                            <option value="<?= $col['id'] ?>" <?= $col['id'] == $v['color_id'] ? 'selected' : '' ?>><?= htmlspecialchars($col['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="variant[<?= $idx ?>][id]" value="<?= $v['id'] ?>">
                </label>
                <label>售價：<input type="number" name="variant[<?= $idx ?>][sell_price]" value="<?= intval($v['sell_price']) ?>" step="1" required></label>
                <label>庫存：<input type="number" name="variant[<?= $idx ?>][stock]" value="<?= $v['stock'] ?>" required></label>
                <label>最低庫存：<input type="number" name="variant[<?= $idx ?>][min_stock]" value="<?= $v['min_stock'] ?>" required></label>
                <button type="button" class="removeVariant btn-back" style="background:#fff0e0;color:#d2691e;">刪除</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="addVariantBtn">＋新增規格</button>
        <br><br>
        <div class="card-action-bar" style="margin-top:1.2em;display:flex;gap:0.5em;flex-wrap:wrap;">
            <button type="submit" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">儲存</button>
            <a href="list.php" class="btn-back btn-sm">返回列表</a>
        </div>
    </form>
    <script>
// 動態新增/刪除顏色規格（卡片式）
let variantIdx = <?= count($variants) ?>;
document.getElementById('addVariantBtn').onclick = function() {
    const grid = document.getElementById('variantGrid');
    const div = document.createElement('div');
    div.className = 'variant-card';
    div.style = 'background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;';
    div.innerHTML = `
        <label>顏色：
            <select name="variant[${variantIdx}][color_id]" required>
                <option value="">請選擇</option>
                <?php foreach($colors as $col): ?>
                    <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>售價：<input type="number" name="variant[${variantIdx}][sell_price]" step="1" required></label>
        <label>庫存：<input type="number" name="variant[${variantIdx}][stock]" required></label>
        <label>最低庫存：<input type="number" name="variant[${variantIdx}][min_stock]" value="5" required></label>
        <button type="button" class="removeVariant btn-back" style="background:#fff0e0;color:#d2691e;">刪除</button>
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