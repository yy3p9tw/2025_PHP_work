<?php
// 測試 stocks API 的簡單版本
header('Content-Type: application/json');
session_start();

// 開啟錯誤報告但不顯示到輸出
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    require_once '../includes/config.php';
    require_once '../includes/db.php';
    require_once '../includes/auth.php';

    // 檢查是否登入
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => '未授權訪問']);
        exit;
    }

    $db = new Database();
    $conn = $db->getConnection();
    
    // 簡單查詢
    $sql = "SELECT code, name, industry, current_price, price_change, change_percent, volume, market_cap FROM stocks WHERE status = 'active' ORDER BY code LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 確保只輸出 JSON
    echo json_encode([
        'data' => $stocks,
        'recordsTotal' => count($stocks),
        'recordsFiltered' => count($stocks)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
?>
