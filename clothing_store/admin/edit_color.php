<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
require_once '../includes/csrf_functions.php';

$pdo = get_pdo();
$message = '';
$error = '';
$color = null;

// 生成 CSRF token
$csrf_token = get_csrf_token();

$color_id = $_GET['id'] ?? null;

if (!$color_id) {
    $error = '未指定顏色ID。';
} else {
    try {
        $stmt = $pdo->prepare('SELECT * FROM colors WHERE id = ?');
        $stmt->execute([$color_id]);
        $color = $stmt->fetch();

        if (!$color) {
            $error = '找不到該顏色。';
        }
    } catch (PDOException $e) {
        $error = '資料庫錯誤：' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $color) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = '無效的請求，請重試。';
    } else {
        $new_color_name = trim($_POST['name'] ?? '');

        if (empty($new_color_name)) {
            $error = '顏色名稱不可為空。';
        } else if ($new_color_name === $color['name']) {
            $message = '顏色名稱未改變。';
        } else {
            try {
                // 檢查新名稱是否已存在
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM colors WHERE name = ? AND id != ?');
                $stmt->execute([$new_color_name, $color_id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = '此顏色名稱已存在。';
                } else {
                    $stmt = $pdo->prepare('UPDATE colors SET name = ? WHERE id = ?');
                    $stmt->execute([$new_color_name, $color_id]);
                    $message = '顏色更新成功！';
                    // 更新 $color 變數以顯示最新名稱
                    $color['name'] = $new_color_name;
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
    <title>後台管理 - 編輯顏色</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php
$current_page = 'colors';
require_once 'admin_nav.php';
?>

<div class="container">
    <h1>編輯顏色</h1>

    <?php if ($message): ?><p class="success-message"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error-message"><?php echo $error; ?></p><?php endif; ?>

    <?php if ($color): ?>
        <form action="edit_color.php?id=<?php echo htmlspecialchars($color['id']); ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group">
                <label for="name">顏色名稱:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($color['name']); ?>" required>
            </div>

            <button type="submit" class="btn btn-success">更新顏色</button>
            <a href="manage_colors.php" class="btn">取消</a>
        </form>
    <?php else: ?>
        <p>無法載入顏色資訊。</p>
        <a href="manage_colors.php" class="btn">返回顏色管理</a>
    <?php endif; ?>
</div>

</body>
</html>