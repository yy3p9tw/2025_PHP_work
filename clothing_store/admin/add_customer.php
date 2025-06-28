<?php
require_once 'auth_check.php';
require_once '../includes/db.php';
require_once '../includes/csrf_functions.php';

$error = '';
$success = '';

// 生成 CSRF token
$csrf_token = get_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('無效的請求，請重試。');
    }

    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';

    if (empty($name) || empty($phone)) {
        $error = '姓名和電話是必填欄位。';
    } else {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $phone, $email, $address]);
            header('Location: customers.php?success=add');
            exit;
        } catch (PDOException $e) {
            $error = '新增顧客失敗: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>後台管理 - 新增顧客</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <div class="container">
        <h1>新增顧客</h1>

        <?php if ($error): ?><p class="error-message"><?php echo $error; ?></p><?php endif; ?>

        <form action="add_customer.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group">
                <label for="name">姓名 <span class="required">*</span></label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="phone">電話 <span class="required">*</span></label>
                <input type="text" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>

            <div class="form-group">
                <label for="address">地址</label>
                <textarea id="address" name="address" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-success">儲存</button>
            <a href="customers.php" class="btn">取消</a>
        </form>
    </div>
</body>
</html>
