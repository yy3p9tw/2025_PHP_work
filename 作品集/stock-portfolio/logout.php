<?php
session_start();
require_once 'includes/auth.php';

// 銷毀 session
session_destroy();

// 重新導向到登入頁面
header('Location: login.php');
exit;
?>
