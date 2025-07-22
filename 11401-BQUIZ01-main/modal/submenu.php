<?php include_once "../api/db.php";?>
<h3 style='text-align:center;'>
    編輯次選單
</h3>
<hr>
<form action="./api/submenu.php" method='post' enctype="multipart/form-data">
    <div style="display:flex;margin:auto;width:70%" class="cent">
        <div style="width:44%;margin:5px 0.5%">次選單名稱</div>
        <div style="width:44%;margin:5px 0.5%">次選單連結網址</div>
        <div style="width:10%;margin:5px 0.5%">刪除</div>
    </div>
    <?php 
    $rows=$Menu->all(['main_id'=>$_GET['id']]);
    foreach($rows as $row):
    ?>
    <div style="display:flex;margin:auto;width:70%" class="cent">
        <div style="width:45%;margin:5px 0.5%">
            <input type="text" name='text[]' value="<?=$row['text'];?>" style="width:95%">
        </div>
        <div style="width:45%;margin:5px 0.5%">
            <input type="text" name='href[]' value="<?=$row['href'];?>" style="width:95%">
        </div>
        <div style="width:10%;margin:5px 0.5%">
            <input type="checkbox" name='del[]' value="<?=$row['id'];?>" >
        </div>
        <input type="hidden" name="id[]" value="<?=$row['id'];?>">
    </div>

    <?php 
    endforeach;
    ?>
    <div id="add"></div>
    <div class="cent">
        <input type="hidden" name='main_id' value="<?=$_GET['id'];?>">
        <input type="hidden" name='table' value="<?=$_GET['table'];?>">
        <input type="submit" value="修改確定">
        <input type="reset" value="重置">
        <input type="button" value="更多次選單" onclick="more()">
    </div>
    
</form>

<script>
function more(){
    let item=`<div style="display:flex;margin:auto;width:70%" class="cent">
        <div style="width:45%;margin:5px 0.5%">
            <input type="text" name='text2[]' value="" style="width:95%">
        </div>
        <div style="width:45%;margin:5px 0.5%">
            <input type="text" name='href2[]' value="" style="width:95%">
        </div>
        <div style="width:10%;margin:5px 0.5%">
            
        </div>
    </div>`

    $("#add").append(item)
}


</script>