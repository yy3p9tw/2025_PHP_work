<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員註冊</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .reg-container {
            max-width: 420px;
            margin: 40px auto;
            background: #fffbe9;
            border-radius: 18px;
            box-shadow: 0 4px 24px #ffd58077;
            padding: 36px 32px 28px 32px;
        }
        .reg-title {
            font-size: 2em;
            color: #ff9800;
            text-align: center;
            margin-bottom: 24px;
            letter-spacing: 2px;
            font-weight: bold;
        }
        .reg-form label {
            display: block;
            margin-bottom: 8px;
            color: #ff9800;
            font-weight: bold;
        }
        .reg-form input[type="text"],
        .reg-form input[type="password"],
        .reg-form input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #ffd580;
            border-radius: 8px;
            font-size: 1em;
            background: #fff8e1;
            transition: border 0.2s;
        }
        .reg-form input:focus {
            border: 1.5px solid #ffb347;
            outline: none;
        }
        .reg-form button {
            width: 100%;
            padding: 12px 0;
            background: linear-gradient(90deg, #ffb347 60%, #ffcc80 100%);
            color: #fff;
            font-size: 1.1em;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 2px 8px #ffd58055;
            transition: background 0.2s;
        }
        .reg-form button:hover {
            background: linear-gradient(90deg, #ff9800 60%, #ffb347 100%);
        }
        .reg-form .login-link {
            display: block;
            text-align: right;
            margin-top: 10px;
            font-size: 0.98em;
        }
        .reg-form .login-link a {
            color: #ff9800;
            text-decoration: underline;
        }
        .reg-form .login-link a:hover {
            color: #ff7043;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <div class="reg-container">
        <div class="reg-title">會員註冊</div>
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
<?php include 'footer.php'; ?>    
</body>
</html>