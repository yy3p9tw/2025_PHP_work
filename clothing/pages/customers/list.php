<?php
require_once '../../includes/db.php';
$Customer = new DB('customers');
$customers = $Customer->all();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客戶管理</title>
    <link rel="stylesheet" href="../../css/style.css">
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
<body class="warm-bg">
    <div style="max-width:950px;margin:40px auto 0;">
        <h1 class="main-title">客戶管理</h1>
        <div style="text-align:right;margin-bottom:1.5em;">
            <a href="../../index.php" class="btn-back">返回首頁</a>
            <a href="add.php" class="btn-back" style="margin-left:8px;">＋ 新增客戶</a>
        </div>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 16px #ffb34733;padding:2em 1em 1em 1em;">
        <table style="width:100%;">
            <tr>
                <th>姓名</th>
                <th>電話</th>
                <th>Email</th>
                <th>地址</th>
                <th>操作</th>
            </tr>
            <?php foreach($customers as $c): ?>
            <tr>
                <td data-label="姓名"><?= htmlspecialchars($c['name']) ?></td>
                <td data-label="電話"><?= htmlspecialchars($c['phone']) ?></td>
                <td data-label="Email"><?= htmlspecialchars($c['email']) ?></td>
                <td data-label="地址"><?= htmlspecialchars($c['address']) ?></td>
                <td data-label="操作">
                    <a href="edit.php?id=<?= $c['id'] ?>" class="btn-back" style="padding:0.3em 1em;font-size:0.95em;">編輯</a>
                    <a href="delete.php?id=<?= $c['id'] ?>" class="btn-back" style="background:#fff0e0;color:#d2691e;padding:0.3em 1em;font-size:0.95em;border:1px solid #ffb347;" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
    </div>
</body>
</html>
