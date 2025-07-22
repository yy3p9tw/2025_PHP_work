<form action="./api/admin_news.php" method='post'>
<table style="width:70%; margin:auto">
    <tr class="ct">
        <td>編號</td>
        <td>標題</td>
        <td>顯示</td>
        <td>刪除</td>
    </tr>
    <?php
    $total=$News->count();
    $div=3;
    $pages=ceil($total/$div);
    $now=$_GET['p']??1;
    $start=($now-1)*$div;
    $rows=$News->all(" limit $start,$div");
    foreach($rows as $idx => $row):
    ?>
    <tr class="ct">
        <td><?=$idx+($now-1)*$div+1;?></td>
        <td width='80%'><?=$row['title'];?></td>
        <td>
            <input type="checkbox" name="sh[]" value="<?=$row['id'];?>" <?=($row['sh']==1)?'checked':'';?>>
        </td>
        <td>
            <input type="checkbox" name="del[]" value="<?=$row['id'];?>">
        </td>
        <input type="hidden" name="id[]" value="<?=$row['id'];?>">
    </tr>
    <?php
    endforeach;
    ?>
</table>
<div class="ct">
<?php

if($now-1>0){
    echo "<a href='?do=news&p=".($now-1)."' style='font-size:18px'> < </a>";
}

for($i=1;$i<=$pages;$i++){
    $size=($i==$now)?'24px':'18px';
    echo "<a href='?do=news&p=$i' style='font-size:{$size}'> $i </a>";
}


if($now+1<=$pages){
    echo "<a href='?do=news&p=".($now+1)."' style='font-size:18px'> > </a>";
}
?>


</div>
<div class="ct">
    <input type="submit" value="確定修改">
</div>
</form>