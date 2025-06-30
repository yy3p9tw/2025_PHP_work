<?php
// D:\YU\church_website\backend\includes\db.php

// --- 資料庫連線設定 ---
$db_host = 'localhost';     // 資料庫主機
$db_user = 'root';          // 資料庫使用者名稱
$db_pass = '';              // 資料庫密碼
$db_name = 'church_db';// 資料庫名稱
$db_charset = 'utf8mb4';    // 資料庫字元編碼

// --- 建立資料庫連線 ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- 檢查連線是否成功 ---
if ($conn->connect_error) {
    // 如果連線失敗，顯示錯誤訊息並終止程式
    die('資料庫連線失敗: ' . $conn->connect_error);
}

// --- 設定資料庫字元編碼 ---
if (!$conn->set_charset($db_charset)) {
    // 如果設定字元編碼失敗，顯示錯誤訊息
    // 在實際應用中，您可能希望記錄此錯誤而不是直接顯示
    error_log("設定資料庫字元編碼失敗: " . $conn->error);
}

// --- 連線成功 ---
// 您可以在其他 PHP 檔案中引入 (include) 此檔案來使用 $conn 變數進行資料庫操作
// 例如: include_once 'includes/db.php';

?>
