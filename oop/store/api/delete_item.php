<?php
include_once "db.php";
/* $dsn="mysql:host=localhost;dbname=store;charset=utf8";
$pdo=new PDO($dsn, 'root', '');

$sql="DELETE FROM `items` WHERE `id`='{$_GET['id']}'";
$pdo->exec($sql);    */

$Item->del($_GET['id']);

header("Location: ../index.php");

