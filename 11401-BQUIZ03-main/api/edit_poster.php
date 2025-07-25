<?php include_once "db.php";

foreach($_POST['id'] as $idx => $id){
   if(isset($_POST['del']) && in_array($id, $_POST['del'])){
      $Poster->del($id);
   }else{
       $poster=$Poster->find($id);
       $poster['name']=$_POST['name'][$idx];   
       $poster['sh']=(isset($_POST['sh']) && in_array($id, $_POST['sh'])) ? 1 : 0;
       $poster['ani']=$_POST['ani'][$idx];
       $Poster->save($poster);
   }
}

to("../back.php?do=poster");