<?php
session_start();
$dsn = "mysql:host=localhost;dbname=forum;charset=utf8";
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
?>
<div id='header'>
    <div class='logo'></div>
    <div class="nav">
        <a href="index.php">首頁</a>
        <a href="member_center.php">會員中心</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <span style="color:#ff9800;font-weight:bold;"><?=htmlspecialchars($_SESSION['username'])?></span>
            <a href="logout.php">登出</a>
        <?php else: ?>
            <a href="reg.php">註冊</a>
            <a href="login.php">登入</a>
        <?php endif; ?>
    </div>
</div>