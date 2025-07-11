<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 獲取 POST 資料
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
        echo json_encode([
            'success' => false,
            'error' => '缺少必要參數'
        ]);
        exit;
    }
    
    $product_id = (int)$input['product_id'];
    $quantity = (int)$input['quantity'];
    
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode([
            'success' => false,
            'error' => '參數無效'
        ]);
        exit;
    }
    
    // 獲取 session_id
    session_start();
    if (!isset($_SESSION['cart_session_id'])) {
        $_SESSION['cart_session_id'] = session_id();
    }
    $session_id = $_SESSION['cart_session_id'];
    
    // 檢查商品是否存在
    $check_product_sql = "SELECT id, name, price FROM products WHERE id = :product_id";
    $check_stmt = $conn->prepare($check_product_sql);
    $check_stmt->bindParam(':product_id', $product_id);
    $check_stmt->execute();
    $product = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode([
            'success' => false,
            'error' => '商品不存在'
        ]);
        exit;
    }
    
    // 檢查購物車中是否已有此商品
    $check_cart_sql = "SELECT id, quantity FROM cart_items WHERE session_id = :session_id AND product_id = :product_id";
    $check_cart_stmt = $conn->prepare($check_cart_sql);
    $check_cart_stmt->bindParam(':session_id', $session_id);
    $check_cart_stmt->bindParam(':product_id', $product_id);
    $check_cart_stmt->execute();
    $existing_item = $check_cart_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_item) {
        // 更新數量
        $new_quantity = $existing_item['quantity'] + $quantity;
        $update_sql = "UPDATE cart_items SET quantity = :quantity, updated_at = NOW() WHERE id = :id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':quantity', $new_quantity);
        $update_stmt->bindParam(':id', $existing_item['id']);
        $update_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => '商品數量已更新',
            'action' => 'updated',
            'new_quantity' => $new_quantity
        ]);
    } else {
        // 新增商品到購物車
        $insert_sql = "INSERT INTO cart_items (session_id, product_id, quantity, price, added_at, updated_at) 
                       VALUES (:session_id, :product_id, :quantity, :price, NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bindParam(':session_id', $session_id);
        $insert_stmt->bindParam(':product_id', $product_id);
        $insert_stmt->bindParam(':quantity', $quantity);
        $insert_stmt->bindParam(':price', $product['price']);
        $insert_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => '商品已加入購物車',
            'action' => 'added',
            'product_name' => $product['name']
        ]);
    }

} catch (PDOException $e) {
    error_log("Error adding to cart: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '無法加入購物車'
    ]);
} catch (Exception $e) {
    error_log("General error in cart_add.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '系統錯誤'
    ]);
}
?>
