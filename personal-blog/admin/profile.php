<?php
// 管理端：個人資料與密碼變更
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$user = $_SESSION['user'];

// 密碼變更
if (isset($_POST['change_pw'])) {
    $old = $_POST['old_pw'] ?? '';
    $new = $_POST['new_pw'] ?? '';
    $new2 = $_POST['new_pw2'] ?? '';
    if (!$old || !$new || !$new2) {
        $error = '請填寫所有欄位';
    } elseif ($new !== $new2) {
        $error = '新密碼兩次輸入不一致';
    } else {
        $stmt = db()->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !password_verify($old, $row['password'])) {
            $error = '舊密碼錯誤';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            db()->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $user['id']]);
            log_action($user['id'], 'change_password', '變更密碼');
            $success = '密碼已更新';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>個人資料 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">個人資料與密碼變更</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <div class="mb-4">
        <strong>帳號：</strong> <?= htmlspecialchars($user['username']) ?>
    </div>
    <form method="post" class="bg-white p-4 rounded shadow-sm" style="max-width:400px;">
        <h5 class="mb-3">變更密碼</h5>
        <div class="mb-3">
            <label class="form-label">舊密碼</label>
            <input type="password" name="old_pw" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">新密碼</label>
            <input type="password" name="new_pw" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">新密碼（再輸入一次）</label>
            <input type="password" name="new_pw2" class="form-control" required>
        </div>
        <button type="submit" name="change_pw" class="btn btn-primary">儲存變更</button>
        <a href="dashboard.php" class="btn btn-secondary">返回</a>
    </form>
</div>
</body>
</html>
