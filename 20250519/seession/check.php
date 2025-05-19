<?php
session_start();


if($_POST['acc'] == 'admin' && $_POST['pw'] == '1234'){
    echo "登入成功";    
    $_SESSION['login']=1;
    //header("location:login.php");
}else{
    echo "登入失敗";
    // header("location:login.php");
}   

?>