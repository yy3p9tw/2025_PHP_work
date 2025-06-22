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
    <style>
        *{
            box-sizing: border-box;
        }
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
        label { display: block; margin-bottom: 0.5em; color: #b97a56; font-weight: 500; }
        input[type="text"], input[type="number"], select, textarea {
            width: 100%;
            padding: 0.5em;
            border: 1px solid #ffb347;
            border-radius: 6px;
            margin-bottom: 1.2em;
            font-size: 1em;
        }
        textarea { min-height: 60px; }
        .btn-back, button, input[type="submit"] {
            background: linear-gradient(135deg, #ffb347 0%, #ff9966 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.6em 1.5em;
            font-size: 1em;
            font-weight: 500;
            margin-right: 0.5em;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px #ffb34744;
            display: inline-block;
        }
        .btn-back:hover, button:hover, input[type="submit"]:hover {
            background: linear-gradient(135deg, #ff9966 0%, #ffb347 100%);
        }
        .variant-table th, .variant-table td { padding: 6px 8px; }
        .variant-table { width: 100%; margin-bottom: 1em; background: #fff7f0; border-radius: 8px; }
        .variant-table select {
            font-size: 1.2em;
            min-width: 120px;
            height: 2.6em;
            padding: 0.4em 1.2em 0.4em 0.8em;
            border-radius: 8px;
            border: 1.5px solid #ffb347;
            box-shadow: 0 1px 6px #ffb34733;
        }
    </style>
</head>
<body class="warm-bg">
    <h1 class="main-title">新增商品</h1>
    <form method="post" enctype="multipart/form-data" class="form-container">
        <label>名稱：<input type="text" name="name" required></label>
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
        <table class="variant-table" border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>顏色</th>
                    <th>成本價</th>
                    <th>售價</th>
                    <th>庫存</th>
                    <th>最低庫存</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="variantTbody">
                <tr>
                    <td>
                        <select name="variant[0][color_id]" required>
                            <option value="">請選擇</option>
                            <?php foreach($colors as $col): ?>
                                <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="variant[0][cost_price]" step="0.01" required></td>
                    <td><input type="number" name="variant[0][sell_price]" step="0.01" required></td>
                    <td><input type="number" name="variant[0][stock]" required></td>
                    <td><input type="number" name="variant[0][min_stock]" value="5" required></td>
                    <td><button type="button" class="removeVariant btn-back" style="background:#fff0e0;color:#d2691e;">刪除</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" id="addVariantBtn">＋新增規格</button>
        <br><br>
        <button type="submit">新增</button>
        <a href="list.php">返回列表</a>
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
// 動態新增/刪除顏色規格
let variantIdx = 1;
document.getElementById('addVariantBtn').onclick = function() {
    const tbody = document.getElementById('variantTbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select name="variant[${variantIdx}][color_id]" required>
                <option value="">請選擇</option>
                <?php foreach($colors as $col): ?>
                    <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" name="variant[${variantIdx}][cost_price]" step="0.01" required></td>
        <td><input type="number" name="variant[${variantIdx}][sell_price]" step="0.01" required></td>
        <td><input type="number" name="variant[${variantIdx}][stock]" required></td>
        <td><input type="number" name="variant[${variantIdx}][min_stock]" value="5" required></td>
        <td><button type="button" class="removeVariant btn-back" style="background:#fff0e0;color:#d2691e;">刪除</button></td>
    `;
    tbody.appendChild(tr);
    variantIdx++;
};
document.getElementById('variantTbody').onclick = function(e) {
    if (e.target.classList.contains('removeVariant')) {
        if (document.querySelectorAll('#variantTbody tr').length > 1) {
            e.target.closest('tr').remove();
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