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
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.2em;
        }
        .product-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #ffb34722;
            padding: 1.2em 1em;
            margin-bottom: 1.2em;
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
        @media (max-width: 700px) {
            .main-title { font-size: 1.2em; }
            table, thead, tbody, th, td, tr { display: block; width: 100%; }
            thead { display: none; }
            tr { margin-bottom: 1.2em; background: #fff; border-radius: 10px; box-shadow: 0 1px 6px #ffb34722; }
            td { padding: 0.7em 1em; border: none; border-bottom: 1px solid #ffe0e0; position: relative; }
            td:before { content: attr(data-label); font-weight: bold; color: #b97a56; display: block; margin-bottom: 0.3em; }
            .btn-back { width: 100%; margin-bottom: 0.5em; }
        }
    </style>
</head>
<body class="warm-bg">
    <div style="max-width:600px;margin:40px auto 0;">
        <h1 class="main-title">分類列表</h1>
        <div style="text-align:right;margin-bottom:1.5em;">
            <a href="add.php" class="btn-back">＋ 新增分類</a>
            <a href="../items/list.php" class="btn-back">返回商品列表</a>
        </div>
        <div class="form-container">
    <div class="grid">
    <?php foreach($categories as $cat): ?>
        <div class="product-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;">
            <div style="font-weight:bold;font-size:1.1em;margin-bottom:0.5em;">分類名稱：<?= htmlspecialchars($cat['name']) ?></div>
            <div class="card-action-bar" style="margin-top:0.7em;display:flex;gap:0.5em;flex-wrap:wrap;">
                <a href="edit.php?id=<?= $cat['id'] ?>" class="btn-back btn-sm">編輯</a>
                <a href="list.php?delete=<?= $cat['id'] ?>" class="btn-back btn-sm btn-del" onclick="return confirm('確定要刪除嗎？')">刪除</a>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
        </div>
    </div>
</body>
</html>