<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
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
    
    // 清空購物車
    $delete_sql = "DELETE FROM cart_items WHERE session_id = :session_id";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bindParam(':session_id', $session_id);
    $delete_stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => '購物車已清空',
        'removed_count' => $delete_stmt->rowCount()
    ]);

} catch (PDOException $e) {
    error_log("Error clearing cart: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '無法清空購物車'
    ]);
} catch (Exception $e) {
    error_log("General error in cart_clear.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '系統錯誤'
    ]);
}
?>
