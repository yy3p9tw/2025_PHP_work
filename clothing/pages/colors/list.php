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
    </style>
</head>
<body>
    <h1>顏色列表</h1>
    <a href="add.php">新增顏色</a>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>名稱</th>
        </tr>
        <?php foreach($colors as $col): ?>
        <tr>
            <td data-label="ID"><?= $col['id'] ?></td>
            <td data-label="名稱"><?= htmlspecialchars($col['name']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>