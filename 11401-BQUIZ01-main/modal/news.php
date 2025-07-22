<h3 style='text-align:center;'>新增最新消息資料</h3>
<hr>
<form action="./api/insert.php" method='post' enctype="multipart/form-data">
    <div>
        <label >最新消息資料：</label>
        <textarea name="text" style="width:200px;height:100px;vertical-align:middle"></textarea>
    </div>
    <div>
        <input type="hidden" name="table" value="<?=$_GET['table'];?>">
        <input type="submit" value="新增">
        <input type="reset" value="重置">
    </div>
</form>