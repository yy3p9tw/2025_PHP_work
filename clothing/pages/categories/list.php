<?php
require_once '../../includes/db.php';
$Category = new DB('categories');
// 刪除分類
if (isset($_GET['delete'])) {
    $Category->delete($_GET['delete']);
    header('Location: list.php');
    exit;
}
$categories = $Category->all();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>分類列表</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body.warm-bg { background: #fff7f0; }
        h1.main-title { color: #d2691e; text-align: center; margin-top: 2em; }
        .form-container {
            background: #fff;
            max-width: 500px;
            margin: 40px auto;
            padding: 2em 2em 1em 2em;
            border-radius: 14px;
            box-shadow: 0 2px 16px #ffb34733;
        }
        table {
            background: #fff;
            border-collapse: collapse;
            margin: 0 auto 2em auto;
            box-shadow: 0 2px 8px #0001;
            width: 100%;
            max-width: 500px;
        }
        th, td {
            padding: 8px 16px;
            border: 1px solid #ccc;
        }
        th {
            background: #e3f0fa;
        }
        .btn-back, button {
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
        .btn-back:hover, button:hover {
            background: linear-gradient(135deg, #ff9966 0%, #ffb347 100%);
        }
    </style>
</head>
<body class="warm-bg">
    <div style="max-width:600px;margin:40px auto 0;">
        <h1 class="main-title">分類列表</h1>
        <div style="text-align:right;margin-bottom:1.5em;">
            <a href="add.php" class="btn-back">＋ 顏色列表</a>
            <a href="../items/list.php" class="btn-back">返回商品列表</a>
        </div>
        <div class="form-container">
        <table>
            <tr>
                <th>ID</th>
                <th>名稱</th>
                <th>操作</th>
            </tr>
            <?php foreach($categories as $cat): ?>
            <tr>
                <td><?= $cat['id'] ?></td>
                <td><?= htmlspecialchars($cat['name']) ?></td>
                <td>
                    <a href="?delete=<?= $cat['id'] ?>" class="btn-back" style="background:#fff0e0;color:#d2691e;padding:0.2em 1em;font-size:0.95em;border:1px solid #ffb347;" onclick="return confirm('確定要刪除這個分類？')">刪除</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
    </div>
</body>
</html>