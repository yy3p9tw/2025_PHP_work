<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
require_once '../includes/csrf_functions.php';

$pdo = get_pdo();
$message = '';
$error = '';
$category = null;

// 生成 CSRF token
$csrf_token = get_csrf_token();

$category_id = $_GET['id'] ?? null;

if (!$category_id) {
    $error = '未指定分類ID。';
} else {
    try {
        $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$category_id]);
        $category = $stmt->fetch();

        if (!$category) {
            $error = '找不到該分類。';
        }
    } catch (PDOException $e) {
        $error = '資料庫錯誤：' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $category) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = '無效的請求，請重試。';
    } else {
        $new_category_name = trim($_POST['name'] ?? '');

        if (empty($new_category_name)) {
            $error = '分類名稱不可為空。';
        } else if ($new_category_name === $category['name']) {
            $message = '分類名稱未改變。';
        } else {
            try {
                // 檢查新名稱是否已存在
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?');
                $stmt->execute([$new_category_name, $category_id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = '此分類名稱已存在。';
                } else {
                    $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
                    $stmt->execute([$new_category_name, $category_id]);
                    $message = '分類更新成功！';
                    // 更新 $category 變數以顯示最新名稱
                    $category['name'] = $new_category_name;
                }
            } catch (PDOException $e) {
                $error = '資料庫錯誤：' . $e->getMessage();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 編輯分類</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php
$current_page = 'categories';
require_once 'admin_nav.php';
?>

<div class="container">
    <h1>編輯分類</h1>

    <?php if ($message): ?><p class="success-message"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error-message"><?php echo $error; ?></p><?php endif; ?>

    <?php if ($category): ?>
        <form action="edit_category.php?id=<?php echo htmlspecialchars($category['id']); ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group">
                <label for="name">分類名稱:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
            </div>

            <button type="submit" class="btn btn-success">更新分類</button>
            <a href="manage_categories.php" class="btn">取消</a>
        </form>
    <?php else: ?>
        <p>無法載入分類資訊。</p>
        <a href="manage_categories.php" class="btn">返回分類管理</a>
    <?php endif; ?>
</div>

</body>
</html>