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
            $category_id = $_POST['id'] ?? null;
            $name = trim($_POST['name']);

            if (empty($name)) {
                throw new Exception("分類名稱不能為空。");
            }

            if ($action === 'edit') {
                $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
                $stmt->execute([$name, $category_id]);
            } else { // add
                $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
                $stmt->execute([$name]);
            }
            echo json_encode(['success' => true, 'message' => ($action === 'edit' ? '分類已更新。' : '分類已新增。')]);
            break;

        case 'delete':
            $category_id = $_POST['id'] ?? null;
            if (!$category_id) throw new Exception("Missing category ID.");

            // Optional: Add logic to prevent deleting categories with associated menu items
            // Or, set menu_item category_id to NULL/default before deleting category

            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$category_id]);
            echo json_encode(['success' => true, 'message' => '分類已刪除。']);
            break;

        default:
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    error_log("Category CRUD Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>