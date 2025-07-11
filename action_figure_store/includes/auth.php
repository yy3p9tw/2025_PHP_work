<?php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: dashboard.php'); // 或者導向一個權限不足的頁面
        exit();
    }
}

// 新增 checkAdminAccess 函數，用於分類管理等進階功能
function checkAdminAccess() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
    
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

?>