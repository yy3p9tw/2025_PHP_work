<?php
// filepath: c:\2025-PHP\clothing\pages\colors\add.php
require_once '../../includes/db.php';
$Color = new DB('colors');

// 刪除顏色
if (isset($_GET['delete'])) {
    $Color->delete($_GET['delete']);
    header('Location: add.php');
    exit;
}

// 新增顏色
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Color->insert(['name' => $_POST['name']]);
    header('Location: add.php');
    exit;
}

// 取得所有顏色，並依 id DESC 排序
$colors = $Color->all();
usort($colors, function($a, $b) {
    return $b['id'] - $a['id'];
});
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增顏色</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <h1 class="main-title">顏色管理</h1>
    <form method="post" class="form-container card" style="max-width:420px;margin:auto;">
        <label>顏色名稱：<input type="text" name="name" required></label>
        <div class="card-action-bar" style="margin-top:1.2em;display:flex;gap:0.5em;flex-wrap:wrap;">
            <button type="submit" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">新增</button>
            <a href="../items/list.php" class="btn-back btn-sm">返回商品列表</a>
        </div>
    </form>
    <div class="form-container" style="max-width:600px;">
        <h2 style="font-size:1.1em;color:#d2691e;margin-bottom:1em;">現有顏色</h2>
        <div class="grid">
            <?php foreach($colors as $c): ?>
            <div class="color-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;display:flex;align-items:center;justify-content:space-between;">
                <div style="font-weight:bold;font-size:1.1em;">名稱：<?= htmlspecialchars($c['name']) ?></div>
                <a href="?delete=<?= $c['id'] ?>" class="btn-back" style="background:#fff0e0;color:#d2691e;padding:0.2em 1em;font-size:0.95em;border:1px solid #ffb347;" onclick="return confirm('確定要刪除這個顏色？')">刪除</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>