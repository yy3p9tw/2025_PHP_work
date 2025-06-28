<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
require_once '../includes/csrf_functions.php';

$pdo = get_pdo();

// 從 Session 中獲取並清除訊息
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// 確保 CSRF Token 存在於 session 中
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

try {
    $stmt = $pdo->query('SELECT * FROM colors ORDER BY name ASC');
    $colors = $stmt->fetchAll();
} catch (PDOException $e) {
    die("無法讀取顏色資料: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 顏色管理</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php
$current_page = 'colors';
require_once 'admin_nav.php';
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>顏色管理</h1>
        <a href="add_color.php" class="btn btn-success">新增顏色</a>
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
            <?php foreach ($colors as $color): ?>
            <tr>
                <td><?php echo htmlspecialchars($color['id']); ?></td>
                <td><?php echo htmlspecialchars($color['name']); ?></td>
                <td><?php echo htmlspecialchars($color['created_at']); ?></td>
                <td>
                    <a href="edit_color.php?id=<?php echo $color['id']; ?>" class="btn">編輯</a>
                    <button type="button" class="btn btn-danger" onclick="deleteColor(<?php echo $color['id']; ?>, '<?php echo htmlspecialchars($csrf_token); ?>')">刪除</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function deleteColor(colorId, csrfToken) {
    if (confirm('確定要刪除此顏色嗎？這將會影響到相關商品規格！')) {
        const formData = new FormData();
        formData.append('id', colorId);
        formData.append('csrf_token', csrfToken);

        fetch('delete_color.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // 這裡可以根據 delete_color.php 返回的內容來判斷是否成功
            // 目前 delete_color.php 會直接跳轉，所以這裡不會執行到
            // 如果未來 delete_color.php 返回 JSON，這裡可以處理
            window.location.reload(); // 重新載入頁面以顯示最新狀態和訊息
        })
        .catch(error => {
            console.error('Error:', error);
            alert('刪除失敗，請檢查控制台。');
        });
    }
}
</script>

</body>
</html>
