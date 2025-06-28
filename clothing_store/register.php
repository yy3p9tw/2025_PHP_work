<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? '註冊'; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/csrf_functions.php';

$error = '';
$success = '';

$csrf_token = get_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = '無效的請求，請重試。';
    } else {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = '使用者名稱和密碼皆不可為空';
    } else {
        $pdo = get_pdo();
        // 檢查使用者名稱是否已存在
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = '此使用者名稱已被註冊';
        } else {
            // 新增使用者
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            if ($stmt->execute([$username, $hashed_password, 'customer'])) {
                $success = '註冊成功！請前往登入頁面。';
            } else {
                $error = '註冊失敗，請稍後再試';
            }
        }
    }
    }
}

$page_title = '註冊';
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
        <div class="auth-container">
            <form action="register.php" method="post">
                <h2>註冊新帳號</h2>
                <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
                <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
                <div class="form-group">
                    <label for="username">使用者名稱</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">密碼</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <button type="submit" class="btn-auth">註冊</button>
                <p class="switch-auth">已經有帳號了? <a href="login.php">點此登入</a></p>
            </form>
        </div>
    </div>
<?php require_once 'includes/footer.php'; ?>
</body>
</html>