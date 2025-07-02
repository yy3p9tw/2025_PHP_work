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
            $item_id = $_POST['id'] ?? null;
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = (float)$_POST['price'];
            $category_id = (int)$_POST['category_id'];
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            $image_url = $_POST['current_image_url'] ?? null;
            $selected_customization_options = $_POST['customization_options'] ?? [];

            if (empty($name) || $price <= 0 || $category_id <= 0) {
                throw new Exception("請填寫所有必填欄位並確保價格有效。");
            }

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $target_dir = "../uploads/";
                $image_name = basename($_FILES["image"]["name"]);
                $image_file_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
                $new_image_name = uniqid() . '.' . $image_file_type;
                $target_file = $target_dir . $new_image_name;

                // Basic validation
                $check = getimagesize($_FILES["image"]["tmp_name"]);
                if ($check === false) throw new Exception("檔案不是圖片。");
                if ($_FILES["image"]["size"] > 5000000) throw new Exception("抱歉，您的檔案太大。");
                if (!in_array($image_file_type, ["jpg", "png", "jpeg", "gif"])) throw new Exception("抱歉，只允許 JPG, JPEG, PNG & GIF 檔案。");

                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    throw new Exception("抱歉，上傳您的檔案時發生錯誤。");
                }
                $image_url = $new_image_name;
            }

            $pdo->beginTransaction();

            if ($action === 'edit') {
                $stmt = $pdo->prepare('UPDATE menu_items SET name = ?, description = ?, price = ?, category_id = ?, image_url = ?, is_available = ? WHERE id = ?');
                $stmt->execute([$name, $description, $price, $category_id, $image_url, $is_available, $item_id]);
            } else { // add
                $stmt = $pdo->prepare('INSERT INTO menu_items (name, description, price, category_id, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$name, $description, $price, $category_id, $image_url, $is_available]);
                $item_id = $pdo->lastInsertId();
            }

            // Update menu_item_customizations
            $stmt = $pdo->prepare('DELETE FROM menu_item_customizations WHERE menu_item_id = ?');
            $stmt->execute([$item_id]);

            if (!empty($selected_customization_options)) {
                $stmt = $pdo->prepare('INSERT INTO menu_item_customizations (menu_item_id, option_id) VALUES (?, ?)');
                foreach ($selected_customization_options as $option_id_val) {
                    $stmt->execute([$item_id, $option_id_val]);
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => ($action === 'edit' ? '餐點已更新。' : '餐點已新增。')]);
            break;

        case 'delete':
            $item_id = $_POST['id'] ?? null;
            if (!$item_id) throw new Exception("Missing item ID.");

            $pdo->beginTransaction();
            // Delete related order details first
            $stmt = $pdo->prepare('DELETE FROM order_details WHERE menu_item_id = ?');
            $stmt->execute([$item_id]);
            // Then delete menu item
            $stmt = $pdo->prepare('DELETE FROM menu_items WHERE id = ?');
            $stmt->execute([$item_id]);
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => '餐點已刪除。']);
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
    error_log("Menu Item CRUD Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>