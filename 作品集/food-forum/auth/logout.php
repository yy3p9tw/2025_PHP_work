<?php
session_start(); // 啟動 session，準備清除登入狀態
session_unset(); // 清除所有 session 變數
session_destroy(); // 銷毀 session
header("Location: login.php?msg=您已成功登出"); // 導回登入頁並顯示訊息
exit;
?>