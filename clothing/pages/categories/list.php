<?php
require_once '../../includes/db.php';
$Category = new DB('categories');
$Item = new DB('items');

$error_message = '';

// 刪除分類
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    // 檢查是否有商品關聯到此分類
    $related_items = $Item->all(['category_id' => $id_to_delete]);

    if (!empty($related_items)) {
        $error_message = '分類已被商品使用，無法刪除。';
    } else {
        $Category->delete($id_to_delete);
        header('Location: list.php');
        exit;
    }
}

$categories = $Category->all();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>分類列表</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .main-title { color: #d2691e; text-align: center; margin-top: 2em; }
        .card { box-shadow: 0 2px 16px #ffb34733; }
        .btn-back { background: #ffb347; color: #fff; border: 1px solid #ffb347; }
        .btn-back:hover { background: #ffa500; color: #fff; }
        .card-action-bar { margin-top:0.7em; display:flex; gap:0.5em; flex-wrap:wrap; }
        @media (max-width: 700px) {
            .main-title { font-size: 1.1em; }
        }
    </style>
</head>
<body class="warm-bg">
    <div class="container py-4">
        <h1 class="main-title">分類列表</h1>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="../../index.php" class="btn btn-back btn-sm">返回首頁</a>
            <a href="add.php" class="btn btn-back btn-sm">＋ 新增分類</a>
        </div>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
        <?php if (!empty($categories)): ?>
            <?php foreach($categories as $c): ?>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-2"><?= htmlspecialchars($c['name']) ?></h5>
                        </div>
                        <div class="card-action-bar px-3 pb-3">
                            <a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-back btn-sm">編輯</a>
                            <a href="list.php?delete=<?= $c['id'] ?>" class="btn btn-back btn-sm btn-danger" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    目前沒有任何分類。
                </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>