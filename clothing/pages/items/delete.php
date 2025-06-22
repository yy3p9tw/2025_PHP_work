<?php
require_once '../../includes/db.php';
$Item = new DB('items');
$id = $_GET['id'] ?? 0;
if ($id) {
    $Item->delete($id);
}
header('Location: list.php');
exit;