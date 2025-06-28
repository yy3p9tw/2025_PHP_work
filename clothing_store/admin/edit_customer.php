<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'auth_check.php';
require_once '../includes/db.php';
require_once '../includes/csrf_functions.php';
$pdo = get_pdo();

$customer_id = $_GET['id'] ?? null;
if (!$customer_id) {
    header('Location: customers.php');
    exit;
}

// 在這裡獲取 CSRF Token，確保在表單顯示前生成
$csrf_token = get_csrf_token();

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('無效的請求，請重試。');
    }
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $notes = $_POST['notes'] ?? '';

    try {
        $stmt = $pdo->prepare('UPDATE customers SET name = ?, phone = ?, email = ?, address = ?, notes = ? WHERE id = ?');
        $stmt->execute([$name, $phone, $email, $address, $notes, $customer_id]);
        header('Location: customers.php');
        exit;
    } catch (PDOException $e) {
        die("更新顧客資料失敗: " . $e->getMessage());
    }
}

// 獲取顧客現有資料
try {
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();
    if (!$customer) {
        die('找不到該顧客');
    }
} catch (PDOException $e) {
    die("無法讀取顧客資料: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 編輯顧客</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <nav class="admin-navbar">
        <a href="index.php" class="admin-nav-item">商品管理</a>
        <a href="orders.php" class="admin-nav-item">訂單管理</a>
        <a href="customers.php" class="admin-nav-item selected">顧客管理</a>
    </nav>
    <div class="container">
        <a href="customers.php" class="btn">&larr; 回到顧客列表</a>
        <h1>編輯顧客 #<?php echo htmlspecialchars($customer['id']); ?></h1>
        <form action="edit_customer.php?id=<?php echo $customer['id']; ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="form-group">
                <label for="name">姓名</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">電話</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>">
            </div>
            <div class="form-group">
                <label for="address">地址</label>
                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($customer['address']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="notes">備註</label>
                <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($customer['notes']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">更新顧客資料</button>
        </form>
    </div>
</body>
</html>