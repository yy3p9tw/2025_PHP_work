<h3 style='text-align:center;'>新增主選單</h3>
<hr>
<form action="./api/insert.php" method='post' enctype="multipart/form-data">
    <div>
        <label>主選單名稱：</label>
        <input type="text" name="text">
    </div>
    <div>
        <label>選單連結網址：</label>
        <input type="text" name="href">
    </div>
    <div>
        <input type="hidden" name="table" value="<?=$_GET['table'];?>">
        <input type="submit" value="新增">
        <input type="reset" value="重置">
    </div>
</form>