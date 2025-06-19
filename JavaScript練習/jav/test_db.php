<?php
// 測試資料庫連線的檔案
header('Content-Type: application/json; charset=utf-8');

// 資料庫連線設定
$host = 'localhost';
$dbname = 'member_system';
$username = 'root';
$password = '';

try {
    // 建立 PDO 連線
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 測試查詢
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => '資料庫連線成功',
        'user_count' => $result['user_count'],
        'database' => $dbname,
        'host' => $host
    ], JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => '資料庫連線失敗',
        'error' => $e->getMessage(),
        'database' => $dbname,
        'host' => $host
    ], JSON_UNESCAPED_UNICODE);
}
?>
