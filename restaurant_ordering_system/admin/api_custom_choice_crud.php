<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin')) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
        case 'edit':
            $choice_id = $_POST['id'] ?? null;
            $option_id = (int)$_POST['option_id'];
            $name = trim($_POST['name']);
            $price_adjustment = (float)$_POST['price_adjustment'];

            if (empty($name)) {
                throw new Exception("客製化選擇項名稱不能為空。");
            }

            if ($action === 'edit') {
                $stmt = $pdo->prepare('UPDATE customization_choices SET name = ?, price_adjustment = ? WHERE id = ?');
                $stmt->execute([$name, $price_adjustment, $choice_id]);
            } else { // add
                $stmt = $pdo->prepare('INSERT INTO customization_choices (option_id, name, price_adjustment) VALUES (?, ?, ?)');
                $stmt->execute([$option_id, $name, $price_adjustment]);
            }
            echo json_encode(['success' => true, 'message' => ($action === 'edit' ? '客製化選擇項已更新。' : '客製化選擇項已新增。')]);
            break;

        case 'delete':
            $choice_id = $_POST['id'] ?? null;
            if (!$choice_id) throw new Exception("Missing choice ID.");

            $pdo->beginTransaction();
            // Delete related order item customizations first
            $stmt = $pdo->prepare('DELETE FROM order_item_customizations WHERE customization_choice_id = ?');
            $stmt->execute([$choice_id]);
            // Then delete the choice
            $stmt = $pdo->prepare('DELETE FROM customization_choices WHERE id = ?');
            $stmt->execute([$choice_id]);
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => '客製化選擇項已刪除。']);
            break;

        default:
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500); // Internal Server Error
    error_log("Custom Choice CRUD Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>