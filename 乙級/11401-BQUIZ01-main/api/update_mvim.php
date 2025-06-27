<?php include_once "db.php";


if(!empty($_FILES['img']['tmp_name'])){
    move_uploaded_file($_FILES['img']['tmp_name'],"../images/".$_FILES['img']['name']);
    $row=$Mvim->find($_POST['id']);
    $row['img']=$_FILES['img']['name'];
    $Mvim->save($row);
    
}

to("../backend.php?do=mvim");
