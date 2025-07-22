<h3 style='text-align:center;'>新增動態文字廣告</h3>
<hr>
<form action="./api/insert.php" method='post' enctype="multipart/form-data">
    <div>
        <label>動態文字廣告：</label>
        <input type="text" name="text">
    </div>
    <div>
        <input type="hidden" name="table" value="<?=$_GET['table'];?>">
        <input type="submit" value="新增">
        <input type="reset" value="重置">
    </div>
</form>