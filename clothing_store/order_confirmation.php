<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? '訂單確認'; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/cart_functions.php';
require_once 'includes/csrf_functions.php';

$page_title = '訂單確認';

$order_id = $_GET['order_id'] ?? null;

?>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">衣櫥小舖</a>
            <div class="nav-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="nav-item">歡迎, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="cart.php" class="nav-item">購物車</a>
                    <a href="logout.php" class="nav-item">登出</a>
                <?php else: ?>
                    <a href="login.php" class="nav-item">登入</a>
                    <a href="register.php" class="nav-item">註冊</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="main-content">
        <div class="order-confirmation-container">
            <h1>訂單已成功送出！</h1>
            <p>感謝您的購買！您的訂單已成功建立。</p>
            <?php if ($order_id): ?>
                <p>您的訂單編號是: <strong>#<?php echo htmlspecialchars($order_id); ?></strong></p>
            <?php endif; ?>
            <p>我們會盡快處理您的訂單。</p>
            <div class="actions">
                <a href="index.php" class="btn btn-primary">繼續購物</a>
                <!-- 未來可以增加查看訂單詳情的連結 -->
            </div>
        </div>
    </div>
<?php require_once 'includes/footer.php'; ?>
</body>
</html>