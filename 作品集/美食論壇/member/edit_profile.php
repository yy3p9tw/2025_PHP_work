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
    <title>編輯個人資料</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/member_center.css">
    <link rel="stylesheet" href="../assets/css/edit_profile.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main>
    <div class="edit-profile-container">
        <div class="edit-title">編輯個人資料</div>
        <?php if(isset($_GET['msg'])): ?>
            <div class="msg"><?=htmlspecialchars($_GET['msg'])?></div>
        <?php endif; ?>
        <form class="edit-form" action="edit_profile_save.php" method="post" autocomplete="off">
            <label for="username">帳號（不可修改）</label>
            <input type="text" id="username" name="username" value="<?=htmlspecialchars($user['username'])?>" readonly>

            <label for="email">電子郵件</label>
            <input type="email" id="email" name="email" value="<?=htmlspecialchars($user['email'])?>" required>

            <label for="birthday">出生年月日</label>
            <input type="date" id="birthday" name="birthday" value="<?=htmlspecialchars($user['birthday'])?>" required>

            <button type="submit">儲存變更</button>
            <div class="back-link">
                <a href="member_center.php">回會員中心</a>
            </div>
        </form>
    </div>
</main>
<?php include '../includes/footer.php'; ?>    
</body>
</html>