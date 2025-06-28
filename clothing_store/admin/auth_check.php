<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/csrf_functions.php';

// 在這裡獲取 CSRF Token，確保後台頁面有 CSRF Token
$csrf_token = get_csrf_token();

// 檢查使用者是否已登入
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// 檢查使用者是否為管理員
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // 如果不是管理員，導向到首頁或顯示權限不足訊息
    header('Location: ../index.php'); // 或者可以導向到一個 access_denied.php 頁面
    exit;
}
?>