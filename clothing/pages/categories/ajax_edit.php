<?php
require_once '../../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id']) && !empty($_POST['name'])) {
    $Category = new DB('categories');
    $Category->update($_POST['id'], ['name' => $_POST['name']]);
    header('Content-Type: application/json');
    echo json_encode(['id' => $_POST['id'], 'name' => $_POST['name']]);
    exit;
}
http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
