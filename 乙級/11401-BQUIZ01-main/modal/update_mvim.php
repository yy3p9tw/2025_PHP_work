<h3 style='text-align:center;'>更換動畫圖片</h3>
<hr>
<form action="./api/update_mvim.php" method='post' enctype="multipart/form-data">
    <div>
        <label>動畫圖片：</label>
        <input type="file" name="img">
    </div>
    <div>
        <input type="hidden" name='id' value="<?=$_GET['id'];?>">
        <input type="submit" value="更新">
        <input type="reset" value="重置">
    </div>
</form>