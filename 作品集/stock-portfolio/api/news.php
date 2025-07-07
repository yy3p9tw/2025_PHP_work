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
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            // 獲取新聞列表
            $limit = (int)($_GET['limit'] ?? 10);
            $offset = (int)($_GET['offset'] ?? 0);
            
            $news = $db->fetchAll("
                SELECT * FROM news 
                WHERE status = 'active' 
                ORDER BY published_at DESC 
                LIMIT ? OFFSET ?
            ", [$limit, $offset]);
            
            $total_result = $db->fetchOne("
                SELECT COUNT(*) as count FROM news 
                WHERE status = 'active'
            ");
            $total = $total_result['count'];
            
            $response = [
                'success' => true,
                'data' => $news,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'timestamp' => time()
            ];
            break;
            
        case 'latest':
            // 獲取最新新聞
            $limit = (int)($_GET['limit'] ?? 5);
            
            $news = $db->fetchAll("
                SELECT * FROM news 
                WHERE status = 'active' 
                ORDER BY published_at DESC 
                LIMIT ?
            ", [$limit]);
            
            $response = [
                'success' => true,
                'data' => $news,
                'timestamp' => time()
            ];
            break;
            
        case 'detail':
            // 獲取新聞詳細資料
            $id = (int)($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('新聞ID不能為空');
            }
            
            $news = $db->fetchOne("
                SELECT * FROM news 
                WHERE id = ? AND status = 'active'
            ", [$id]);
            
            if (empty($news)) {
                throw new Exception('找不到該新聞');
            }
            
            // 獲取相關新聞
            $related = $db->fetchAll("
                SELECT id, title, published_at FROM news 
                WHERE id != ? AND status = 'active' 
                ORDER BY published_at DESC 
                LIMIT 5
            ", [$id]);
            
            $response = [
                'success' => true,
                'data' => [
                    'news' => $news,
                    'related' => $related
                ],
                'timestamp' => time()
            ];
            break;
            
        default:
            throw new Exception('不支援的操作');
    }
    
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
