<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 獲取 session_id（暫時使用，後續會整合會員系統）
    session_start();
    if (!isset($_SESSION['cart_session_id'])) {
        $_SESSION['cart_session_id'] = session_id();
    }
    $session_id = $_SESSION['cart_session_id'];
    
    // 查詢購物車商品
    $sql = "SELECT ci.*, p.name, p.description, p.price, p.image_url 
            FROM cart_items ci 
            JOIN products p ON ci.product_id = p.id 
            WHERE ci.session_id = :session_id 
            ORDER BY ci.added_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':session_id', $session_id);
    $stmt->execute();
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 處理圖片 URL
    foreach ($cart_items as &$item) {
        if ($item['image_url']) {
            $item['image_url'] = 'uploads/' . $item['image_url'];
        } else {
            $item['image_url'] = 'assets/images/placeholder_figure.jpg';
        }
    }
    
    // 計算總計
    $total_quantity = 0;
    $total_amount = 0;
    
    foreach ($cart_items as $item) {
        $total_quantity += $item['quantity'];
        $total_amount += $item['price'] * $item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'items' => $cart_items,
        'summary' => [
            'total_quantity' => $total_quantity,
            'total_amount' => $total_amount,
            'item_count' => count($cart_items)
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error fetching cart: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '無法獲取購物車資料'
    ]);
} catch (Exception $e) {
    error_log("General error in cart_get.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => '系統錯誤'
    ]);
}
?>
