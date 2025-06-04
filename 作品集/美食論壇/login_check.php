<?php
session_start();
$dsn = "mysql:host=localhost;dbname=forum;charset=utf8";
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM members WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // 登入成功，寫入 session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    // 更新最後登入時間
    $pdo->prepare("UPDATE members SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
    header("Location: member_center.php");
    exit;
} else {
    header("Location: login.php?msg=帳號或密碼錯誤");
    exit;
}
?>