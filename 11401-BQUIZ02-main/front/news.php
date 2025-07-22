<div class="nav" style="margin-bottom:20px;">
    目前位置：首頁 > 最新文章區
</div>
<style>
    .title{
        cursor: pointer;
        color: blue;
    }
    .title:hover{
        text-decoration: underline;
        color:green;
    }
</style>
<table style="width:95%;margin:auto">
    <tr class="ct">
        <td width="20%">標題</td>
        <td width="60%">內容</td>
        <td></td>
    </tr>
<?php 
    $total=$News->count();
    $div=5;
    $pages=ceil($total/$div);
    $now=$_GET['p']??1;
    $start=($now-1)*$div;
    $rows=$News->all(" limit $start,$div");
    foreach($rows as $idx => $row):
?>
    <tr>
        <td class='title'><?=$row['title'];?></td>
        <td>
            <div class="short"><?=mb_substr($row['text'],0,30);?>...</div>
            <div class="all" style='display:none'><?=nl2br($row['text']);?></div>
        </td>
        <td>
            <?php 
            if(isset($_SESSION['login'])):
                $chk=$Log->count(['news'=>$row['id'],'user'=>$_SESSION['login']]);
            ?>
            <a href="#" onclick="good(<?=$row['id'];?>)"><?=($chk)?'收回讚':'讚';?></a>
            <?php
            endif;
            ?>
        </td>
    </tr>
<?php
    endforeach;
?>    
</table>
<div>
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
<script>
    $(".title").on("click",function(){
        $(this).next().find(".short,.all").toggle();
        //$(this).next().find(".all").toggle();
    })

    function good(news){
        $.post("./api/good.php",{news},function(){
                location.reload();
        })
    }
</script>