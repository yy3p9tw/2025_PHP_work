<?php
require_once '../../includes/db.php';
$Color = new DB('colors');
// 刪除顏色
if (isset($_GET['delete'])) {
    $Color->delete($_GET['delete']);
    header('Location: list.php');
    exit;
}
$colors = $Color->all();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>顏色列表</title>
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
        <h1 class="main-title">顏色列表</h1>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="../../index.php" class="btn btn-back btn-sm">返回首頁</a>
            <a href="add.php" class="btn btn-back btn-sm">＋ 新增顏色</a>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
        <?php foreach($colors as $col): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-2">ID：<?= $col['id'] ?></h5>
                        <div class="mb-1"><span class="fw-bold">名稱：</span><?= htmlspecialchars($col['name']) ?></div>
                    </div>
                    <div class="card-action-bar px-3 pb-3">
                        <a href="edit.php?id=<?= $col['id'] ?>" class="btn btn-back btn-sm">編輯</a>
                        <a href="list.php?delete=<?= $col['id'] ?>" class="btn btn-back btn-sm btn-danger" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>