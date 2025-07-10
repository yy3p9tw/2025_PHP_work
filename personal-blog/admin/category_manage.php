<?php
// 管理端：分類管理
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// 新增分類
if (isset($_POST['add_name'])) {
    $name = trim($_POST['add_name']);
    if ($name) {
        if (create_category($name)) {
            $success = '新增成功';
        } else {
            $error = '新增失敗';
        }
    } else {
        $error = '請輸入分類名稱';
    }
}

// 編輯分類
if (isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $name = trim($_POST['edit_name']);
    if ($name) {
        if (update_category($id, $name)) {
            $success = '更新成功';
        } else {
            $error = '更新失敗';
        }
    } else {
        $error = '請輸入分類名稱';
    }
}

// 刪除分類
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    if (delete_category($id)) {
        $success = '刪除成功';
    } else {
        $error = '刪除失敗';
    }
}

$categories = get_all_categories();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>分類管理 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">分類管理</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3 mb-4">
        <div class="col-auto">
            <input type="text" name="add_name" class="form-control" placeholder="新增分類名稱">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">新增分類</button>
        </div>
        <div class="col-auto">
            <a href="dashboard.php" class="btn btn-secondary">返回</a>
        </div>
    </form>
    <table class="table table-bordered bg-white">
        <thead>
            <tr><th>分類名稱</th><th style="width:180px">操作</th></tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <form method="post" class="row g-2 align-items-center">
                    <td>
                        <input type="hidden" name="edit_id" value="<?= $cat['id'] ?>">
                        <input type="text" name="edit_name" class="form-control" value="<?= htmlspecialchars($cat['name']) ?>">
                    </td>
                    <td>
                        <button type="submit" class="btn btn-success btn-sm">儲存</button>
                        <button type="submit" name="delete_id" value="<?= $cat['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('確定刪除?')">刪除</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
