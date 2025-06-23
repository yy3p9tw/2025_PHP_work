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
        <div class="action-bar" style="margin-bottom:1.5em;">
            <a href="../../index.php" class="btn-back btn-sm">返回首頁</a>
            <a href="add.php" class="btn-back btn-sm">＋ 新增客戶</a>
        </div>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 16px #ffb34733;padding:2em 1em 1em 1em;">
        <div class="grid">
        <?php foreach($customers as $c): ?>
            <div class="product-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px #ffb34722;padding:1.2em 1em 1em 1em;margin-bottom:1.2em;">
                <div style="font-weight:bold;font-size:1.1em;margin-bottom:0.5em;">姓名：<?= htmlspecialchars($c['name']) ?></div>
                <div>電話：<?= htmlspecialchars($c['phone']) ?></div>
                <div>Email：<?= htmlspecialchars($c['email']) ?></div>
                <div>地址：<?= htmlspecialchars($c['address']) ?></div>
                <div class="card-action-bar" style="margin-top:0.7em;display:flex;gap:0.5em;flex-wrap:wrap;">
                    <a href="edit.php?id=<?= $c['id'] ?>" class="btn-back btn-sm">編輯</a>
                    <a href="delete.php?id=<?= $c['id'] ?>" class="btn-back btn-sm btn-del" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        </div>
    </div>
</body>
</html>
