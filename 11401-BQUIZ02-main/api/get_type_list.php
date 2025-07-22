<?php include_once "db.php";

$news=$News->all(['type'=>$_GET['type']]);
foreach($news as $n){
    echo "<div>";
    echo "<a class='post-item' href='#' data-post='{$n['id']}'>";
    echo $n['title'];
    echo "</a>";
    echo "</div>";
}
