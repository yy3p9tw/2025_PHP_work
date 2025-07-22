<?php include_once "db.php";

/* $_POST['news']
$_SESSION['login']
 */

$data=['news'=>$_POST['news'],'user'=>$_SESSION['login']];
$chk=$Log->count($data);

$news=$News->find($_POST['news']);
if($chk){
    $Log->del($data);
    $news['good']-=1;

}else{
    $Log->save($data);
    $news['good']+=1;
}
$News->save($news);