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
    <style>
    .form-container {
        max-width: 350px;
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
    .form-container input[type="text"] {
        padding: 0.6em 1em;
        border: 1px solid #ffb347;
        border-radius: 6px;
        font-size: 1em;
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
    <h1 class="main-title">顏色管理</h1>
    <form method="post" class="form-container" style="max-width:400px;margin-bottom:2em;">
        <label>顏色名稱：
            <input type="text" name="name" required>
        </label>
        <button type="submit">新增</button>
        <a href="../items/list.php" class="btn-back">返回商品列表</a>
    </form>
    <div class="form-container" style="max-width:400px;">
        <h2 style="font-size:1.1em;color:#d2691e;margin-bottom:1em;">現有顏色</h2>
        <table style="width:100%;">
            <tr>
                <th style="text-align:left;">名稱</th>
                <th style="width:60px;">操作</th>
            </tr>
            <?php foreach($colors as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td>
                    <a href="?delete=<?= $c['id'] ?>" class="btn-back" style="background:#fff0e0;color:#d2691e;padding:0.2em 1em;font-size:0.95em;border:1px solid #ffb347;" onclick="return confirm('確定要刪除這個顏色？')">刪除</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>