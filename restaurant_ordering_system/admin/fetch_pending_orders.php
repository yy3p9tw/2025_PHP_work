<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/pdo_db.php'; // Use the new direct PDO connection

// Ensure only logged-in staff/admins can access
if (!isLoggedIn() || !isStaff()) {
    header('Content-Type: application/json');
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    // Prepare and execute the statement safely to count pending orders
    $sql = "SELECT COUNT(*) FROM orders WHERE status = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['pending']); // Bind 'pending' to the placeholder

    // Fetch the result
    $pending_count = $stmt->fetchColumn();

    echo json_encode(['pending_orders' => $pending_count]);

} catch (PDOException $e) {
    // In case of a database error, return an error message
    http_response_code(500); // Internal Server Error
    error_log("Database error: " . $e->getMessage()); // Log the error
    echo json_encode(['error' => 'A database error occurred.']);
}
?>