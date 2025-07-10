<?php
// 管理端：文章管理列表
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 標籤篩選
$tag = trim($_GET['tag'] ?? '');
$tag_id = 0;
if ($tag !== '') {
    $tag_row = get_tag_by_name($tag);
    if ($tag_row) $tag_id = $tag_row['id'];
}

// 搜尋功能
$q = trim($_GET['q'] ?? '');
$where = '';
$params = [];
if ($q !== '') {
    $where .= ($where ? ' AND ' : 'WHERE ') . "p.title LIKE ?";
    $params[] = "%$q%";
}
if ($tag_id) {
    $where .= ($where ? ' AND ' : 'WHERE ') . "EXISTS (SELECT 1 FROM posts_tags pt WHERE pt.post_id=p.id AND pt.tag_id=?)";
    $params[] = $tag_id;
}
if ($where) {
    $sql = "SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id=c.id $where ORDER BY p.created_at DESC";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $posts = get_all_posts();
}
$all_tags = get_all_tags();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>文章管理 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">文章管理</h2>
    <form class="mb-3" method="get">
        <div class="input-group mb-2">
            <input type="text" name="q" class="form-control" placeholder="搜尋文章標題..." value="<?= htmlspecialchars($q) ?>">
            <?php if($tag): ?><input type="hidden" name="tag" value="<?= htmlspecialchars($tag) ?>"><?php endif; ?>
            <button class="btn btn-outline-secondary" type="submit">搜尋</button>
        </div>
        <div>
            <?php foreach($all_tags as $t): ?>
                <a href="?tag=<?=urlencode($t['name'])?><?=($q!==''?'&q='.urlencode($q):'')?>" class="badge bg-secondary text-light mb-1 <?=($tag==$t['name']?'fw-bold':'')?>">#<?=htmlspecialchars($t['name'])?></a>
            <?php endforeach; ?>
            <?php if($tag): ?>
                <a href="?<?=($q!==''?'q='.urlencode($q):'')?>" class="ms-2 text-danger">清除標籤</a>
            <?php endif; ?>
        </div>
    </form>
    <div class="mb-3">
        <a href="post_create.php" class="btn btn-primary">新增文章</a>
        <a href="category_manage.php" class="btn btn-secondary">分類管理</a>
        <a href="tag_manage.php" class="btn btn-secondary">標籤管理</a>
        <a href="user_manage.php" class="btn btn-secondary">管理者管理</a>
        <a href="profile.php" class="btn btn-secondary">個人資料</a>
        <a href="activity_log.php" class="btn btn-secondary">操作日誌</a>
        <a href="backup.php" class="btn btn-warning">資料庫備份</a>
        <?php if ($_SESSION['user']['username'] === 'admin'): ?>
        <a href="restore.php" class="btn btn-danger">資料庫還原</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-outline-danger float-end">登出</a>
    </div>
    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>標題</th>
                <th>分類</th>
                <th>標籤</th>
                <th>建立時間</th>
                <th style="width:180px">操作</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($posts as $post): ?>
            <tr>
                <td><?= htmlspecialchars($post['title']) ?></td>
                <td><?= htmlspecialchars($post['category_name']) ?></td>
                <td>
                  <?php $tags = get_post_tags($post['id']); foreach($tags as $t): ?>
                    <a href="?tag=<?=urlencode($t['name'])?><?=($q!==''?'&q='.urlencode($q):'')?>" class="badge bg-secondary text-light me-1 <?=($tag==$t['name']?'fw-bold':'')?>">#<?=htmlspecialchars($t['name'])?></a>
                  <?php endforeach; ?>
                </td>
                <td><?= htmlspecialchars($post['created_at']) ?></td>
                <td>
                    <a href="post_edit.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-success">編輯</a>
                    <a href="post_delete.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確定刪除?')">刪除</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
