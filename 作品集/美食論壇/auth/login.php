<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員登入</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main>
    <div class="login-container">
        <div class="login-title">會員登入</div>
        <?php if(isset($_GET['msg'])): ?>
            <div class="msg"><?=htmlspecialchars($_GET['msg'])?></div>
        <?php endif; ?>
        <form class="login-form" action="login_check.php" method="post" autocomplete="off">
            <label for="username">帳號</label>
            <input type="text" id="username" name="username" required maxlength="20" placeholder="請輸入帳號">

            <label for="password">密碼</label>
            <input type="password" id="password" name="password" required minlength="6" maxlength="20" placeholder="請輸入密碼">

            <button type="submit">登入</button>
            <div class="reg-link">
                沒有帳號？<a href="reg.php">註冊</a>
            </div>
        </form>
    </div>
</main>
<?php include '../includes/footer.php'; ?>    
</body>
</html>