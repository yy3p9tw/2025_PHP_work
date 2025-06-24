<?php 

include_once "db.php";

$_POST['sh']=1;

$Ad->save($_POST);

to("../backend.php?do=ad");




