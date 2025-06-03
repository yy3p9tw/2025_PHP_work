<?php

echo "<pre>";
print_r($_POST);
echo "</pre>";

$dsn="mysql:host=localhost;dbname=store;charset=utf8";
$pdo=new PDO($dsn, 'root', '');
$sql="DELETE FROM `items` WHERE `id`='{$_POST['id']}'";
$pdo->exec($sql);


    header("location:../index.php");


?>