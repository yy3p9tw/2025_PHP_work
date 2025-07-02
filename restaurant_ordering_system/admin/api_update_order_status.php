<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = $input['order_id'] ?? null;
$new_status = $input['status'] ?? null;

if (!$order_id || !$new_status) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing order ID or new status.']);
    exit;
}

// Validate status to prevent arbitrary values
$allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid status value.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Order status updated.']);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Order not found or status already updated.']);
    }

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
?>