<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?msg=請先登入會員");
    exit;
}
$dsn = "mysql:host=localhost;dbname=forum;charset=utf8";
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$email = trim($_POST['email'] ?? '');
$birthday = $_POST['birthday'] ?? '';

if ($email === '' || $birthday === '') {
    header("Location: edit_profile.php?msg=請填寫完整資料");
    exit;
}

// 檢查 email 是否重複（排除自己）
$stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE email=? AND id<>?");
$stmt->execute([$email, $_SESSION['user_id']]);
if ($stmt->fetchColumn() > 0) {
    header("Location: edit_profile.php?msg=此Email已被其他會員使用");
    exit;
}

// 更新資料
$stmt = $pdo->prepare("UPDATE members SET email=?, birthday=? WHERE id=?");
$stmt->execute([$email, $birthday, $_SESSION['user_id']]);

header("Location: member_center.php?msg=資料已更新");
exit;
?>