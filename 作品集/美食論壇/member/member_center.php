<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?msg=請先登入會員");
    exit;
}
// 取得會員資料
$dsn = "mysql:host=localhost;dbname=forum;charset=utf8";
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$stmt = $pdo->prepare("SELECT * FROM members WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員中心</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/member_center.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main>
    <div class="center-container">
        <div class="center-title">會員中心</div>
        <div class="profile-avatar">
            <!-- 若有大頭貼可改為 <img src="..." ...> -->
            <?= strtoupper(mb_substr($user['username'], 0, 1, 'UTF-8')) ?>
        </div>
        <table class="profile-info-table">
            <tr>
                <td><b>帳號：</b></td>
                <td><?=htmlspecialchars($user['username'])?></td>
            </tr>
            <tr>
                <td><b>Email：</b></td>
                <td><?=htmlspecialchars($user['email'])?></td>
            </tr>
            <tr>
                <td><b>註冊時間：</b></td>
                <td><?=htmlspecialchars($user['reg_time'])?></td>
            </tr>
            <tr>
                <td><b>最後登入：</b></td>
                <td><?=htmlspecialchars($user['last_login'])?></td>
            </tr>
            <tr>
                <td><b>出生年月日：</b></td>
                <td><?=htmlspecialchars($user['birthday'])?></td>
            </tr>
        </table>
        <div class="profile-actions">
            <a class="center-btn" href="../index.php">回首頁</a>
            <a class="center-btn" href="../auth/logout.php">登出</a>
            <a class="profile-edit-link" href="edit_profile.php">編輯個人資料</a>
        </div>
        <div class="profile-divider"></div>
        <div style="color:#ad6800; font-size:1.1em; margin-bottom:12px;">
            近期動態（開發中）
        </div>
        <div style="background:#fff8e1; border-radius:10px; padding:18px; color:#bdb76b;">
            這裡將顯示你的發文、留言、收藏等動態紀錄。
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>    
</body>
</html>