<?php 
include_once "db.php";


foreach($_POST['id'] as $key => $id){
    if(isset($_POST['del']) && in_array($id,$_POST['del'])){
        $Title->del($id);
    }else{
        $row=$Title->find($id);
        dd($row);
        $row['text']=$_POST['text'][$key];
        $row['sh']=($_POST['sh']==$id)?1:0;
        $Title->save($row);
        dd($row);

    }
}


to("../backend.php?do=title");

?>