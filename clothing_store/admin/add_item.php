<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
require_once '../includes/csrf_functions.php';
$pdo = get_pdo();

// 在這裡獲取 CSRF Token，確保在表單顯示前生成
$csrf_token = get_csrf_token();

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('無效的請求，請重試。');
    }
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $image_name = '';

    // 處理圖片上傳
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/';
        $image_name = uniqid('img_') . '_' . basename($_FILES['image']['name']);
        $upload_file = $upload_dir . $image_name;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
            die('圖片上傳失敗');
        }
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO items (name, description, category_id, image) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $description, $category_id, $image_name]);
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        die("新增商品失敗: " . $e->getMessage());
    }
}

// 獲取所有分類用於下拉選單
try {
    $categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
} catch (PDOException $e) {
    die("無法讀取分類資料: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 新增商品</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <div class="container">
        <h1>新增商品</h1>
        <form action="add_item.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="name">商品名稱</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="category_id">分類</label>
                <select id="category_id" name="category_id">
                    <option value="">-- 請選擇 --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">描述</label>
                <textarea id="description" name="description" rows="5"></textarea>
            </div>
            <div class="form-group">
                <label for="image">商品圖片</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-success">儲存</button>
            <a href="index.php" class="btn">取消</a>
        </form>
    </div>
</body>
</html>