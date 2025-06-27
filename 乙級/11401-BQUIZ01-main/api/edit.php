<?php 
include_once "db.php";

$table=$_POST['table'];
$db=${ucfirst($table)};

foreach($_POST['id'] as $key => $id){
    if(isset($_POST['del']) && in_array($id,$_POST['del'])){
        $db->del($id);
    }else{
        $row=$db->find($id);
        //dd($row);
        switch($table){
            case "title":
                $row['text']=$_POST['text'][$key];
                $row['sh']=($_POST['sh']==$id)?1:0;
            break;
            case "ad":
                $row['text']=$_POST['text'][$key];
                $row['sh']=(isset($_POST['sh']) && in_array($id,$_POST['sh']))?1:0;
            break;
            case "mvim":
                $row['sh']=(isset($_POST['sh']) && in_array($id,$_POST['sh']))?1:0;
            break;
            case "image":
            break;
            case "news":
            break;
            case "admin":
            break;
            case "menu":
            break;

        }
        $db->save($row);
        //dd($row);
    }
}


to("../backend.php?do=$table");

?>