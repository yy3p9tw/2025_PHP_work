<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>刪除餐點</title>
</head>
<body>
    <h1>刪除餐點</h1>
    <form action="./api/delete_item.php" method="post">
        <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
        <input type="submit" value="刪除">
        <a href="index.php">取消</a>
      
    </form>
</body>
</html>