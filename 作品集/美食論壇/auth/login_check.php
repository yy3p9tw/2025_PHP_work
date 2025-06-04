<?php
session_start(); // 啟動 session，方便儲存登入狀態

// 連接資料庫
$dsn = "mysql:host=localhost;dbname=forum;charset=utf8";
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// 取得表單傳來的帳號與密碼
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// 查詢資料庫是否有此帳號
$stmt = $pdo->prepare("SELECT * FROM members WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

// 驗證密碼是否正確
if ($user && password_verify($password, $user['password'])) {
    // 登入成功，將會員資訊寫入 session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    // 更新會員的最後登入時間
    $pdo->prepare("UPDATE members SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
    // 導向會員中心
    header("Location: ../member/member_center.php");
    exit;
} else {
    // 登入失敗，回登入頁並顯示錯誤訊息
    header("Location: login.php?msg=帳號或密碼錯誤");
    exit;
}
?>