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
    
    if ($product_id <= 0 || $quantity < 0) {
        echo json_encode([
            'success' => false,
            'error' => '參數無效'
        ]);
        exit;
    }
    
    // 獲取 session_id
    session_start();
    if (!isset($_SESSION['cart_session_id'])) {
        echo json_encode([
            'success' => false,
            'error' => '購物車 session 不存在'
        ]);
        exit;
    }
    $session_id = $_SESSION['cart_session_id'];
    
    if ($quantity == 0) {
        // 數量為 0，移除商品
        $delete_sql = "DELETE FROM cart_items WHERE session_id = :session_id AND product_id = :product_id";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bindParam(':session_id', $session_id);
        $delete_stmt->bindParam(':product_id', $product_id);
        $delete_stmt->execute();
        
        if ($delete_stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => '商品已從購物車移除',
                'action' => 'removed'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => '找不到要移除的商品'
            ]);
        }
    } else {
        // 更新數量
        $update_sql = "UPDATE cart_items SET quantity = :quantity, updated_at = NOW() 
                       WHERE session_id = :session_id AND product_id = :product_id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':quantity', $quantity);
        $update_stmt->bindParam(':session_id', $session_id);
        $update_stmt->bindParam(':product_id', $product_id);
        $update_stmt->execute();
        
        if ($update_stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => '商品數量已更新',
                'action' => 'updated',
                'new_quantity' => $quantity
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => '找不到要更新的商品'
            ]);
        }
    }

} catch (PDOException $e) {
    error_log("Error updating cart: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '無法更新購物車'
    ]);
} catch (Exception $e) {
    error_log("General error in cart_update.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '系統錯誤'
    ]);
}
?>
