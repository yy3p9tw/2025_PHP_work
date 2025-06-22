<?php
require_once '../../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $Category = new DB('categories');
    $id = $Category->insert(['name' => $_POST['name']]);
    // 回傳新分類資料
    header('Content-Type: application/json');
    echo json_encode(['id' => $id, 'name' => $_POST['name']]);
    exit;
}
http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
