<?php
require_once __DIR__ . '/database.php';

// 建立資料庫連接
$db = new Database();

// 啟動 session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 檢查用戶是否登入
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// 檢查是否為管理員
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

// 登入用戶
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    
    // 更新最後登入時間
    global $db;
    $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
}

// 登出用戶
function logoutUser() {
    session_unset();
    session_destroy();
}

// 檢查權限中介軟體
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// 驗證用戶登入
function authenticateUser($username, $password) {
    global $db;
    
    $user = $db->fetchOne("SELECT * FROM users WHERE username = ? OR email = ?", [$username, $username]);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}

// 註冊新用戶
function registerUser($username, $email, $password) {
    global $db;
    
    // 檢查用戶名和郵箱是否已存在
    $existing = $db->fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
    
    if ($existing) {
        return false; // 用戶已存在
    }
    
    // 建立新用戶
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $db->query("INSERT INTO users (username, email, password) VALUES (?, ?, ?)", [$username, $email, $hashedPassword]);
        return $db->getConnection()->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

// 取得目前用戶資訊
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $db;
    return $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

// 生成 CSRF Token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 驗證 CSRF Token
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
