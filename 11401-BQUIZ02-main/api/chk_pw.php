<?php include_once "db.php";

$chk=$User->count(['acc'=>$_GET['acc'],'pw'=>$_GET['pw']]);
if($chk){
    echo 1; 
    $_SESSION['login']=$_GET['acc']; //登入成功，將帳號存入session
}else{
    echo 0; 
}



?>