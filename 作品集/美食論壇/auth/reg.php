<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員註冊</title>
    <!-- 載入主要樣式與註冊頁專屬樣式 -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/reg.css">
</head>
<body>
<?php include '../includes/header.php'; // 載入網站上方導覽 ?>
<main>
    <div class="reg-container">
        <div class="reg-title">會員註冊</div>
        <!-- 註冊表單 -->
        <form class="reg-form" action="reg_save.php" method="post" autocomplete="off">
            <label for="username">帳號</label>
            <input type="text" id="username" name="username" required maxlength="20" placeholder="請輸入帳號">

            <label for="password">密碼</label>
            <input type="password" id="password" name="password" required minlength="6" maxlength="20" placeholder="請輸入密碼">

            <label for="email">電子郵件</label>
            <input type="email" id="email" name="email" required placeholder="請輸入Email">

            <button type="submit">註冊</button>
            <div class="login-link">
                已有帳號？<a href="login.php">登入</a>
            </div>
        </form>
    </div>
</main>
<?php include '../includes/footer.php'; // 載入網站下方頁腳 ?>    
</body>
</html>