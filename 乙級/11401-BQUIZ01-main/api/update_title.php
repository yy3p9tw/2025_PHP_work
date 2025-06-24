<?php include_once "db.php";


if(!empty($_FILES['img']['tmp_name'])){
    move_uploaded_file($_FILES['img']['tmp_name'],"../images/".$_FILES['img']['name']);
    $row=$Title->find($_POST['id']);
    $row['img']=$_FILES['img']['name'];
    $Title->save($row);
    
}

to("../backend.php?do=title");
