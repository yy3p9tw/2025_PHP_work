<?php include_once "db.php";
$user=$User->find($_GET);


if(empty($user)){
    echo "查無此資料";
}else{
    echo "你的密碼為：".$user['pw'];
}

?>