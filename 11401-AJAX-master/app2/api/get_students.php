<?php include_once "db.php";

$classroom=$_GET['classroom']??'101';

$students=$Stu->all(['classroom'=>$classroom]);

foreach($students as $key => $student){
    $students[$key]['classname']=$ref[$student['classroom']];
}


header('Content-Type: application/json;');
echo json_encode($students);