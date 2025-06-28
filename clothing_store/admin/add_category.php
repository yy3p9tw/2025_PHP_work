<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
require_once '../includes/csrf_functions.php';

$pdo = get_pdo();
$message = '';
$error = '';

// 生成 CSRF token
$csrf_token = get_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('無效的請求，請重試。');
    }

    $category_name = trim($_POST['category_name'] ?? '');

    if (empty($category_name)) {
        $error = '分類名稱不可為空。';
    } else {
        try {
            // 檢查分類是否已存在
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = ?');
            $stmt->execute([$category_name]);
            if ($stmt->fetchColumn() > 0) {
                $error = '此分類名稱已存在。';
            } else {
                $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
                $stmt->execute([$category_name]);
                $message = '分類新增成功！';
            }
        } catch (PDOException $e) {
            $error = '資料庫錯誤：' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 新增分類</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php
$current_page = 'categories';
require_once 'admin_nav.php';
?>

<div class="container">
    <h1>新增分類</h1>

    <?php if ($message): ?><p class="success-message"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error-message"><?php echo $error; ?></p><?php endif; ?>

    <form action="add_category.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-group">
            <label for="category_name">分類名稱:</label>
            <input type="text" id="category_name" name="category_name" required>
        </div>

        <button type="submit" class="btn btn-success">新增分類</button>
        <a href="manage_categories.php" class="btn">取消</a>
    </form>
</div>

</body>
</html>