<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/pdo_db.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: menu.php');
    exit();
}

$message = 'An unknown error occurred.';
$message_type = 'danger';

try {
    $pdo->beginTransaction();

    // --- Menu Item Operations ---
    if (isset($_POST['add_menu_item']) || isset($_POST['edit_menu_item'])) {
        $item_id = $_POST['item_id'] ?? null;
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $image_url = $_POST['current_image_url'] ?? null;
        $selected_customization_options = $_POST['customization_options'] ?? [];

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../uploads/";
            $image_name = basename($_FILES["image"]["name"]);
            $image_file_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
            $new_image_name = uniqid() . '.' . $image_file_type;
            $target_file = $target_dir . $new_image_name;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $new_image_name;
            }
        }

        if (empty($name) || $price <= 0 || $category_id <= 0) {
            throw new Exception("Please fill all required fields and ensure the price is valid.");
        }

        if ($item_id) { // Edit
            $stmt = $pdo->prepare('UPDATE menu_items SET name = ?, description = ?, price = ?, category_id = ?, image_url = ?, is_available = ? WHERE id = ?');
            $stmt->execute([$name, $description, $price, $category_id, $image_url, $is_available, $item_id]);
            $message = "Menu item updated successfully.";
        } else { // Add
            $stmt = $pdo->prepare('INSERT INTO menu_items (name, description, price, category_id, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $description, $price, $category_id, $image_url, $is_available]);
            $item_id = $pdo->lastInsertId();
            $message = "Menu item added successfully.";
        }

        $delete_stmt = $pdo->prepare('DELETE FROM menu_item_customizations WHERE menu_item_id = ?');
        $delete_stmt->execute([$item_id]);

        if (!empty($selected_customization_options)) {
            $insert_stmt = $pdo->prepare('INSERT INTO menu_item_customizations (menu_item_id, option_id) VALUES (?, ?)');
            foreach ($selected_customization_options as $option_id_val) {
                $insert_stmt->execute([$item_id, $option_id_val]);
            }
        }
        $message_type = "success";

    } elseif (isset($_POST['delete_menu_item'])) {
        $item_id = $_POST['item_id'];
        $stmt = $pdo->prepare('DELETE FROM order_details WHERE menu_item_id = ?');
        $stmt->execute([$item_id]);
        $stmt = $pdo->prepare('DELETE FROM menu_items WHERE id = ?');
        $stmt->execute([$item_id]);
        $message = "Menu item deleted successfully.";
        $message_type = "success";

    } elseif (isset($_POST['add_category']) || isset($_POST['edit_category'])) {
        $category_id = $_POST['category_id'] ?? null;
        $category_name = trim($_POST['category_name']);
        if (empty($category_name)) throw new Exception("Category name cannot be empty.");

        if ($category_id) {
            $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
            $stmt->execute([$category_name, $category_id]);
            $message = "Category updated successfully.";
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$category_name]);
            $message = "Category added successfully.";
        }
        $message_type = "success";

    } elseif (isset($_POST['delete_category'])) {
        $category_id = $_POST['category_id'];
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$category_id]);
        $message = "Category deleted successfully.";
        $message_type = "success";

    } elseif (isset($_POST['add_option']) || isset($_POST['edit_option'])) {
        $option_id = $_POST['option_id'] ?? null;
        $option_name = trim($_POST['option_name']);
        $option_type = $_POST['option_type'];
        $is_required = isset($_POST['option_is_required']) ? 1 : 0;
        if (empty($option_name)) throw new Exception("Option name cannot be empty.");

        if ($option_id) {
            $stmt = $pdo->prepare('UPDATE customization_options SET name = ?, type = ?, is_required = ? WHERE id = ?');
            $stmt->execute([$option_name, $option_type, $is_required, $option_id]);
            $message = "Customization option updated successfully.";
        } else {
            $stmt = $pdo->prepare('INSERT INTO customization_options (name, type, is_required) VALUES (?, ?, ?)');
            $stmt->execute([$option_name, $option_type, $is_required]);
            $message = "Customization option added successfully.";
        }
        $message_type = "success";

    } elseif (isset($_POST['delete_option'])) {
        $option_id = $_POST['option_id'];
        $stmt = $pdo->prepare('DELETE FROM customization_options WHERE id = ?');
        $stmt->execute([$option_id]);
        $message = "Customization option deleted successfully.";
        $message_type = "success";

    } elseif (isset($_POST['add_choice']) || isset($_POST['edit_choice'])) {
        $choice_id = $_POST['choice_id'] ?? null;
        $option_id = (int)$_POST['choice_option_id'];
        $choice_name = trim($_POST['choice_name']);
        $price_adjustment = (float)$_POST['price_adjustment'];
        if (empty($choice_name)) throw new Exception("Choice name cannot be empty.");

        if ($choice_id) {
            $stmt = $pdo->prepare('UPDATE customization_choices SET name = ?, price_adjustment = ? WHERE id = ?');
            $stmt->execute([$choice_name, $price_adjustment, $choice_id]);
            $message = "Customization choice updated successfully.";
        } else {
            $stmt = $pdo->prepare('INSERT INTO customization_choices (option_id, name, price_adjustment) VALUES (?, ?, ?)');
            $stmt->execute([$option_id, $choice_name, $price_adjustment]);
            $message = "Customization choice added successfully.";
        }
        $message_type = "success";

    } elseif (isset($_POST['delete_choice'])) {
        $choice_id = $_POST['choice_id'];
        $stmt = $pdo->prepare('DELETE FROM order_item_customizations WHERE customization_choice_id = ?');
        $stmt->execute([$choice_id]);
        $stmt = $pdo->prepare('DELETE FROM customization_choices WHERE id = ?');
        $stmt->execute([$choice_id]);
        $message = "Customization choice deleted successfully.";
        $message_type = "success";
    }

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $message = $e->getMessage();
    $message_type = "danger";
    error_log("Menu Logic Error: " . $e->getMessage());
}

header('Location: menu.php?msg=' . urlencode($message) . '&type=' . urlencode($message_type));
exit();
?>