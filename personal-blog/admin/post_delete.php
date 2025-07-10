<?php
// 管理端：刪除文章
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
$post = get_post($id);
if (!$post) {
    die('找不到文章');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        if (delete_post($id)) {
            log_action($_SESSION['user']['id'], 'delete_post', '刪除文章：' . $post['title']);
            header('Location: dashboard.php?msg=deleted');
            exit;
        } else {
            $error = '刪除失敗，請重試';
        }
    } else {
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>刪除文章 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-danger">確定要刪除這篇文章嗎？</h2>
    <div class="alert alert-warning">
        <strong>標題：</strong> <?= htmlspecialchars($post['title']) ?><br>
        <strong>分類：</strong> <?= htmlspecialchars($post['category_name']) ?><br>
        <strong>建立時間：</strong> <?= htmlspecialchars($post['created_at']) ?><br>
    </div>
    <form method="post">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" class="btn btn-danger">確定刪除</button>
        <a href="dashboard.php" class="btn btn-secondary">取消</a>
    </form>
</div>
</body>
</html>
