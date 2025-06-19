<?php
// 設定頁面編碼
header('Content-Type: application/json; charset=utf-8');

// 允許跨域請求（開發環境使用）
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// 開始 Session
session_start();

// 資料庫連線設定（請根據實際情況修改）
$host = 'localhost';
$dbname = 'member_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // 資料庫連線失敗時使用模擬資料
    $db_connected = false;
}

// 回應函數
function sendResponse($success, $message, $data = null) {
    $response = array(
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    );
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// 驗證輸入資料
function validateInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, '僅支援 POST 請求');
}

// 取得 POST 資料
$input_username = isset($_POST['username']) ? validateInput($_POST['username']) : '';
$input_password = isset($_POST['password']) ? validateInput($_POST['password']) : '';
$action = isset($_POST['action']) ? validateInput($_POST['action']) : 'login';

// 基本驗證
if (empty($input_username) || empty($input_password)) {
    sendResponse(false, '請輸入帳號和密碼');
}

// 帳號密碼長度驗證
if (strlen($input_username) < 3 || strlen($input_username) > 20) {
    sendResponse(false, '帳號長度必須在 3-20 個字元之間');
}

if (strlen($input_password) < 6 || strlen($input_password) > 50) {
    sendResponse(false, '密碼長度必須在 6-50 個字元之間');
}

// 登入失敗次數限制（使用 Session 儲存）
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// 如果失敗次數超過 5 次，需要等待 5 分鐘
if ($_SESSION['login_attempts'] >= 5) {
    $wait_time = 300; // 5 分鐘
    $time_diff = time() - $_SESSION['last_attempt_time'];
    
    if ($time_diff < $wait_time) {
        $remaining_time = $wait_time - $time_diff;
        sendResponse(false, "登入失敗次數過多，請等待 {$remaining_time} 秒後再試");
    } else {
        // 重置失敗次數
        $_SESSION['login_attempts'] = 0;
    }
}

// 模擬登入驗證（如果沒有資料庫連線）
if (!isset($db_connected) || !$db_connected) {
    // 預設測試帳號
    $test_accounts = array(
        'admin' => array(
            'password' => '123456',
            'role' => 'admin',
            'name' => '系統管理員',
            'email' => 'admin@example.com'
        ),
        'user' => array(
            'password' => 'password',
            'role' => 'user',
            'name' => '一般用戶',
            'email' => 'user@example.com'
        ),
        'demo' => array(
            'password' => 'demo123',
            'role' => 'demo',
            'name' => '演示帳號',
            'email' => 'demo@example.com'
        )
    );
    
    // 驗證帳號密碼
    if (isset($test_accounts[$input_username]) && 
        $test_accounts[$input_username]['password'] === $input_password) {
        
        // 登入成功，重置失敗次數
        $_SESSION['login_attempts'] = 0;
        
        // 設定 Session
        $_SESSION['user_id'] = $input_username;
        $_SESSION['username'] = $input_username;
        $_SESSION['user_role'] = $test_accounts[$input_username]['role'];
        $_SESSION['user_name'] = $test_accounts[$input_username]['name'];
        $_SESSION['login_time'] = time();
        $_SESSION['is_logged_in'] = true;
        
        // 記錄登入日誌
        $log_data = array(
            'username' => $input_username,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'login_time' => date('Y-m-d H:i:s')
        );
        
        sendResponse(true, '登入成功', array(
            'username' => $input_username,
            'name' => $test_accounts[$input_username]['name'],
            'role' => $test_accounts[$input_username]['role'],
            'redirect_url' => 'dashboard.html'
        ));
        
    } else {
        // 登入失敗
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        
        $remaining_attempts = 5 - $_SESSION['login_attempts'];
        
        if ($remaining_attempts > 0) {
            sendResponse(false, "帳號或密碼錯誤，您還有 {$remaining_attempts} 次機會");
        } else {
            sendResponse(false, "登入失敗次數過多，請等待 5 分鐘後再試");
        }
    }
}

// 資料庫驗證（如果有資料庫連線）
else {
    try {
        // 查詢用戶資料
        $stmt = $pdo->prepare("SELECT id, username, password, name, email, role, status FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$input_username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($input_password, $user['password'])) {
            // 登入成功
            $_SESSION['login_attempts'] = 0;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['login_time'] = time();
            $_SESSION['is_logged_in'] = true;
            
            // 更新最後登入時間
            $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW(), login_count = login_count + 1 WHERE id = ?");
            $update_stmt->execute([$user['id']]);
            
            // 記錄登入日誌
            $log_stmt = $pdo->prepare("INSERT INTO login_logs (user_id, username, ip, user_agent, login_time) VALUES (?, ?, ?, ?, NOW())");
            $log_stmt->execute([
                $user['id'],
                $user['username'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);
            
            sendResponse(true, '登入成功', array(
                'username' => $user['username'],
                'name' => $user['name'],
                'role' => $user['role'],
                'redirect_url' => 'dashboard.html'
            ));
            
        } else {
            // 登入失敗
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            
            $remaining_attempts = 5 - $_SESSION['login_attempts'];
            
            if ($remaining_attempts > 0) {
                sendResponse(false, "帳號或密碼錯誤，您還有 {$remaining_attempts} 次機會");
            } else {
                sendResponse(false, "登入失敗次數過多，請等待 5 分鐘後再試");
            }
        }
        
    } catch(PDOException $e) {
        // 資料庫錯誤
        sendResponse(false, '系統錯誤，請稍後再試');
    }
}

// 其他動作處理
switch ($action) {
    case 'logout':
        session_destroy();
        sendResponse(true, '登出成功');
        break;
        
    case 'check_login':
        if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']) {
            sendResponse(true, '已登入', array(
                'username' => $_SESSION['username'],
                'name' => $_SESSION['user_name'],
                'role' => $_SESSION['user_role']
            ));
        } else {
            sendResponse(false, '未登入');
        }
        break;
        
    default:
        sendResponse(false, '未知的操作');
}
?>
