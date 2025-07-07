<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/auth.php';

// 檢查是否已登入
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$user_id = $_SESSION['user_id'];

$error = '';
$success = '';

// 獲取用戶資料
$user = $db->fetchOne('SELECT * FROM users WHERE id = ?', [$user_id]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // 驗證 CSRF token
        if (!validateCSRFToken($_POST['csrf_token'])) {
            $error = 'Invalid request';
        } else {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            
            if (empty($username) || empty($email)) {
                $error = '請填寫所有必填欄位';
            } else {
                // 檢查用戶名是否已存在（排除自己）
                $existing_user = $db->fetchOne('SELECT id FROM users WHERE username = ? AND id != ?', [$username, $user_id]);
                if (!empty($existing_user)) {
                    $error = '用戶名已被使用';
                } else {
                    // 檢查郵箱是否已存在（排除自己）
                    $existing_email = $db->fetchOne('SELECT id FROM users WHERE email = ? AND id != ?', [$email, $user_id]);
                    if (!empty($existing_email)) {
                        $error = '郵箱已被使用';
                    } else {
                        // 更新用戶資料
                        $db->query('UPDATE users SET username = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?', 
                                  [$username, $email, $user_id]);
                        
                        // 更新 session
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $email;
                        
                        $success = '個人資料更新成功';
                        $user['username'] = $username;
                        $user['email'] = $email;
                    }
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // 驗證 CSRF token
        if (!validateCSRFToken($_POST['csrf_token'])) {
            $error = 'Invalid request';
        } else {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error = '請填寫所有密碼欄位';
            } elseif ($new_password !== $confirm_password) {
                $error = '新密碼確認不符';
            } elseif (strlen($new_password) < 6) {
                $error = '新密碼至少需要6個字符';
            } else {
                // 驗證當前密碼
                if (!password_verify($current_password, $user['password'])) {
                    $error = '當前密碼不正確';
                } else {
                    // 更新密碼
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $db->query('UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?', 
                              [$hashed_password, $user_id]);
                    
                    $success = '密碼更新成功';
                }
            }
        }
    }
}

$page_title = '個人資料';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="mb-4">個人資料</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- 基本資料 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">基本資料</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">用戶名</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">郵箱</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">帳戶類型</label>
                                    <input type="text" class="form-control" readonly
                                           value="<?php echo $user['role'] === 'admin' ? '管理員' : '一般用戶'; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">註冊時間</label>
                                    <input type="text" class="form-control" readonly
                                           value="<?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">更新資料</button>
                    </form>
                </div>
            </div>
            
            <!-- 變更密碼 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">變更密碼</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">當前密碼</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">新密碼</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <small class="form-text text-muted">至少6個字符</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">確認新密碼</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-warning">變更密碼</button>
                    </form>
                </div>
            </div>
            
            <!-- 帳戶統計 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">帳戶統計</h5>
                </div>
                <div class="card-body">
                    <?php
                    // 獲取統計資料
                    $portfolio_result = $db->fetchOne('SELECT COUNT(*) as count FROM portfolios WHERE user_id = ? AND quantity > 0', [$user_id]);
                    $portfolio_count = $portfolio_result['count'];
                    $watchlist_result = $db->fetchOne('SELECT COUNT(*) as count FROM watchlist WHERE user_id = ?', [$user_id]);
                    $watchlist_count = $watchlist_result['count'];
                    $transaction_result = $db->fetchOne('SELECT COUNT(*) as count FROM transactions WHERE user_id = ?', [$user_id]);
                    $transaction_count = $transaction_result['count'];
                    $last_login = $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : '無';
                    ?>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary"><?php echo $portfolio_count; ?></h4>
                                <small class="text-muted">持有股票數</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success"><?php echo $watchlist_count; ?></h4>
                                <small class="text-muted">關注股票數</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info"><?php echo $transaction_count; ?></h4>
                                <small class="text-muted">交易記錄數</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">上次登入</h6>
                                <small class="text-muted"><?php echo $last_login; ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
