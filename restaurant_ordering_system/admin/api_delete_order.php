<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/db.php';

// Ensure only logged-in staff/admins can access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = $input['order_id'] ?? null;

if (!$order_id) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing order ID.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // First, delete order details associated with this order
    $stmt = $pdo->prepare('DELETE FROM order_details WHERE order_id = ?');
    $stmt->execute([$order_id]);

    // Then, delete the order itself
    $stmt = $pdo->prepare('DELETE FROM orders WHERE id = ?');
    $stmt->execute([$order_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => '訂單已成功刪除。']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500); // Internal Server Error
    error_log("Order deletion error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '刪除訂單失敗。']);
}
?>