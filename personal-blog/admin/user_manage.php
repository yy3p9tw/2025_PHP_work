<?php
// 管理端：使用者管理
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// 新增管理者
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt->execute([$username, $hash])) {
            log_action($_SESSION['user']['id'], 'add_user', '新增管理者：' . $username);
            $success = '新增成功';
        } else {
            $error = '新增失敗，帳號可能重複';
        }
    } else {
        $error = '請輸入帳號與密碼';
    }
}

// 刪除管理者
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    if ($id == $_SESSION['user']['id']) {
        $error = '無法刪除自己';
    } else {
        $stmt = db()->prepare("DELETE FROM users WHERE id=?");
        if ($stmt->execute([$id])) {
            log_action($_SESSION['user']['id'], 'delete_user', '刪除管理者ID：' . $id);
            $success = '刪除成功';
        } else {
            $error = '刪除失敗';
        }
    }
}

$users = db()->query("SELECT * FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>管理者帳號管理 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">管理者帳號管理</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3 mb-4">
        <div class="col-auto">
            <input type="text" name="username" class="form-control" placeholder="帳號">
        </div>
        <div class="col-auto">
            <input type="password" name="password" class="form-control" placeholder="密碼">
        </div>
        <div class="col-auto">
            <button type="submit" name="add_user" class="btn btn-primary">新增管理者</button>
        </div>
        <div class="col-auto">
            <a href="dashboard.php" class="btn btn-secondary">返回</a>
        </div>
    </form>
    <table class="table table-bordered bg-white">
        <thead>
            <tr><th>帳號</th><th style="width:120px">操作</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td>
                    <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('確定刪除?')">刪除</button>
                    </form>
                    <?php else: ?>
                        <span class="text-muted">目前登入</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
