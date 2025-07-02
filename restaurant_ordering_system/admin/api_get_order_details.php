<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing required parameters.']);
    exit;
}

$order_id = $_GET['order_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM order_details WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $details = $stmt->fetchAll();

    echo json_encode($details);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    error_log($e->getMessage());
    echo json_encode(['error' => 'Database query failed.']);
}
?>