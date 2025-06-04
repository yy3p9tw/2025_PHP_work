<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=請先登入會員");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員中心</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .center-container {
            max-width: 600px;
            margin: 48px auto;
            background: #fffbe9;
            border-radius: 18px;
            box-shadow: 0 4px 24px #ffd58077;
            padding: 36px 32px 28px 32px;
            text-align: center;
        }
        .center-title {
            font-size: 2em;
            color: #ff9800;
            margin-bottom: 18px;
            letter-spacing: 2px;
            font-weight: bold;
        }
        .center-info {
            font-size: 1.2em;
            color: #ad6800;
            margin-bottom: 24px;
        }
        .center-btn {
            display: inline-block;
            margin: 12px 8px 0 8px;
            padding: 10px 28px;
            background: linear-gradient(90deg, #ffb347 60%, #ffcc80 100%);
            color: #fff;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            text-decoration: none;
            box-shadow: 0 2px 8px #ffd58055;
            border: none;
            transition: background 0.2s;
        }
        .center-btn:hover {
            background: linear-gradient(90deg, #ff9800 60%, #ffb347 100%);
            color: #fff;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <div class="center-container">
        <div class="center-title">會員中心</div>
        <div class="center-info">
            歡迎回來，<b><?=htmlspecialchars($_SESSION['username'])?></b>！
        </div>
        <a class="center-btn" href="index.php">回首頁</a>
        <a class="center-btn" href="logout.php">登出</a>
    </div>
</main>
<?php include 'footer.php'; ?>    
</body>
</html>