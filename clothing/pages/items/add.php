<?php
require_once '../../includes/db.php';
$Category = new DB('categories');
$categories = $Category->all();
$Color = new DB('colors');
$colors = $Color->all();

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
    // 新增多個顏色規格
    $Variant = new DB('item_variants');
    foreach ($_POST['variant'] as $v) {
        $Variant->insert([
            'item_id' => $item_id,
            'color_id' => $v['color_id'],
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
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <h1 class="main-title">新增商品</h1>
    <form method="post" class="form-container card" style="max-width:420px;margin:auto;" enctype="multipart/form-data">
        <label>商品名稱：<input type="text" name="name" required></label>
        <label>分類：
            <select name="category_id" id="category_id" required>
                <option value="">請選擇</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="addCatBtn" class="btn-back" style="margin-left:8px;">＋新增分類</button>
        </label>
        <div id="addCatBox" style="display:none;margin:8px 0;">
            <input type="text" id="newCatName" placeholder="輸入新分類名稱">
            <button type="button" id="saveCatBtn">儲存</button>
            <button type="button" id="cancelCatBtn">取消</button>
        </div>
        <label>商品圖片：<input type="file" name="image" accept="image/*"></label>
        <label>描述：<textarea name="description"></textarea></label>
        <hr style="margin:2em 0;">
        <h3 style="color:#d2691e;">顏色/規格與庫存</h3>
        <div class="grid" id="variantGrid">
            <div class="variant-card">
                <label>顏色：
                    <select name="variant[0][color_id]" required>
                        <option value="">請選擇</option>
                        <?php foreach($colors as $col): ?>
                            <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>成本：<input type="number" name="variant[0][cost_price]" step="1" required></label>
                <label>售價：<input type="number" name="variant[0][sell_price]" step="1" required></label>
                <label>庫存：<input type="number" name="variant[0][stock]" required></label>
                <label>最低庫存：<input type="number" name="variant[0][min_stock]" value="5" required></label>
                <button type="button" class="removeVariant btn-back" style="background:#fff0e0;color:#d2691e;">刪除</button>
            </div>
        </div>
        <button type="button" id="addVariantBtn">＋新增規格</button>
        <div class="card-action-bar" style="margin-top:1.2em;display:flex;gap:0.5em;flex-wrap:wrap;">
            <button type="submit" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">新增</button>
            <a href="list.php" class="btn-back btn-sm">返回列表</a>
        </div>
    </form>
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
// 移除每一行的＋按鈕與新增顏色輸入框
function removeRowAddColorBtn() {
    document.querySelectorAll('.addColorBtn, .addColorBox').forEach(el => el.remove());
}
removeRowAddColorBtn();
// 動態新增/刪除顏色規格（卡片式）
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
        <label>成本：<input type="number" name="variant[${variantIdx}][cost_price]" step="1" required value="${lastCost}"></label>
        <label>售價：<input type="number" name="variant[${variantIdx}][sell_price]" step="1" required value="${lastSell}"></label>
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
    <style>
    @media (min-width: 900px) {
      #variantGrid {
        display: flex;
        flex-direction: column;
        gap: 1.2em;
      }
      #variantGrid .variant-card {
        width: 100%;
        max-width: 100%;
      }
    }
    /* 讓「＋新增規格」按鈕與「刪除」按鈕間距 5px */
    #addVariantBtn {
      margin-top: 10px;
    }
    #variantGrid .removeVariant {
      margin-left: 10px;
    }
    </style>
</body>
</html>