<?php
require_once '../../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $Category = new DB('categories');
    $name = trim($_POST['name']);

    try {
        $Category->insert(['name' => $name]);
        $id = $Category->getLastInsertId();
        echo json_encode(['id' => $id, 'name' => $name]);
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { // Duplicate entry
            http_response_code(409); // Conflict
            echo json_encode(['error' => '分類名稱已存在。']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => '新增失敗: ' . $e->getMessage()]);
        }
        exit;
    }
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);