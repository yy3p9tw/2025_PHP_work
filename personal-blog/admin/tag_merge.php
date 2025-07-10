<?php
// 管理端：標籤合併
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$tags = get_all_tags();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_id = intval($_POST['from_id'] ?? 0);
    $to_id = intval($_POST['to_id'] ?? 0);
    if ($from_id && $to_id && $from_id != $to_id) {
        // 將 from_id 的文章標籤全部轉移到 to_id
        db()->prepare("UPDATE IGNORE posts_tags SET tag_id=? WHERE tag_id=?")->execute([$to_id, $from_id]);
        db()->prepare("DELETE FROM tags WHERE id=?")->execute([$from_id]);
        log_action($_SESSION['user']['id'], 'merge_tag', '合併標籤 from:' . $from_id . ' to:' . $to_id);
        $success = '標籤合併完成';
    } else {
        $error = '請選擇不同的標籤進行合併';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>標籤合併 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">標籤合併</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-5">
            <label class="form-label">來源標籤（將被合併並刪除）</label>
            <select name="from_id" class="form-select" required>
                <option value="">請選擇</option>
                <?php foreach ($tags as $tag): ?>
                    <option value="<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label">目標標籤（合併到此）</label>
            <select name="to_id" class="form-select" required>
                <option value="">請選擇</option>
                <?php foreach ($tags as $tag): ?>
                    <option value="<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">合併</button>
        </div>
    </form>
    <a href="tag_manage.php" class="btn btn-secondary">返回標籤管理</a>
</div>
</body>
</html>
