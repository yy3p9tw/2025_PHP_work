<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/auth.php';

// 如果已登入，重定向到首頁
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // 驗證 CSRF token
        if (!validateCSRFToken($_POST['csrf_token'])) {
            $error = 'Invalid request';
        } else {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = '請填寫所有必填欄位';
            } elseif ($password !== $confirm_password) {
                $error = '密碼確認不符';
            } elseif (strlen($password) < 6) {
                $error = '密碼至少需要6個字符';
            } else {
                if (register_user($username, $email, $password)) {
                    $success = '註冊成功！請登入';
                } else {
                    $error = '註冊失敗，帳號或信箱可能已被使用';
                }
            }
        }
    }
}

$page_title = '註冊';
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">註冊</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">帳號</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">信箱</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">密碼</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="form-text text-muted">密碼至少需要6個字符</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">確認密碼</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="register" class="btn btn-primary">註冊</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>已有帳號？<a href="login.php">立即登入</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
