<?php
// 管理端：資料庫還原（僅限超級管理員，需手動貼上 SQL）
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['username'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = $_POST['sql'] ?? '';
    if ($sql) {
        try {
            db()->exec($sql);
            $success = '還原成功';
        } catch (Exception $e) {
            $error = '還原失敗：' . $e->getMessage();
        }
    } else {
        $error = '請貼上 SQL 指令';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>資料庫還原 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">資料庫還原（僅限 admin）</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">貼上 SQL 指令（請謹慎操作，會覆蓋現有資料）</label>
            <textarea name="sql" class="form-control" rows="10" required><?= htmlspecialchars($_POST['sql'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-danger">執行還原</button>
        <a href="dashboard.php" class="btn btn-secondary">返回</a>
    </form>
    <div class="mt-4 text-muted">僅限帳號 admin 可用，請務必備份後再執行。</div>
</div>
</body>
</html>
