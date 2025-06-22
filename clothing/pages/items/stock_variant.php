<?php
require_once '../../includes/db.php';
$Variant = new DB('item_variants');
$Color = new DB('colors');
$variant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$variant = $Variant->find($variant_id);
$color = '';
if ($variant && $variant['color_id']) {
    $colorRow = (new DB('colors'))->find($variant['color_id']);
    $color = $colorRow ? $colorRow['name'] : '';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $add_stock = intval($_POST['add_stock']);
    if ($variant && $add_stock > 0) {
        $variant['stock'] += $add_stock;
        $Variant->save($variant);
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
    <title>補庫存 - 規格 #<?= $variant_id ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <div class="card form-card form-container" style="max-width:400px;margin:40px auto;">
        <h2 style="text-align:center;">補庫存：規格 #<?= $variant_id ?> <?= $color ? '('.htmlspecialchars($color).')' : '' ?></h2>
        <div style="text-align:center;margin-bottom:1em;">現有庫存：<?= $variant ? $variant['stock'] : '-' ?></div>
        <?php if (!empty($msg)): ?><div style="color:red;text-align:center;"><?= $msg ?></div><?php endif; ?>
        <form method="post">
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
