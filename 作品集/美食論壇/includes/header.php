<?php
// 如果尚未啟動 session，則啟動 session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 連接資料庫
require_once __DIR__ . '/db.php';
$pdo = getPDO();
?>
<div id='header'>
    <div class='logo'>
        <!-- LOGO圖示，點擊可回首頁 -->
        <a href="/作品集/美食論壇/index.php" style="display:inline-block;text-decoration:none;">
            <span style="font-size:2em;color:#ff9800;font-weight:bold;">🍜</span>
        </a>
    </div>
    <div class="nav">
        <!-- 導覽列連結 -->
        <a href="/作品集/美食論壇/index.php">首頁</a>
        <a href="/作品集/美食論壇/member/member_center.php">會員中心</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <!-- 已登入顯示帳號與登出 -->
            <span style="color:#ff9800;font-weight:bold; margin-left:10px;">
                <?=htmlspecialchars($_SESSION['username'])?>，歡迎您！
            </span>
            <a href="/作品集/美食論壇/auth/logout.php" style="margin-left:10px;">登出</a>
        <?php else: ?>
            <!-- 未登入顯示註冊與登入 -->
            <a href="/作品集/美食論壇/auth/reg.php">註冊</a>
            <a href="/作品集/美食論壇/auth/login.php">登入</a>
        <?php endif; ?>
    </div>
</div>