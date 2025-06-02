<?php

echo "<pre>";
print_r($_POST);
echo "</pre>";

$dsn="mysql:host=localhost;dbname=store;charset=utf8";
$pdo=new PDO($dsn, 'root', '');
$sql="INSERT INTO `items`(`name`, `price`, `cost`, `stock`) 
                VALUES ('{$_POST['name']}',
                '{$_POST['price']}',
                '{$_POST['cost']}',
                '{$_POST['stock']}')";
$pdo->exec($sql);   

header("Location: ../index.php");