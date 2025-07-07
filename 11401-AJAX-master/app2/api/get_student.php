<?php include_once "db.php";

$student=$Stu->find($_GET['id']);


$student['classname']=$ref[$student['classroom']];

header('Content-Type: application/json;');
echo json_encode($student);