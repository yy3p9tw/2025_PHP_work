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
    <link rel="stylesheet" href="member_center.css">
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