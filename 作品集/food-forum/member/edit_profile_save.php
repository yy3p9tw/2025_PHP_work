<?php
session_start(); // 啟動 session，確認會員身份
if (!isset($_SESSION['user_id'])) {
    // 未登入則導回登入頁
    header("Location: ../auth/login.php?msg=請先登入會員");
    exit;
}

// 連接資料庫
require_once '../includes/db.php'; // 路徑依實際位置調整
$pdo = getPDO();

// 取得表單資料
$email = trim($_POST['email'] ?? '');
$birthday = $_POST['birthday'] ?? '';

// 檢查欄位是否填寫完整
if ($email === '' || $birthday === '') {
    header("Location: edit_profile.php?msg=請填寫完整資料");
    exit;
}

// 檢查 email 是否重複（排除自己）
$stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE email=? AND id<>?");
$stmt->execute([$email, $_SESSION['user_id']]);
if ($stmt->fetchColumn() > 0) {
    // 若 email 已被其他會員使用，導回編輯頁
    header("Location: edit_profile.php?msg=此Email已被其他會員使用");
    exit;
}

// 更新會員資料
$stmt = $pdo->prepare("UPDATE members SET email=?, birthday=? WHERE id=?");
$stmt->execute([$email, $birthday, $_SESSION['user_id']]);

// 更新成功，導回會員中心並顯示訊息
header("Location: member_center.php?msg=資料已更新");
exit;
?>