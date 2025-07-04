<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// 檢查是否已登入
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 預設動作
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            getNewsList($conn);
            break;
        case 'get':
            getNewsDetail($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => '無效的動作']);
    }
} catch (Exception $e) {
    error_log("News API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '系統錯誤，請稍後再試']);
}

function getNewsList($conn) {
    $limit = $_GET['limit'] ?? 10;
    $offset = $_GET['offset'] ?? 0;
    
    try {
        // 獲取新聞列表
        $stmt = $conn->prepare("
            SELECT id, title, summary, source, url, published_at, created_at
            FROM news 
            WHERE status = 'active'
            ORDER BY published_at DESC, created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 獲取總數
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM news WHERE status = 'active'");
        $stmt->execute();
        $total = $stmt->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'news' => $news,
            'pagination' => [
                'total' => (int)$total,
                'limit' => (int)$limit,
                'offset' => (int)$offset,
                'has_more' => ($offset + $limit) < $total
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Get news list error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '無法獲取新聞列表']);
    }
}

function getNewsDetail($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => '缺少新聞ID']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT id, title, content, summary, source, url, published_at, created_at
            FROM news 
            WHERE id = ? AND status = 'active'
        ");
        
        $stmt->execute([$id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$news) {
            echo json_encode(['success' => false, 'message' => '新聞不存在']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $news
        ]);
        
    } catch (Exception $e) {
        error_log("Get news detail error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '無法獲取新聞詳情']);
    }
}
?>
