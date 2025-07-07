<?php include_once "db.php";

$Stu->del($_POST['id']);

header("Content-Type: application/json;");
echo json_encode(['success'=>true]);