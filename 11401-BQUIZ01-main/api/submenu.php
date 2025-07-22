<?php include_once "db.php";
$main_id=$_POST['main_id'];

if(isset($_POST['id'])){
    foreach($_POST['id'] as $key => $id){
        if(isset($_POST['del']) && in_array($id,$_POST['del'])){
            $Menu->del($id);
        }else{
            $row=$Menu->find($id);
            $row['text']=$_POST['text'][$key];
            $row['href']=$_POST['href'][$key];
            $Menu->save($row);
        }
    }
}


if(isset($_POST['text2'])){
    foreach($_POST['text2'] as $key =>$text){
        if($text!=""){
            $href=$_POST['href2'][$key];
            $Menu->save(['text'=>$text,
                         'href'=>$href,
                         'main_id'=>$main_id,
                         'sh'=>1]);
        }
    }
}

to("../backend.php?do=menu");

?>

