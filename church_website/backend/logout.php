<?php
// D:\YU\church_website\backend\logout.php

session_start(); // 啟動 Session

// 清除所有 Session 變數
$_SESSION = array();

// 如果使用 session cookie，也需要刪除它
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 最後，銷毀 Session
session_destroy();

// 導向到登入頁面
header('Location: login.php');
exit();
?>
