<?php
session_start();
echo "Session 測試:<br>";
echo "是否有 session: " . (session_id() ? "有" : "無") . "<br>";
echo "用戶 ID: " . ($_SESSION['user_id'] ?? "未設置") . "<br>";
echo "用戶名: " . ($_SESSION['username'] ?? "未設置") . "<br>";
echo "角色: " . ($_SESSION['role'] ?? "未設置") . "<br>";

require_once 'includes/auth.php';
echo "登入狀態: " . (isLoggedIn() ? "已登入" : "未登入") . "<br>";
?>
