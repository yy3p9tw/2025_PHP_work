<div style="height:300px;">
<h3 class="ct" style="margin:0;">預告片清單</h3>
<div class='ct' style="display:flex;justify-content:space-between;">
    <div style='width:24.8%;background:#ccc'>預告片海報</div>
    <div style='width:24.8%;background:#ccc'>預告片片名</div>
    <div style='width:24.8%;background:#ccc'>預告片排序</div>
    <div style='width:24.8%;background:#ccc'>操作</div>
</div>
<form action="./api/edit_poster.php" method="post">
    
    <div style="overflow:auto;height:200px;">
    <?php
    $posters=$Poster->all(" order by `rank`");
    foreach($posters as $idx => $poster):
        $prev=($idx-1>=0)?$posters[$idx-1]['id']:$poster['id'];
        $next=($idx+1<count($posters))?$posters[$idx+1]['id']:$poster['id'];

    ?>
    <div class='ct' style="padding:3px;display:flex;justify-content:space-between;align-items:center;background:white;margin-bottom:3px;">
        <div style='width:24.8%'>
            <img src="./image/<?=$poster['img']?>" style="width:60px;height:80px;">
        </div>
        <div style='width:24.8%'>
            <input type="text" name="name[]" value="<?=$poster['name']?>" style="width:90%;">
        </div>
        <div style='width:24.8%'>
            <button class='sw-btn' type='button' data-sw='<?=$prev;?>' data-id="<?=$poster['id'];?>">往上</button>
            <button class='sw-btn' type='button' data-sw='<?=$next;?>' data-id="<?=$poster['id'];?>">往下</button>
        </div>
        <div style='width:24.8%'>
            <input type="checkbox" name="sh[]" value="<?=$poster['id'];?>" <?=($poster['sh']==1)?'checked':'';?>>顯示
            <input type="checkbox" name="del[]" value="<?=$poster['id'];?>">刪除
            <select name="ani[]" >
                <option value="1" <?=($poster['ani']==1)?'selected':'';?>>淡入淡出</option>
                <option value="2" <?=($poster['ani']==2)?'selected':'';?>>縮放</option>
                <option value="3" <?=($poster['ani']==3)?'selected':'';?>>滑入滑出</option>
            </select>
        </div>
    </div>
    <input type="hidden" name="id[]" value="<?=$poster['id'];?>">
    <?php
    endforeach;
    ?>
    </div>
        <div class="ct">
            <input type="submit" value="編輯確定">
            <input type="reset" value="重置">
        </div>
</form>

</div>
<script>
$(".sw-btn").on("click",function(){
    let id=$(this).data("id")
    let sw=$(this).data("sw")
    $.post("./api/sw.php",{table:'Poster',id,sw},(res)=>{
        location.reload();
    })
})

</script>
<hr>
<div style="height:140px;">
<h3 class='ct' style="margin:0;">新增預告片海報</h3>
<form action="./api/add_poster.php" method="post" enctype="multipart/form-data">
    <table>
        <tr>
            <td>預告片海報：</td>
            <td><input type="file" name="img" id="img"></td>
            <td>預告片片名：</td>
            <td><input type="text" name="name" id="name"></td>
        </tr>
    </table>
    <div class="ct">
        <input type="submit" value="新增">
        <input type="reset" value="重置">
    </div>
</form>
</div>