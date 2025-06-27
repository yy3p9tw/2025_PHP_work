<?php
require_once '../../includes/db.php';
$Category = new DB('categories');
$categories = $Category->all();
$Color = new DB('colors');
$colors = $Color->all();
$Size = new DB('sizes');
$sizes = $Size->all();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Item = new DB('items');
    // 圖片上傳處理
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowExt = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowExt)) {
            $imageName = uniqid('img_', true) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../../uploads/' . $imageName);
        }
    }
    // 新增商品主檔
    $Item->insert([
        'name' => $_POST['name'],
        'category_id' => $_POST['category_id'],
        'description' => $_POST['description'],
        'image' => $imageName
    ]);
    // 取得新商品id（用 PDO lastInsertId）
    $item_id = $Item->getLastInsertId();
    // 新增多個顏色/尺寸規格
    $Variant = new DB('item_variants');
    foreach ($_POST['variant'] as $v) {
        $Variant->insert([
            'item_id' => $item_id,
            'color_id' => $v['color_id'],
            'size_id' => $v['size_id'],
            'cost_price' => $v['cost_price'],
            'sell_price' => $v['sell_price'],
            'stock' => $v['stock'],
            'min_stock' => $v['min_stock']
        ]);
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
    <title>新增商品</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <h1 class="main-title">新增商品</h1>
    <form method="post" class="form-container card" style="max-width:420px;margin:auto;" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">商品名稱：</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">分類：</label>
            <select name="category_id" id="category_id" class="form-select" required>
                <option value="">請選擇</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="addCatBtn" class="btn btn-outline-secondary btn-sm ms-2">＋新增分類</button>
        </div>
        <div id="addCatBox" class="mb-3" style="display:none;">
            <div class="input-group">
                <input type="text" id="newCatName" class="form-control" placeholder="輸入新分類名稱">
                <button type="button" id="saveCatBtn" class="btn btn-primary">儲存</button>
                <button type="button" id="cancelCatBtn" class="btn btn-secondary">取消</button>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">商品圖片：</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        <div class="mb-3">
            <label class="form-label">描述：</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <hr class="my-4">
        <h3 class="text-center mb-3" style="color:#d2691e;">顏色/尺寸/規格與庫存</h3>
        <div class="grid" id="variantGrid">
            <div class="variant-card p-3 mb-3">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">顏色：</label>
                        <select name="variant[0][color_id]" class="form-select" required>
                            <option value="">請選擇</option>
                            <?php foreach($colors as $col): ?>
                                <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">尺寸：</label>
                        <select name="variant[0][size_id]" class="form-select" required>
                            <option value="">請選擇</option>
                            <?php foreach($sizes as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">成本：</label>
                        <input type="number" name="variant[0][cost_price]" class="form-control" step="1" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">售價：</label>
                        <input type="number" name="variant[0][sell_price]" class="form-control" step="1" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">庫存：</label>
                        <input type="number" name="variant[0][stock]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">最低庫存：</label>
                        <input type="number" name="variant[0][min_stock]" class="form-control" value="5" required>
                    </div>
                    <div class="col-12 text-end">
                        <button type="button" class="removeVariant btn btn-outline-danger btn-sm">刪除</button>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" id="addVariantBtn" class="btn btn-secondary btn-sm mb-3">＋新增規格</button>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">新增</button>
            <a href="list.php" class="btn btn-outline-secondary">返回列表</a>
        </div>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('addCatBtn').onclick = function() {
        document.getElementById('addCatBox').style.display = 'block';
        document.getElementById('newCatName').focus();
    };
    document.getElementById('cancelCatBtn').onclick = function() {
        document.getElementById('addCatBox').style.display = 'none';
        document.getElementById('newCatName').value = '';
    };
    document.getElementById('saveCatBtn').onclick = function() {
        var name = document.getElementById('newCatName').value.trim();
        if (!name) { alert('請輸入分類名稱'); return; }
        fetch('../categories/ajax_add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'name=' + encodeURIComponent(name)
        })
        .then(r => r.json())
        .then(data => {
            if (data.id) {
                var sel = document.getElementById('category_id');
                var opt = document.createElement('option');
                opt.value = data.id;
                opt.textContent = data.name;
                opt.selected = true;
                sel.appendChild(opt);
                document.getElementById('addCatBox').style.display = 'none';
                document.getElementById('newCatName').value = '';
            } else {
                alert('新增失敗');
            }
        });
    };
    // 動態新增/刪除顏色/尺寸規格（卡片式）
    let variantIdx = 1;
    document.getElementById('addVariantBtn').onclick = function() {
        const grid = document.getElementById('variantGrid');
        // 取得上一個規格的成本與售價
        let lastCost = '';
        let lastSell = '';
        const lastCard = grid.querySelector('.variant-card:last-of-type');
        if (lastCard) {
            const costInput = lastCard.querySelector('input[name*="[cost_price]"]');
            const sellInput = lastCard.querySelector('input[name*="[sell_price]"]');
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
                    <input type="number" name="variant[${variantIdx}][cost_price]" class="form-control" step="1" required value="${lastCost}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">售價：</label>
                    <input type="number" name="variant[${variantIdx}][sell_price]" class="form-control" step="1" required value="${lastSell}">
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