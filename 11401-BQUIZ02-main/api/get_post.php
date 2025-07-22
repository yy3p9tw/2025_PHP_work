<?php include_once "db.php"; 

$post=$News->find($_GET['id']);
echo "<h3>{$post['title']}</h3>";
echo nl2br($post['text']);

?>

