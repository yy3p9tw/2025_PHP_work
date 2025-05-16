<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .icon{
            background-image: url(https://iili.io/3gzpqen.png);
            width: 100%;
            height: 30vh;
            background-size:px;
            margin: 0 auto;
        }

    </style>
</head>
<body>
    <form action="pin.php" method="post">
        <div class="icon"></div>
        <div>
            <label for="acc">帳號：</label>
            <input type="text" name="acc"  min="0" required>
        </div>
        <div>
            <label for="pw">密碼：</label>
            <input type="password" name="pw"  min="0" required>
        </div>
        <div class="button-group">
            <input type="submit" value="登入">
            <input type="reset" value="清空內容">
        </div>
    </form>
</body>
</html>