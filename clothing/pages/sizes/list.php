<?php
require_once '../../includes/db.php';
$Size = new DB('sizes');
$sizes = $Size->all();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>尺寸列表</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="warm-bg">
    <div class="container py-4">
        <h1 class="main-title mb-4">尺寸列表</h1>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="../../index.php" class="btn btn-outline-secondary">返回首頁</a>
            <a href="add.php" class="btn btn-primary">＋ 新增尺寸</a>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>尺寸名稱</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sizes)): ?>
                                <?php foreach ($sizes as $size): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($size['id']) ?></td>
                                        <td><?= htmlspecialchars($size['name']) ?></td>
                                        <td>
                                            <a href="edit.php?id=<?= $size['id'] ?>" class="btn btn-sm btn-outline-primary">編輯</a>
                                            <a href="delete.php?id=<?= $size['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('確定要刪除嗎？')">刪除</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center">目前沒有任何尺寸。</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>