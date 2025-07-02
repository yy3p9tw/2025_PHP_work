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
            $option_id = $_POST['id'] ?? null;
            $name = trim($_POST['name']);
            $type = $_POST['type'];
            $is_required = isset($_POST['is_required']) ? 1 : 0;

            if (empty($name)) {
                throw new Exception("客製化選項名稱不能為空。");
            }

            if ($action === 'edit') {
                $stmt = $pdo->prepare('UPDATE customization_options SET name = ?, type = ?, is_required = ? WHERE id = ?');
                $stmt->execute([$name, $type, $is_required, $option_id]);
            } else { // add
                $stmt = $pdo->prepare('INSERT INTO customization_options (name, type, is_required) VALUES (?, ?, ?)');
                $stmt->execute([$name, $type, $is_required]);
            }
            echo json_encode(['success' => true, 'message' => ($action === 'edit' ? '客製化選項已更新。' : '客製化選項已新增。')]);
            break;

        case 'delete':
            $option_id = $_POST['id'] ?? null;
            if (!$option_id) throw new Exception("Missing option ID.");

            $pdo->beginTransaction();
            // Delete associated choices first
            $stmt = $pdo->prepare('DELETE FROM customization_choices WHERE option_id = ?');
            $stmt->execute([$option_id]);
            // Then delete the option
            $stmt = $pdo->prepare('DELETE FROM customization_options WHERE id = ?');
            $stmt->execute([$option_id]);
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => '客製化選項已刪除。']);
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
    error_log("Custom Option CRUD Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>