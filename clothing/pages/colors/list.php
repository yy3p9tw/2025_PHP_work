<?php
require_once '../../includes/db.php';
$Color = new DB('colors');
$colors = $Color->all();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>顏色列表</title>
    <style>
    @media (max-width: 700px) {
        .main-title { font-size: 1.2em; }
        table, thead, tbody, th, td, tr { display: block; width: 100%; }
        thead { display: none; }
        tr { margin-bottom: 1.2em; background: #fff; border-radius: 10px; box-shadow: 0 1px 6px #ffb34722; }
        td { padding: 0.7em 1em; border: none; border-bottom: 1px solid #ffe0e0; position: relative; }
        td:before { content: attr(data-label); font-weight: bold; color: #b97a56; display: block; margin-bottom: 0.3em; }
        .btn-back { width: 100%; margin-bottom: 0.5em; }
    }
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.2em;
    }
    .product-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px #ffb34722;
        padding: 1.2em 1em;
        margin-bottom: 1.2em;
    }
    </style>
</head>
<body>
    <h1 class="main-title">顏色列表</h1>
    <div class="action-bar" style="margin-bottom:1.5em;">
        <a href="add.php" class="btn-back btn-sm">＋ 新增顏色</a>
    </div>
    <div class="grid">
    <?php foreach($colors as $col): ?>
        <div class="product-card">
            <div style="font-weight:bold;font-size:1.1em;margin-bottom:0.5em;">ID：<?= $col['id'] ?></div>
            <div>名稱：<?= htmlspecialchars($col['name']) ?></div>
            <div class="card-action-bar" style="margin-top:0.7em;display:flex;gap:0.5em;flex-wrap:wrap;">
                <a href="edit.php?id=<?= $col['id'] ?>" class="btn-back btn-sm">編輯</a>
                <a href="delete.php?id=<?= $col['id'] ?>" class="btn-back btn-sm btn-del" onclick="return confirm('確定要刪除嗎？')">刪除</a>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</body>
</html>