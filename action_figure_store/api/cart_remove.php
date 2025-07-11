<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 獲取 POST 資料
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        echo json_encode([
            'success' => false,
            'error' => '缺少商品 ID'
        ]);
        exit;
    }
    
    $product_id = (int)$input['product_id'];
    
    if ($product_id <= 0) {
        echo json_encode([
            'success' => false,
            'error' => '商品 ID 無效'
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
    
    // 刪除商品
    $delete_sql = "DELETE FROM cart_items WHERE session_id = :session_id AND product_id = :product_id";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bindParam(':session_id', $session_id);
    $delete_stmt->bindParam(':product_id', $product_id);
    $delete_stmt->execute();
    
    if ($delete_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => '商品已從購物車移除'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => '找不到要移除的商品'
        ]);
    }

} catch (PDOException $e) {
    error_log("Error removing from cart: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '無法移除商品'
    ]);
} catch (Exception $e) {
    error_log("General error in cart_remove.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '系統錯誤'
    ]);
}
?>
