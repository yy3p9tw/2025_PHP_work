<?php
// 管理端：新增文章
session_start();
require_once '../includes/db.php';

// 檢查登入
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$categories = get_all_categories();
$all_tags = get_all_tags();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $user_id = $_SESSION['user']['id'];
    $image_path = null; // 單張圖片上傳已移除，保留變數避免函式錯誤
    $tags = isset($_POST['tags']) ? (array)$_POST['tags'] : [];
    $tags_new = trim($_POST['tags_new'] ?? '');
    if ($tags_new) {
        foreach (explode(',', $tags_new) as $t) {
            $t = trim($t);
            if ($t !== '' && !in_array($t, $tags)) $tags[] = $t;
        }
    }

    // 單張圖片上傳功能已移除，改用 CKEditor 內文插圖

    $summary = trim($_POST['summary'] ?? '');
    $is_featured = !empty($_POST['is_featured']) ? 1 : 0;

    if (!$error && $title && $content && $category_id) {
        if (create_post($title, $content, $category_id, $image_path, $user_id, $summary, $is_featured)) {
            $post_id = db()->lastInsertId();
            set_post_tags($post_id, $tags);
            log_action($user_id, 'create_post', '新增文章：' . $title);
            header('Location: dashboard.php?msg=created');
            exit;
        } else {
            $error = '新增失敗，請重試';
        }
    } elseif (!$error) {
        $error = '請填寫完整資料';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>新增文章 | 管理後台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">新增文章</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">標題</label>
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">分類</label>
            <select name="category_id" class="form-select" required>
                <option value="">請選擇</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id']==$cat['id'])?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">標籤（可多選）</label>
            <select name="tags[]" class="form-select" multiple size="4">
                <?php foreach ($all_tags as $tag): ?>
                    <option value="<?= htmlspecialchars($tag['name']) ?>" <?= (isset($_POST['tags']) && in_array($tag['name'], $_POST['tags']))?'selected':'' ?>><?= htmlspecialchars($tag['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">可按 Ctrl/Command 多選，或自行輸入新標籤（請用半形逗號分隔）</div>
            <input type="text" name="tags_new" class="form-control mt-2" placeholder="新增標籤（多個以 , 分隔）" value="<?= htmlspecialchars($_POST['tags_new'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">內容</label>
            <textarea name="content" id="content" class="form-control" style="min-height:800px;font-size:1.15rem;" rows="32" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
            <div class="form-text">可直接插入圖片、表格、連結等（支援 CKEditor 富文本編輯器）</div>
        </div>
        <!-- 單張圖片上傳功能已移除，改用 CKEditor 內文插圖（如需恢復可隨時加回） -->
        <div class="mb-3">
            <label for="summary" class="form-label">文章摘要</label>
            <textarea name="summary" id="summary" class="form-control" rows="2"><?=htmlspecialchars($_POST['summary']??'')?></textarea>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" <?=!empty($_POST['is_featured'])?'checked':''?>>
            <label class="form-check-label" for="is_featured">精選文章</label>
        </div>
        <button type="submit" class="btn btn-primary">送出</button>
        <a href="dashboard.php" class="btn btn-secondary">返回</a>
    </form>
</div>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor.create(document.querySelector('#content'), {
            ckfinder: {
                // 若有自訂圖片上傳 API，可在這裡設定 uploadUrl
                // uploadUrl: '/admin/upload_image.php'
            },
            toolbar: {
                items: [
                    'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo'
                ]
            },
            language: 'zh',
            image: {
                toolbar: [
                    'imageTextAlternative', 'imageStyle:full', 'imageStyle:side'
                ]
            },
            table: {
                contentToolbar: [ 'tableColumn', 'tableRow', 'mergeTableCells' ]
            }
        }).catch(error => { console.error(error); });
    </script>
</body>
</html>
