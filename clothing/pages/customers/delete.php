<?php
require_once '../../includes/db.php';
$Customer = new DB('customers');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $Customer->delete($id);
}
header('Location: list.php');
exit;
