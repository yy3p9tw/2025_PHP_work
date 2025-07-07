<?php
// 資料庫配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'stock_portfolio');
define('DB_USER', 'root');
define('DB_PASS', '');

// 應用程式配置
define('APP_NAME', '股票投資組合系統');
define('APP_VERSION', '2.0');
define('BASE_URL', 'http://localhost:8000');

// 安全設定
define('SESSION_LIFETIME', 3600); // 1小時
define('CSRF_TOKEN_NAME', '_token');

// 時區設定
date_default_timezone_set('Asia/Taipei');

// 錯誤報告設定
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
