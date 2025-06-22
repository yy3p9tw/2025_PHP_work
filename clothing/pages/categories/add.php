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
    <title>新增分類</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <h1 class="main-title">新增分類</h1>
    <form method="post" class="form-container">
        <label>分類名稱：</label>
        <input type="text" name="name" required>
        <button type="submit">新增</button>
        <a href="list.php" class="btn-back">返回列表</a>
    </form>
</body>
</html>