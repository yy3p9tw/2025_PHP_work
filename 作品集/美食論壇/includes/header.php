<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$dsn = "mysql:host=localhost;dbname=forum;charset=utf8";
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
?>
<div id='header'>
    <div class='logo'>
        <!-- 可放LOGO圖示 -->
        <a href="/作品集/美食論壇/index.php" style="display:inline-block;text-decoration:none;">
            <span style="font-size:2em;color:#ff9800;font-weight:bold;">🍜</span>
        </a>
    </div>
    <div class="nav">
        <a href="/作品集/美食論壇/index.php">首頁</a>
        <a href="/作品集/美食論壇/member/member_center.php">會員中心</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <span style="color:#ff9800;font-weight:bold; margin-left:10px;">
                <?=htmlspecialchars($_SESSION['username'])?>，歡迎您！
            </span>
            <a href="/作品集/美食論壇/auth/logout.php" style="margin-left:10px;">登出</a>
        <?php else: ?>
            <a href="/作品集/美食論壇/auth/reg.php">註冊</a>
            <a href="/作品集/美食論壇/auth/login.php">登入</a>
        <?php endif; ?>
    </div>
</div>