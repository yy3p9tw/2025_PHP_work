<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // 不在頁面顯示錯誤，但會記錄

try {
    require_once __DIR__ . '/../includes/db.php';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // 獲取查詢參數
    $category_id = $_GET['category_id'] ?? $_GET['category'] ?? null;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(50, (int)($_GET['limit'] ?? 12)));
    $price_min = isset($_GET['price_min']) ? (float)$_GET['price_min'] : null;
    $price_max = isset($_GET['price_max']) ? (float)$_GET['price_max'] : null;
    $sort = $_GET['sort'] ?? 'newest';
    $search = $_GET['search'] ?? null;
    $offset = ($page - 1) * $limit;
    
    // 基本查詢
    $base_query = "
        FROM products p 
        LEFT JOIN product_category pc ON p.id = pc.product_id 
        LEFT JOIN categories c ON pc.category_id = c.id AND c.status = 1
        WHERE p.status = 1
    ";
    
    $params = [];
    $where_conditions = [];
    
    // 分類篩選
    if ($category_id && $category_id !== 'all') {
        $where_conditions[] = "pc.category_id = ?";
        $params[] = (int)$category_id;
    }
    
    // 價格篩選
    if ($price_min !== null) {
        $where_conditions[] = "p.price >= ?";
        $params[] = $price_min;
    }
    
    if ($price_max !== null) {
        $where_conditions[] = "p.price <= ?";
        $params[] = $price_max;
    }
    
    // 搜尋功能
    if ($search) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // 合併條件
    if (!empty($where_conditions)) {
        $base_query .= " AND " . implode(' AND ', $where_conditions);
    }
    
    // 排序條件
    $order_clause = "ORDER BY ";
    switch ($sort) {
        case 'price-asc':
            $order_clause .= 'p.price ASC';
            break;
        case 'price-desc':
            $order_clause .= 'p.price DESC';
            break;
        case 'name-asc':
            $order_clause .= 'p.name ASC';
            break;
        case 'name-desc':
            $order_clause .= 'p.name DESC';
            break;
        case 'oldest':
            $order_clause .= 'p.created_at ASC';
            break;
        case 'newest':
        default:
            $order_clause .= 'p.created_at DESC';
    }
    
    // 計算總數
    $count_query = "SELECT COUNT(DISTINCT p.id) " . $base_query;
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute($params);
    $total_count = $count_stmt->fetchColumn();
    
    // 查詢產品資料
    $products_query = "
        SELECT DISTINCT
            p.id,
            p.name,
            p.description,
            p.price,
            p.image_url,
            p.created_at,
            GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names,
            GROUP_CONCAT(DISTINCT c.id SEPARATOR ',') as category_ids
        " . $base_query . "
        GROUP BY p.id
        " . $order_clause . "
        LIMIT " . intval($limit) . " OFFSET " . intval($offset) . "
    ";
    
    $products_stmt = $conn->prepare($products_query);
    $products_stmt->execute($params);
    $products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 處理產品資料
    foreach ($products as &$product) {
        $product['price'] = (float)$product['price'];
        
        if ($product['image_url']) {
            $product['image_url'] = 'uploads/' . $product['image_url'];
        } else {
            $product['image_url'] = 'assets/images/no-image.png';
        }
        
        $product['categories'] = [];
        if ($product['category_names']) {
            $names = explode(', ', $product['category_names']);
            $ids = explode(',', $product['category_ids']);
            for ($i = 0; $i < count($names); $i++) {
                if (isset($ids[$i]) && $ids[$i]) {
                    $product['categories'][] = [
                        'id' => (int)$ids[$i],
                        'name' => trim($names[$i])
                    ];
                }
            }
        }
        
        unset($product['category_names'], $product['category_ids']);
        $product['created_at'] = date('Y-m-d H:i:s', strtotime($product['created_at']));
    }
    
    // 計算分頁資訊
    $total_pages = ceil($total_count / $limit);
    
    // 如果有指定分類，獲取分類資訊
    $category_info = null;
    if ($category_id && $category_id !== 'all') {
        $category_stmt = $conn->prepare("SELECT id, name, description FROM categories WHERE id = ? AND status = 1");
        $category_stmt->execute([$category_id]);
        $category_info = $category_stmt->fetch(PDO::FETCH_ASSOC);
        if ($category_info) {
            $category_info['id'] = (int)$category_info['id'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_count' => (int)$total_count,
            'limit' => $limit,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ],
        'category' => $category_info,
        'filters' => [
            'category_id' => $category_id,
            'price_min' => $price_min,
            'price_max' => $price_max,
            'search' => $search,
            'sort' => $sort
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo json_encode([
        'success' => false,
        'error' => '無法載入產品資料',
        'message' => $e->getMessage()
    ]);
}
?>