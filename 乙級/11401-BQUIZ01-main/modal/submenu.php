<h3 style='text-align:center;'>
    編輯次選單
</h3>
<hr>
<form action="./api/update.php" method='post' enctype="multipart/form-data">
    <div>
        <label>：</label>
        <input type="file" name="img">
    </div>
    <div>
        <input type="hidden" name='id' value="<?=$_GET['id'];?>">
        <input type="hidden" name='table' value="<?=$_GET['table'];?>">
        <input type="submit" value="更新">
        <input type="reset" value="重置">
    </div>
</form>