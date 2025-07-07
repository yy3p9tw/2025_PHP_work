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
            // 獲取股票列表
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? '';
            $limit = (int)($_GET['limit'] ?? 20);
            $offset = (int)($_GET['offset'] ?? 0);
            
            $where_conditions = ['status = ?'];
            $params = ['active'];
            
            if (!empty($search)) {
                $where_conditions[] = '(code LIKE ? OR name LIKE ?)';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            if (!empty($category)) {
                $where_conditions[] = 'industry = ?';
                $params[] = $category;
            }
            
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
            
            $stocks = $db->fetchAll("
                SELECT * FROM stocks 
                $where_clause 
                ORDER BY code ASC 
                LIMIT ? OFFSET ?
            ", array_merge($params, [$limit, $offset]));
            
            $total_result = $db->fetchOne("
                SELECT COUNT(*) as count FROM stocks 
                $where_clause
            ", $params);
            $total = $total_result['count'];
            
            $response = [
                'success' => true,
                'data' => $stocks,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'timestamp' => time()
            ];
            break;
            
        case 'portfolio':
            // 獲取用戶投資組合
            if (!isLoggedIn()) {
                throw new Exception('請先登入');
            }
            
            $user_id = $_SESSION['user_id'];
            
            $portfolio = $db->fetchAll("
                SELECT p.*, s.name as stock_name, s.current_price, s.price_change, s.change_percent
                FROM portfolios p
                JOIN stocks s ON p.stock_code = s.code
                WHERE p.user_id = ? AND p.quantity > 0
                ORDER BY p.updated_at DESC
            ", [$user_id]);
            
            $response = [
                'success' => true,
                'data' => $portfolio,
                'timestamp' => time()
            ];
            break;
            
        case 'hot':
            // 獲取熱門股票
            $limit = (int)($_GET['limit'] ?? 10);
            
            $stocks = $db->fetchAll("
                SELECT * FROM stocks 
                WHERE status = 'active' 
                ORDER BY volume DESC 
                LIMIT ?
            ", [$limit]);
            
            $response = [
                'success' => true,
                'data' => $stocks,
                'timestamp' => time()
            ];
            break;
            
        case 'detail':
            // 獲取股票詳細資料
            $code = $_GET['code'] ?? '';
            
            if (empty($code)) {
                throw new Exception('股票代碼不能為空');
            }
            
            $stock = $db->fetchOne("
                SELECT * FROM stocks 
                WHERE code = ? AND status = 'active'
            ", [$code]);
            
            if (empty($stock)) {
                throw new Exception('找不到該股票');
            }
            
            // 獲取價格歷史
            $price_history = $db->fetchAll("
                SELECT * FROM stock_prices 
                WHERE stock_code = ? 
                ORDER BY date DESC 
                LIMIT 30
            ", [$code]);
            
            $response = [
                'success' => true,
                'data' => [
                    'stock' => $stock,
                    'price_history' => $price_history
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
