<?php
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Category = new DB('categories');
    $Category->insert(['name' => $_POST['name']]);
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增分類</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <h1 class="main-title">新增分類</h1>
    <form method="post" class="form-container card" style="max-width:420px;margin:auto;">
        <label>分類名稱：<input type="text" name="name" required></label>
        <div class="card-action-bar" style="margin-top:1.2em;display:flex;gap:0.5em;flex-wrap:wrap;">
            <button type="submit" class="btn-back btn-sm" style="background:#ffb347;color:#fff;">新增</button>
            <a href="list.php" class="btn-back btn-sm">返回列表</a>
        </div>
    </form>
</body>
</html>