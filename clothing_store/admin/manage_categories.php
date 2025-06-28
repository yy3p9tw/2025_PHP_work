<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
require_once '../includes/csrf_functions.php';

$pdo = get_pdo();
$message = '';
$error = '';

// 確保 CSRF Token 存在於 session 中
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

try {
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    die("無法讀取分類資料: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 分類管理</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php
$current_page = 'categories';
require_once 'admin_nav.php';
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>分類管理</h1>
        <a href="add_category.php" class="btn btn-success">新增分類</a>
    </div>

    <?php if ($message): ?><p class="success-message"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error-message"><?php echo $error; ?></p><?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>名稱</th>
                <th>建立時間</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td><?php echo htmlspecialchars($category['id']); ?></td>
                <td><?php echo htmlspecialchars($category['name']); ?></td>
                <td><?php echo htmlspecialchars($category['created_at']); ?></td>
                <td>
                    <a href="edit_category.php?id=<?php echo $category['id']; ?>" class="btn">編輯</a>
                    <form action="delete_category.php" method="post" style="display:inline-block;" onsubmit="return confirm('確定要刪除此分類嗎？這將會影響到相關商品！');">
                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <button type="submit" class="btn btn-danger">刪除</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>