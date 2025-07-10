<?php
// 管理端：編輯文章
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
$categories = get_all_categories();
$all_tags = get_all_tags();
$post_tags = array_column(get_post_tags($id), 'name');
$error = '';

// 搜尋功能（僅顯示於標題上方，實際搜尋在 dashboard）
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>編輯文章 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <form class="mb-4" method="get" action="../admin/dashboard.php">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="搜尋文章標題...">
            <button class="btn btn-outline-secondary" type="submit">搜尋</button>
        </div>
    </form>
    <h2 class="mb-4">編輯文章</h2>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $image_path = $post['image'];
    $tags = isset($_POST['tags']) ? (array)$_POST['tags'] : [];
    $summary = trim($_POST['summary'] ?? '');
    $is_featured = !empty($_POST['is_featured']) ? 1 : 0;

    // 處理圖片上傳
    if (!empty($_FILES['image']['name'])) {
        $target_dir = '../uploads/';
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (!in_array($ext, $allowed)) {
            $error = '僅允許上傳 jpg, jpeg, png, gif 圖片';
        } else if ($_FILES['image']['size'] > 2*1024*1024) {
            $error = '圖片大小不可超過 2MB';
        } else {
            $filename = uniqid('img_', true) . '.' . $ext;
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = 'uploads/' . $filename;
            } else {
                $error = '圖片上傳失敗';
            }
        }
    }

    if (!$error && $title && $content && $category_id) {
        if (update_post($id, $title, $content, $category_id, $image_path, $summary, $is_featured)) {
            set_post_tags($id, $tags);
            log_action($_SESSION['user']['id'], 'edit_post', '編輯文章：' . $title);
            header('Location: dashboard.php?msg=updated');
            exit;
        } else {
            $error = '更新失敗，請重試';
        }
    } elseif (!$error) {
        $error = '請填寫完整資料';
    }
}
?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">標題</label>
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? $post['title']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">分類</label>
            <select name="category_id" class="form-select" required>
                <option value="">請選擇</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ((isset($_POST['category_id']) ? $_POST['category_id'] : $post['category_id'])==$cat['id'])?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">標籤（可多選）</label>
            <select name="tags[]" class="form-select" multiple size="4">
                <?php foreach ($all_tags as $tag): ?>
                    <option value="<?= htmlspecialchars($tag['name']) ?>" <?= (in_array($tag['name'], $_POST['tags'] ?? $post_tags))?'selected':'' ?>><?= htmlspecialchars($tag['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">可按 Ctrl/Command 多選，或自行輸入新標籤（請用半形逗號分隔）</div>
            <input type="text" name="tags_new" class="form-control mt-2" placeholder="新增標籤（多個以 , 分隔）" value="<?= htmlspecialchars($_POST['tags_new'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">內容</label>
            <textarea name="content" class="form-control" rows="8" required><?= htmlspecialchars($_POST['content'] ?? $post['content']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">目前圖片</label><br>
            <?php if ($post['image']): ?>
                <img src="../<?= htmlspecialchars($post['image']) ?>" style="max-width:200px;max-height:120px;">
            <?php else: ?>
                <span class="text-muted">無</span>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label">更換圖片 (選填, 2MB 內, jpg/png/gif)</label>
            <input type="file" name="image" accept="image/*" class="form-control">
        </div>
        <div class="mb-3">
            <label for="summary" class="form-label">文章摘要</label>
            <textarea name="summary" id="summary" class="form-control" rows="2"><?=htmlspecialchars($post['summary']??'')?></textarea>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" <?=!empty($post['is_featured'])?'checked':''?>>
            <label class="form-check-label" for="is_featured">精選文章</label>
        </div>
        <button type="submit" class="btn btn-primary">儲存變更</button>
        <a href="dashboard.php" class="btn btn-secondary">返回</a>
    </form>
</div>
</body>
</html>
