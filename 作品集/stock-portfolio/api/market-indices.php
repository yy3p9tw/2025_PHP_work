<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../includes/database.php';
require_once '../includes/auth.php';

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $db = new Database();
    
    // 獲取市場指數
    $indices = $db->fetchAll("
        SELECT * FROM market_indices 
        WHERE status = 'active' 
        ORDER BY display_order
    ");
    
    $response = [
        'success' => true,
        'data' => $indices,
        'timestamp' => time()
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => time()
    ];
    
    http_response_code(500);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>
