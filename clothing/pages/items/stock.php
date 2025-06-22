<?php
require_once '../../includes/db.php';
$Item = new DB('items');
$Variant = new DB('item_variants');
$Color = new DB('colors');
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$item = null;
$tmp = $Item->all("id = $item_id");
if ($tmp) $item = $tmp[0];
$variants = $Variant->all("item_id = $item_id");
$colors = $Color->all();
$colorMap = [];
foreach($colors as $c) $colorMap[$c['id']] = $c['name'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vid = intval($_POST['variant_id']);
    $add_stock = intval($_POST['add_stock']);
    $v = null;
    $tmpv = $Variant->all("id = $vid");
    if ($tmpv) $v = $tmpv[0];
    if ($v && $add_stock > 0) {
        $v['stock'] += $add_stock;
        $Variant->save($v);
        header('Location: list.php');
        exit;
    }
    $msg = '補庫存失敗，請檢查輸入';
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>補庫存 - <?= htmlspecialchars($item['name'] ?? '') ?></title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
    .form-container {
        max-width: 400px;
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
    .form-container input[type="number"] {
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
    <div class="card form-card form-container">
        <h2 style="text-align:center;">補庫存：<?= htmlspecialchars($item['name'] ?? '') ?></h2>
        <?php if (!empty($msg)): ?><div style="color:red;text-align:center;"><?= $msg ?></div><?php endif; ?>
        <form method="post">
            <label>選擇規格：</label>
            <select name="variant_id" required style="width:100%;padding:0.5em 1em;margin-bottom:1em;">
                <?php foreach($variants as $v): ?>
                <option value="<?= $v['id'] ?>">
                    <?= isset($colorMap[$v['color_id']]) ? $colorMap[$v['color_id']] : '' ?> (#<?= $v['id'] ?>) ｜現有庫存：<?= $v['stock'] ?>
                </option>
                <?php endforeach; ?>
            </select>
            <label>補貨數量：</label>
            <input type="number" name="add_stock" min="1" required style="width:100%;padding:0.5em 1em;margin-bottom:1em;">
            <button type="submit" class="btn-back" style="width:100%;">確認補庫存</button>
        </form>
        <div style="text-align:center;margin-top:1em;">
            <a href="list.php" class="btn-back">返回商品列表</a>
        </div>
    </div>
</body>
</html>
