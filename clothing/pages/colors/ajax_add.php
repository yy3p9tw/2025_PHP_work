<?php
require_once '../../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $Color = new DB('colors');
    $id = $Color->insert(['name' => $_POST['name']]);
    header('Content-Type: application/json');
    echo json_encode(['id' => $id, 'name' => $_POST['name']]);
    exit;
}
http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
