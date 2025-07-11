<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

// 檢查管理員權限
session_start();
try {
    requireLogin();
    requireAdmin();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => '需要管理員權限'
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    switch ($method) {
        case 'POST':
            // 新增分類
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');
            $parent_id = !empty($input['parent_id']) ? (int)$input['parent_id'] : null;
            $sort_order = (int)($input['sort_order'] ?? 0);
            $status = ($input['status'] ?? 'active') === 'active' ? 1 : 0;
            
            if (empty($name)) {
                throw new Exception('分類名稱不能為空');
            }
            
            $stmt = $conn->prepare("INSERT INTO categories (name, description, parent_id, sort_order, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $parent_id, $sort_order, $status]);
            
            $new_id = $conn->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => '分類新增成功',
                'id' => $new_id
            ]);
            break;
            
        case 'PUT':
            // 編輯分類
            $id = (int)($input['id'] ?? 0);
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');
            $parent_id = !empty($input['parent_id']) ? (int)$input['parent_id'] : null;
            $sort_order = (int)($input['sort_order'] ?? 0);
            $status = ($input['status'] ?? 'active') === 'active' ? 1 : 0;
            
            if ($id <= 0) {
                throw new Exception('無效的分類 ID');
            }
            
            if (empty($name)) {
                throw new Exception('分類名稱不能為空');
            }
            
            // 檢查是否會造成循環引用
            if ($parent_id && $parent_id == $id) {
                throw new Exception('分類不能設定自己為父分類');
            }
            
            // 檢查是否會造成循環引用（深度檢查）
            if ($parent_id) {
                $current_parent = $parent_id;
                $depth = 0;
                while ($current_parent && $depth < 10) {
                    if ($current_parent == $id) {
                        throw new Exception('不能設定子分類為父分類（會造成循環引用）');
                    }
                    $check_stmt = $conn->prepare("SELECT parent_id FROM categories WHERE id = ?");
                    $check_stmt->execute([$current_parent]);
                    $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
                    $current_parent = $result ? $result['parent_id'] : null;
                    $depth++;
                }
            }
            
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, parent_id = ?, sort_order = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $description, $parent_id, $sort_order, $status, $id]);
            
            echo json_encode([
                'success' => true,
                'message' => '分類更新成功'
            ]);
            break;
            
        case 'DELETE':
            // 刪除分類
            $id = (int)($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('無效的分類 ID');
            }
            
            // 檢查是否有子分類
            $check_children = $conn->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
            $check_children->execute([$id]);
            $children_count = $check_children->fetchColumn();
            
            if ($children_count > 0) {
                throw new Exception('此分類有子分類，無法刪除');
            }
            
            // 檢查是否有商品使用此分類
            $check_products = $conn->prepare("SELECT COUNT(*) FROM product_category WHERE category_id = ?");
            $check_products->execute([$id]);
            $products_count = $check_products->fetchColumn();
            
            if ($products_count > 0) {
                throw new Exception("此分類有 {$products_count} 個商品使用，無法刪除。請先將商品移至其他分類或刪除商品。");
            }
            
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => '分類刪除成功'
            ]);
            break;
            
        case 'PATCH':
            // 批次操作：移動分類下的產品到其他分類
            $action = $input['action'] ?? '';
            
            if ($action === 'move_products') {
                $source_category_id = (int)($input['source_category_id'] ?? 0);
                $target_category_id = (int)($input['target_category_id'] ?? 0);
                
                if ($source_category_id <= 0 || $target_category_id <= 0) {
                    throw new Exception('無效的分類 ID');
                }
                
                if ($source_category_id === $target_category_id) {
                    throw new Exception('來源分類和目標分類不能相同');
                }
                
                // 檢查目標分類是否存在且啟用
                $check_target = $conn->prepare("SELECT id FROM categories WHERE id = ? AND status = 1");
                $check_target->execute([$target_category_id]);
                if (!$check_target->fetch()) {
                    throw new Exception('目標分類不存在或已停用');
                }
                
                // 移動產品
                $stmt = $conn->prepare("UPDATE product_category SET category_id = ? WHERE category_id = ?");
                $result = $stmt->execute([$target_category_id, $source_category_id]);
                
                // 獲取移動的產品數量
                $moved_count = $stmt->rowCount();
                
                echo json_encode([
                    'success' => true,
                    'message' => "成功將 {$moved_count} 個產品從分類移至目標分類",
                    'moved_count' => $moved_count
                ]);
                
            } elseif ($action === 'clear_products') {
                $category_id = (int)($input['category_id'] ?? 0);
                
                if ($category_id <= 0) {
                    throw new Exception('無效的分類 ID');
                }
                
                // 清除產品關聯
                $stmt = $conn->prepare("DELETE FROM product_category WHERE category_id = ?");
                $result = $stmt->execute([$category_id]);
                
                $cleared_count = $stmt->rowCount();
                
                echo json_encode([
                    'success' => true,
                    'message' => "成功清除 {$cleared_count} 個產品關聯",
                    'cleared_count' => $cleared_count
                ]);
                
            } else {
                throw new Exception('不支援的批次操作');
            }
            break;

        default:
            throw new Exception('不支援的請求方法');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
