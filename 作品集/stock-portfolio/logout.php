<?php
session_start();
require_once 'includes/auth.php';

// 執行登出
logout_user();

// 重定向到首頁
header('Location: index.php');
exit();
?>
