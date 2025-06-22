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
            <td><?= $col['id'] ?></td>
            <td><?= htmlspecialchars($col['name']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>