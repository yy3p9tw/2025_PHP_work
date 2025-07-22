<?php include_once "db.php";

$chk=$User->count(['acc'=>$_GET['acc']]);
if($chk){
    echo 1; //帳號重複
}else{
    echo 0; //帳號可用
}



?>