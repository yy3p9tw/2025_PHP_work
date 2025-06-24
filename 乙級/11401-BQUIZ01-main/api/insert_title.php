<?php 

include_once "db.php";

if(!empty($_FILES['img']['tmp_name'])){
    move_uploaded_file($_FILES['img']['tmp_name'],"../images/".$_FILES['img']['name']);
    $_POST['img']=$_FILES['img']['name'];
    $_POST['sh']=0;
}

$Title->save($_POST);

to("../backend.php?do=title");




