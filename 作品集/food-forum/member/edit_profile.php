?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯個人資料</title>
    <!-- 載入主要樣式與會員中心、編輯頁專屬樣式 -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/member_center.css">
    <link rel="stylesheet" href="../assets/css/edit_profile.css">
</head>
<body>
<?php include '../includes/header.php'; // 載入網站上方導覽 ?>
<main>
    <div class="edit-profile-container">
        <div class="edit-title">編輯個人資料</div>
        <!-- 顯示提示訊息 -->
        <?php if(isset($_GET['msg'])): ?>
            <div class="msg"><?=htmlspecialchars($_GET['msg'])?></div>
        <?php endif; ?>
        <!-- 編輯個人資料表單 -->
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
<?php include '../includes/footer.php'; // 載入網站下方頁腳 ?>    
</body>
</html>