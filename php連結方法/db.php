<?php
$dsn="mysql:host=localhost;dbname=school;charset=utf8";
$pdo=new PDO($dsn,'root','');

$sql="select * from students where id<=10";
$query=$pdo->query($sql);
$rows=$query->fetchAll(PDO::FETCH_ASSOC);
// echo "<pre>";
// print_r($rows);
// echo"</pre>";
?>
<table>
    <tr>
        <th>id</th>
        <th>學號</th>
        <th>姓名</th>
        <th>生日</th>
        <th>電話</th>
    </tr>
    <?php
    foreach($rows as $row){
    ?>
    <tr>
        <td><?=$row['id'];?></td>
        <td><?=$row['school_num'];?></td>
        <td><?=$row['name'];?></td>
        <td><?=$row['birthday'];?></td>
        <td><?=$row['tel'];?></td>
    </tr>
    <?php } ?>
</table>
 <?php 
    foreach($rows as $row){
    ?>
<div class='card'>
    <h3 class='head'><?=$row['name'];?></h3>
    <div class='id'><?=$row['id'];?></div>
    <div class='birthday'><?=$row['birthday'];?></div>
    <div class='tel'><?=$row['tel'];?></div>
    <div class='num'><?=$row['school_num'];?></div>
</div>
<?php 
    }

?>
