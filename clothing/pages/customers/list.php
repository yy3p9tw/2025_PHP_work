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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .main-title { color: #d2691e; text-align: center; margin-top: 2em; }
        .card { box-shadow: 0 2px 16px #ffb34733; }
        .btn-back { background: #ffb347; color: #fff; border: 1px solid #ffb347; }
        .btn-back:hover { background: #ffa500; color: #fff; }
        .card-action-bar { margin-top:0.7em; display:flex; gap:0.5em; flex-wrap:wrap; }
        @media (max-width: 700px) {
            .main-title { font-size: 1.2em; }
        }
    </style>
</head>
<body class="warm-bg">
    <div class="container py-4">
        <h1 class="main-title">客戶管理</h1>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="../../index.php" class="btn btn-back btn-sm">返回首頁</a>
            <a href="add.php" class="btn btn-back btn-sm">＋ 新增客戶</a>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
        <?php foreach($customers as $c): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-2"><?= htmlspecialchars($c['name']) ?></h5>
                        <div class="mb-1"><span class="fw-bold">電話：</span><?= htmlspecialchars($c['phone']) ?></div>
                        <div class="mb-1"><span class="fw-bold">Email：</span><?= htmlspecialchars($c['email']) ?></div>
                        <div class="mb-1"><span class="fw-bold">地址：</span><?= htmlspecialchars($c['address']) ?></div>
                        <?php if (!empty($c['notes'])): ?>
                        <div class="mb-1"><span class="fw-bold">備註：</span><?= htmlspecialchars($c['notes']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-action-bar px-3 pb-3">
                        <a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-back btn-sm">編輯</a>
                        <a href="delete.php?id=<?= $c['id'] ?>" class="btn btn-back btn-sm btn-danger" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
