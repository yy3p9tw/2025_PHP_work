<h3 style='text-align:center;'>新增標題區圖片</h3>
<hr>
<form action="./api/insert_title.php" method='post' enctype="multipart/form-data">
    <div>
        <label>標題區圖片：</label>
        <input type="file" name="img">
    </div>
    <div>
        <label>標題區替代文字：</label>
        <input type="text" name="text">
    </div>
    <div>
        <input type="submit" value="新增">
        <input type="reset" value="重置">
    </div>
</form>