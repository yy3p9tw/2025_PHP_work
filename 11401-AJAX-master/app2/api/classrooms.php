<?php include_once "db.php";

$sql="select classroom from students group by classroom";
$classrooms=q($sql);


foreach($classrooms as $key => $classroom){
    $classrooms[$key]['name']=$ref[$classroom['classroom']];
}

header('Content-Type: application/json;');
echo json_encode($classrooms);

