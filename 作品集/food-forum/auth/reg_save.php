<?php
// 連接資料庫
require_once '../includes/db.php'; // 路徑依實際位置調整
$pdo = getPDO();

// 取得表單資料
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$email = trim($_POST['email'] ?? '');

// 基本驗證，檢查欄位是否填寫完整
if ($username === '' || $password === '' || $email === '') {
    header("Location: reg.php?msg=請填寫完整資料");
    exit;
}

// 檢查帳號或信箱是否已存在
$stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE username=? OR email=?");
$stmt->execute([$username, $email]);
if ($stmt->fetchColumn() > 0) {
    // 若帳號或信箱已被註冊，導回註冊頁並顯示訊息
    header("Location: reg.php?msg=帳號或信箱已被註冊");
    exit;
}

// 密碼加密（安全性）
$hash = password_hash($password, PASSWORD_DEFAULT);

// 寫入資料庫
$stmt = $pdo->prepare("INSERT INTO members (username, password, email) VALUES (?, ?, ?)");
$stmt->execute([$username, $hash, $email]);

// 註冊成功，導向登入頁
header("Location: login.php?msg=註冊成功，請登入");
exit;
?>