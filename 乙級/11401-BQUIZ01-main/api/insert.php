<?php 

include_once "db.php";

if(!empty($_FILES['img']['tmp_name'])){
    move_uploaded_file($_FILES['img']['tmp_name'],"../images/".$_FILES['img']['name']);
    $_POST['img']=$_FILES['img']['name'];
}

$table=$_POST['table'];
$db=${ucfirst($table)};

switch($table){
    case "title":
        $_POST['sh']=0;
    break;
    case "admin":
    break;
    default:
        $_POST['sh']=1;

}

unset($_POST['table']);

$db->save($_POST);


to("../backend.php?do=$table");




