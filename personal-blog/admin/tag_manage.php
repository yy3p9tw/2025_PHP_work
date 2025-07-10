<?php
// 管理端：標籤管理
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// 刪除標籤
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = db()->prepare("DELETE FROM tags WHERE id=?");
    if ($stmt->execute([$id])) {
        log_action($_SESSION['user']['id'], 'delete_tag', '刪除標籤ID：' . $id);
        $success = '刪除成功';
    } else {
        $error = '刪除失敗';
    }
}

$tags = get_all_tags();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>標籤管理 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">標籤管理</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <a href="dashboard.php" class="btn btn-secondary mb-3">返回文章管理</a>
    <a href="tag_merge.php" class="btn btn-primary mb-3 ms-2">標籤合併</a>
    <table class="table table-bordered bg-white">
        <thead>
            <tr><th>標籤名稱</th><th style="width:120px">操作</th></tr>
        </thead>
        <tbody>
        <?php foreach ($tags as $tag): ?>
            <tr>
                <td><?= htmlspecialchars($tag['name']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $tag['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('確定刪除?')">刪除</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
