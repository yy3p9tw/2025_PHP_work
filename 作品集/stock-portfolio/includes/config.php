<?php
// 資料庫設定
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stock_portfolio');

// 網站根目錄
define('ROOT_PATH', dirname(__DIR__));

// API 設定
define('API_TIMEOUT', 30);
define('STOCK_API_KEY', 'your_api_key_here');

// 系統設定
define('SITE_NAME', 'Stock Portfolio');
define('ITEMS_PER_PAGE', 10);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// 錯誤報告設定 (開發環境)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
