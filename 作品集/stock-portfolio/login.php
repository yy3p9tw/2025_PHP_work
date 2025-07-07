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
    if (isset($_POST['login'])) {
        // 驗證 CSRF token
        if (!validateCSRFToken($_POST['csrf_token'])) {
            $error = 'Invalid request';
        } else {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            if (empty($username) || empty($password)) {
                $error = '請輸入帳號和密碼';
            } else {
                $user = authenticateUser($username, $password);
                if ($user) {
                    loginUser($user);
                    // 登入成功，重定向到首頁
                    header('Location: index.php');
                    exit();
                } else {
                    $error = '帳號或密碼錯誤';
                }
            }
        }
    }
}

$page_title = '登入';
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">登入</h3>
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
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">密碼</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="login" class="btn btn-primary">登入</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>還沒有帳號？<a href="register.php">立即註冊</a></p>
                    </div>
                    
                    <div class="mt-4">
                        <h5>測試帳號</h5>
                        <div class="row">
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>管理員</h6>
                                        <p class="small mb-1">帳號：admin</p>
                                        <p class="small mb-0">密碼：admin123</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>一般用戶</h6>
                                        <p class="small mb-1">帳號：demo</p>
                                        <p class="small mb-0">密碼：demo123</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
